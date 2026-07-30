<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Calendario de Pagos Generados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-success-error-bagdes />
            <div class="bg-white shadow-lg rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <button onclick="changeMonth(-1)"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">Anterior</button>
                    <h3 id="calendar-month" class="text-xl font-bold text-gray-700"></h3>
                    <button onclick="changeMonth(1)"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">Siguiente</button>
                </div>
                <div id="calendar" class="overflow-x-auto"></div>
            </div>
        </div>
    </div>

    <!-- Modal de Información de Pago -->
    <div id="payment-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-md w-96 shadow-xl relative">
            <h3 id="payment-title" class="text-xl font-semibold mb-2"></h3>
            <div id="payment-dropdown-container" class="mb-2 hidden">
                <label class="text-sm font-medium text-gray-700">Selecciona un pago:</label>
                <select id="payment-dropdown" onchange="handleDropdownChange()" class="w-full mt-1 p-2 border rounded">
                </select>
            </div>
            <div id="payment-details">
                <p><strong>Descripción:</strong> <span id="payment-description"></span></p>
                <p><strong>Tipo de Pago:</strong> <span id="payment-type"></span></p>
                <p><strong>Monto:</strong> <span id="payment-amount"></span></p>
                <p><strong>Estado:</strong> <span id="payment-status"></span></p>
            </div>
            <button onclick="closePaymentModal()"
                class="mt-4 w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md">Cerrar</button>
            <form id="mark-as-paid-form" method="POST" class="mt-4 hidden">
                @csrf
                @method('PATCH')
                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md">
                    Marcar como Pagado
                </button>
            </form>
        </div>
    </div>
</x-app-layout>

<script>
    const pagosGenerados = @json($pagosGenerados);
    let currentDate = new Date();
    let currentDropdownPayments = [];

    function generateCalendar() {
        const calendarElement = document.getElementById('calendar');
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();

        const mes = currentDate.toLocaleDateString('es-PE', {
            month: 'long'
        }).toUpperCase();
        const año = currentDate.getFullYear();
        document.getElementById('calendar-month').innerText = `${mes} de ${año}`;


        const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
        const lastDayOfMonth = new Date(currentYear, currentMonth + 1, 0);
        const firstDay = firstDayOfMonth.getDay();
        const lastDate = lastDayOfMonth.getDate();

        let daysHTML = '<table class="min-w-full table-auto border-collapse">';
        daysHTML += '<thead><tr class="bg-gray-200 text-gray-700">';
        daysHTML +=
            '<th class="px-4 py-2">Dom</th><th class="px-4 py-2">Lun</th><th class="px-4 py-2">Mar</th><th class="px-4 py-2">Mié</th><th class="px-4 py-2">Jue</th><th class="px-4 py-2">Vie</th><th class="px-4 py-2">Sáb</th>';
        daysHTML += '</tr></thead><tbody><tr>';

        for (let i = 0; i < firstDay; i++) {
            daysHTML += '<td class="px-4 py-2"></td>';
        }

        for (let day = 1; day <= lastDate; day++) {
            const fullDate =
                `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            const dayEvents = pagosGenerados.filter(p => p.fecha === fullDate);

            if ((firstDay + day - 1) % 7 === 0 && day !== 1) {
                daysHTML += '</tr><tr>';
            }

            let content =
                `<td class="border p-2 align-top cursor-pointer hover:bg-blue-100" onclick="showPaymentDetails('${fullDate}')">`;
            content += `<div class="font-semibold">${day}</div>`;

            dayEvents.forEach(event => {
                let desc = event.pago_programado?.descripcion || 'Sin descripción';
                const montoFormateado = parseFloat(event.monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,
                    ",");
                content += `<div class="text-xs text-gray-600 truncate">${desc} - S/. ${montoFormateado}</div>`;
            });


            content += '</td>';
            daysHTML += content;
        }

        const remainingDays = 7 - ((firstDay + lastDate - 1) % 7 + 1);
        for (let i = 0; i < remainingDays; i++) {
            daysHTML += '<td class="px-4 py-2"></td>';
        }

        daysHTML += '</tr></tbody></table>';
        calendarElement.innerHTML = daysHTML;
    }

    function displayPaymentDetails(payment) {
        const [año, mes, dia] = payment.fecha.split('-');
        document.getElementById('payment-title').innerText = `Pago del ${dia}/${mes}/${año}`;
        document.getElementById('payment-description').innerText = payment.pago_programado?.descripcion ||
            'Descripción no disponible';
        document.getElementById('payment-type').innerText = payment.pago_programado?.tipo_pago?.nombre ||
            'Tipo de pago no disponible';

        const montoFormateado = parseFloat(payment.monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        document.getElementById('payment-amount').innerText = `S/. ${montoFormateado}`;

        const statusElement = document.getElementById('payment-status');
        const markForm = document.getElementById('mark-as-paid-form');

        if (payment.pagado) {
            // Extraer SOLO la parte de la fecha (sin hora) para comparación
            const fechaProgramada = payment.fecha; // Formato: "YYYY-MM-DD"
            const fechaPago = payment.fecha_pago.split(' ')[0]; // Si tiene hora, la removemos

            // Si la fecha de pago es igual o anterior a la programada → Pago puntual
            statusElement.innerHTML = fechaPago <= fechaProgramada ?
                `<span class="text-green-600 font-semibold">Pago puntual</span>` :
                `<span class="text-red-600 font-semibold">Pago tardío</span>`;

            markForm.classList.add('hidden'); // Ocultar botón si ya está pagado
        } else {
            statusElement.innerHTML = `<span class="text-yellow-600 font-semibold">Pendiente</span>`;
            markForm.classList.remove('hidden');
            markForm.action = `/pagos-generados/${payment.id_pago_generado}/marcar-pagado`;
        }

        document.getElementById('payment-details').classList.remove('hidden');
    }


    window.changeMonth = function(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        generateCalendar();
    };

    window.showPaymentDetails = function(date) {
        const selectedPayments = pagosGenerados.filter(p => p.fecha === date);

        // Verificar si hay pagos generados en esa fecha
        if (selectedPayments.length === 0) {
            return; // Si no hay pagos, no hacer nada
        }

        currentDropdownPayments = selectedPayments;

        if (selectedPayments.length > 1) {
            // Mostrar dropdown
            const dropdown = document.getElementById('payment-dropdown');
            dropdown.innerHTML = '';
            selectedPayments.forEach((p, index) => {
                let text = `${p.pago_programado?.descripcion || 'Sin desc.'} - S/. ${p.monto}`;
                dropdown.innerHTML += `<option value="${index}">${text}</option>`;
            });

            document.getElementById('payment-dropdown-container').classList.remove('hidden');
            displayPaymentDetails(selectedPayments[0]);

        } else if (selectedPayments.length === 1) {
            document.getElementById('payment-dropdown-container').classList.add('hidden');
            displayPaymentDetails(selectedPayments[0]);
        }

        document.getElementById('payment-modal').classList.remove('hidden');
    };

    window.handleDropdownChange = function() {
        const index = document.getElementById('payment-dropdown').value;
        const selected = currentDropdownPayments[index];
        displayPaymentDetails(selected);
    };

    window.closePaymentModal = function() {
        document.getElementById('payment-modal').classList.add('hidden');
    };

    generateCalendar();
</script>
