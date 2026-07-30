(function ($) {
    // Inject styles
    $("<style>")
        .html(
            `
        .excel-filter-btn { color: #9ca3af; cursor: pointer; margin-left: 5px; font-size: 0.85rem; transition: color 0.2s; }
        .excel-filter-btn:hover { color: #4b5563; }
        .excel-filter-btn.active { color: #2563eb; }
        .excel-dropdown { position: absolute; z-index: 1050; background: white; border: 1px solid #d1d5db; border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); width: 240px; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 13px; color: #374151; padding: 0px; display: none; overflow: hidden; }
        .excel-dropdown .action-item { padding: 8px 12px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .excel-dropdown .action-item:hover { background-color: #f3f4f6; }
        .excel-dropdown hr { margin: 0; border: 0; border-top: 1px solid #e5e7eb; }
        .excel-dropdown .search-container { padding: 8px; }
        .excel-dropdown input.search { width: 100%; border: 1px solid #d1d5db; border-radius: 4px; padding: 6px 8px; outline: none; transition: border 0.2s; }
        .excel-dropdown input.search:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
        .excel-dropdown .checklist-container { border-top: 1px solid #e5e7eb; padding: 4px 8px; }
        .excel-dropdown .checklist { max-height: 180px; overflow-y: auto; padding-right: 4px; }
        .excel-dropdown .checklist label { display: flex; align-items: center; padding: 4px 4px; cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-radius: 4px; margin-bottom: 0; font-weight: normal; }
        .excel-dropdown .checklist label:hover { background-color: #f3f4f6; }
        .excel-dropdown .checklist input[type="checkbox"] { margin-right: 8px; cursor: pointer; }
        .excel-dropdown .buttons-row { display: flex; justify-content: flex-end; gap: 8px; padding: 8px; border-top: 1px solid #e5e7eb; background: #f9fafb; }
        .excel-dropdown .buttons-row button { padding: 6px 14px; border-radius: 4px; cursor: pointer; font-weight: 500; transition: background 0.2s; }
        .excel-dropdown .btn-ok { background: #3b82f6; color: white; border: none; }
        .excel-dropdown .btn-ok:hover { background: #2563eb; }
        .excel-dropdown .btn-cancel { background: white; color: #374151; border: 1px solid #d1d5db; }
        .excel-dropdown .btn-cancel:hover { background: #f3f4f6; }
        
        /* Dark mode styles */
        .excel-dropdown.dark-mode { background: #1f2937; border-color: #374151; color: #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5); }
        .excel-dropdown.dark-mode .action-item:hover { background-color: #374151; }
        .excel-dropdown.dark-mode hr { border-top-color: #4b5563; }
        .excel-dropdown.dark-mode input.search { background: #374151; border-color: #4b5563; color: #e5e7eb; }
        .excel-dropdown.dark-mode input.search:focus { border-color: #3b82f6; }
        .excel-dropdown.dark-mode input.search::placeholder { color: #9ca3af; }
        .excel-dropdown.dark-mode .checklist-container { border-top-color: #4b5563; }
        .excel-dropdown.dark-mode .checklist label:hover { background-color: #374151; }
        .excel-dropdown.dark-mode .buttons-row { border-top-color: #4b5563; background: #111827; }
        .excel-dropdown.dark-mode .btn-cancel { background: #374151; color: #e5e7eb; border-color: #4b5563; }
        .excel-dropdown.dark-mode .btn-cancel:hover { background: #4b5563; }
        
        table.dataTable thead th .dt-column-title { display: inline-flex; align-items: center; }
    `,
        )
        .appendTo("head");

    let $dropdown = $("#excel-filter-dropdown");
    if ($dropdown.length === 0) {
        $dropdown = $(`
            <div id="excel-filter-dropdown" class="excel-dropdown">
                <div class="action-item sort-asc"><i class="bi bi-sort-alpha-down"></i> Ordenar de A a Z</div>
                <div class="action-item sort-desc"><i class="bi bi-sort-alpha-down-alt"></i> Ordenar de Z a A</div>
                <hr>
                <div class="action-item clear-filter text-blue-600" style="display:none;"><i class="bi bi-funnel"></i> <span>Borrar filtro</span></div>
                <hr>
                <div class="search-container">
                    <input type="text" class="search" placeholder="Buscar">
                </div>
                <div class="checklist-container">
                    <div class="checklist"></div>
                </div>
                <div class="buttons-row">
                    <button class="btn-cancel">Cancelar</button>
                    <button class="btn-ok">ACEPTAR</button>
                </div>
            </div>
        `).appendTo("body");
        
        // Sincronizar tema oscuro/claro
        const syncTheme = () => {
            if (document.documentElement.classList.contains('dark')) {
                $dropdown.addClass('dark-mode');
            } else {
                $dropdown.removeClass('dark-mode');
            }
        };
        
        // Observar cambios en la clase dark del html
        const observer = new MutationObserver(syncTheme);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        syncTheme(); // Aplicar tema inicial
    }

    let activeTable = null;
    let activeColIndex = null;
    let activeIcon = null;

    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }

    $.fn.initExcelFilter = function () {
        return this.each(function () {
            var table = $(this).DataTable();

            table.columns().every(function (index) {
                var column = this;
                var th = $(column.header());
                var title = th.text().trim();

                // Ignorar columnas de acciones (no filtrables)
                if (
                    !title ||
                    title.toLowerCase().includes("acción") ||
                    title.toLowerCase().includes("acciones") ||
                    title.toLowerCase().includes("opciones")
                )
                    return;

                th.find(".excel-filter-btn").remove();
                var $icon = $(
                    '<i class="bi bi-funnel-fill excel-filter-btn" title="Filtrar"></i>',
                );

                var dtTitle = th.find(".dt-column-title");
                if (dtTitle.length > 0) {
                    dtTitle.append($icon);
                } else {
                    th.append($icon);
                }

                // Initial state check
                var currentFilter = column.search();
                if (currentFilter) {
                    $icon.addClass("active");
                }

                $icon.on("click", function (e) {
                    e.stopPropagation();
                    e.preventDefault();

                    if (
                        $dropdown.is(":visible") &&
                        activeColIndex === index &&
                        activeTable === table
                    ) {
                        $dropdown.hide();
                        return;
                    }

                    activeTable = table;
                    activeColIndex = index;
                    activeIcon = $(this);

                    var offset = $(this).offset();
                    $dropdown
                        .css({
                            top: offset.top + $(this).outerHeight() + 5,
                            left: offset.left,
                        })
                        .show();

                    if (
                        $dropdown.offset().left + $dropdown.outerWidth() >
                        $(window).width()
                    ) {
                        $dropdown.css(
                            "left",
                            $(window).width() - $dropdown.outerWidth() - 10,
                        );
                    }

                    var $checklist = $dropdown.find(".checklist");
                    $checklist.empty();

                    $checklist.append(
                        '<label class="select-all-label"><input type="checkbox" class="select-all"> (Seleccionar todo)</label>',
                    );

                    var uniqueData = [];
                    column.render("filter").each(function (d) {
                        let txt = d;
                        if (typeof d === "string" && d.indexOf("<") !== -1) {
                            txt = $("<div>").html(d).text().trim();
                        } else if (typeof d === "string") {
                            txt = d.trim();
                        }

                        if (!txt || txt === "") txt = "(Vacío)";
                        if (uniqueData.indexOf(txt) === -1)
                            uniqueData.push(txt);
                    });
                    uniqueData.sort();

                    currentFilter = column.search();
                    var selectedVals = [];
                    if (
                        currentFilter &&
                        currentFilter.startsWith("^\\s*(") &&
                        currentFilter.endsWith(")\\s*$")
                    ) {
                        var inner = currentFilter.substring(
                            5,
                            currentFilter.length - 5,
                        );
                        selectedVals = inner
                            .split("|")
                            .map((v) => v.replace(/\\/g, ""));
                    } else if (currentFilter) {
                        selectedVals = [
                            currentFilter.replace(
                                /\\.*$|[.*+?^${}()|[\]\\]/g,
                                "",
                            ),
                        ];
                    }

                    let allChecked = true;
                    uniqueData.forEach(function (textVal) {
                        let isChecked =
                            selectedVals.length === 0 ||
                            selectedVals.includes(textVal);
                        if (!isChecked) allChecked = false;

                        let displayVal =
                            textVal === "(Vacío)" ? "(Vacío)" : textVal;

                        let $lbl = $('<label class="item-label"></label>');
                        let $chk = $('<input type="checkbox" class="item-chk">')
                            .val(textVal)
                            .prop("checked", isChecked);
                        $lbl.append($chk).append(
                            document.createTextNode(" " + displayVal),
                        );
                        $checklist.append($lbl);
                    });

                    $dropdown.find(".select-all").prop("checked", allChecked);
                    $dropdown.find("input.search").val("").trigger("input");

                    if (currentFilter) {
                        $dropdown
                            .find(".clear-filter")
                            .show()
                            .find("span")
                            .text('Borrar filtro de "' + title + '"');
                    } else {
                        $dropdown.find(".clear-filter").hide();
                    }
                });
            });
        });
    };

    $(document).on("click", function (e) {
        if (
            $dropdown.is(":visible") &&
            !$(e.target).closest(".excel-dropdown, .excel-filter-btn").length
        ) {
            $dropdown.hide();
        }
    });

    $dropdown.find(".btn-cancel").on("click", function () {
        $dropdown.hide();
    });

    $dropdown.find(".sort-asc").on("click", function () {
        activeTable.order([activeColIndex, "asc"]).draw();
        $dropdown.hide();
    });

    $dropdown.find(".sort-desc").on("click", function () {
        activeTable.order([activeColIndex, "desc"]).draw();
        $dropdown.hide();
    });

    $dropdown.find(".clear-filter").on("click", function () {
        activeTable.column(activeColIndex).search("").draw();
        activeIcon.removeClass("active");
        $dropdown.hide();
    });

    $dropdown.on("change", ".select-all", function () {
        var isChecked = $(this).is(":checked");
        $dropdown.find(".item-chk:visible").prop("checked", isChecked);
    });

    $dropdown.on("change", ".item-chk", function () {
        var anyUnchecked = $dropdown.find(".item-chk:not(:checked)").length > 0;
        $dropdown.find(".select-all").prop("checked", !anyUnchecked);
    });

    $dropdown.find("input.search").on("input", function () {
        var term = $(this).val().toLowerCase();
        $dropdown.find(".checklist .item-label").each(function () {
            var txt = $(this).text().toLowerCase();
            $(this).toggle(txt.indexOf(term) > -1);
        });
    });

    $dropdown.find(".btn-ok").on("click", function () {
        var allCheckboxes = $dropdown.find(".item-chk");
        var checkedCheckboxes = $dropdown.find(".item-chk:checked");

        if (checkedCheckboxes.length === allCheckboxes.length) {
            activeTable.column(activeColIndex).search("").draw();
            activeIcon.removeClass("active");
        } else if (checkedCheckboxes.length === 0) {
            activeTable.column(activeColIndex).search("^$", true, false).draw();
            activeIcon.addClass("active");
        } else {
            var vals = [];
            checkedCheckboxes.each(function () {
                var v = $(this).val();
                if (v === "(Vacío)") v = "";
                vals.push(escapeRegExp(v));
            });
            var regex = "^\\s*(" + vals.join("|") + ")\\s*$";
            activeTable
                .column(activeColIndex)
                .search(regex, true, false)
                .draw();
            activeIcon.addClass("active");
        }
        $dropdown.hide();
    });

    // Auto-initialize for all DataTables automatically
    $(document).on("init.dt", function (e, settings) {
        if (e.namespace !== "dt") return;

        // Permitir varias formas de desactivarlo: excelFilter, excelfilter, 'false' (string), o data-excel-filter="false" en el HTML
        var initProps = settings.oInit || {};
        var nTable = $(settings.nTable);

        var isDisabled =
            initProps.excelFilter === false ||
            initProps.excelFilter === "false" ||
            initProps.excelfilter === false ||
            initProps.excelfilter === "false" ||
            nTable.attr("data-excel-filter") === "false" ||
            nTable.data("excel-filter") === false;

        if (isDisabled) {
            return;
        }

        $(settings.nTable).initExcelFilter();
    });
})(jQuery);
