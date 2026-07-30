<x-app-layout>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Consultas SUNAT</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-gray-100 min-h-screen">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Consultas SUNAT</h1>
                    <p class="text-gray-600">Consulta tipo de cambio y datos de RUC</p>
                </div>

                <!-- Consulta de Precio del Dólar -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Tipo de Cambio del Dólar</h2>

                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 mb-4">
                        <button id="tabHoy"
                            class="tab-button px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600">
                            Precio de Hoy
                        </button>
                        <button id="tabFecha"
                            class="tab-button px-4 py-2 font-medium text-gray-500 hover:text-gray-700">
                            Consultar por Fecha
                        </button>
                        <button id="tabMes"
                            class="tab-button px-4 py-2 font-medium text-gray-500 hover:text-gray-700">
                            Historial del Mes
                        </button>
                    </div>

                    <!-- Contenido Tab: Hoy -->
                    <div id="contentHoy" class="tab-content">
                        <p class="text-sm text-gray-600 mb-4">Tipo de cambio SUNAT actual</p>
                        <button id="btnConsultarDolar" type="button"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                            Consultar Precio del Dólar Hoy
                        </button>
                    </div>

                    <!-- Contenido Tab: Por Fecha -->
                    <div id="contentFecha" class="tab-content hidden">
                        <form id="formConsultarFecha" class="space-y-4">
                            <div>
                                <label for="fechaConsulta" class="block text-sm font-medium text-gray-700 mb-2">
                                    Seleccione la fecha
                                </label>
                                <input type="date" id="fechaConsulta" name="fecha" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200">
                                Consultar Tipo de Cambio
                            </button>
                        </form>
                    </div>

                    <!-- Contenido Tab: Mes -->
                    <div id="contentMes" class="tab-content hidden">
                        <form id="formConsultarMes" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="mes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Mes
                                    </label>
                                    <select id="mes" name="mes" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="1">Enero</option>
                                        <option value="2">Febrero</option>
                                        <option value="3">Marzo</option>
                                        <option value="4">Abril</option>
                                        <option value="5">Mayo</option>
                                        <option value="6">Junio</option>
                                        <option value="7">Julio</option>
                                        <option value="8">Agosto</option>
                                        <option value="9">Septiembre</option>
                                        <option value="10">Octubre</option>
                                        <option value="11">Noviembre</option>
                                        <option value="12">Diciembre</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="anio" class="block text-sm font-medium text-gray-700 mb-2">
                                        Año
                                    </label>
                                    <input type="number" id="anio" name="anio" min="2000" max="2100"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200">
                                Ver Historial del Mes
                            </button>
                        </form>
                    </div>

                    <!-- Resultado Tipo de Cambio -->
                    <div id="resultadoTipoCambio" class="mt-6 hidden"></div>
                </div>
            </div>
        </div>

        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Manejo de Tabs
            const tabs = document.querySelectorAll('.tab-button');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', function() {
                    // Remover active de todos
                    tabs.forEach(t => {
                        t.classList.remove('text-blue-600', 'border-blue-600');
                        t.classList.add('text-gray-500');
                    });
                    contents.forEach(c => c.classList.add('hidden'));

                    // Activar seleccionado
                    this.classList.remove('text-gray-500');
                    this.classList.add('text-blue-600', 'border-blue-600');
                    contents[index].classList.remove('hidden');

                    // Limpiar resultados
                    document.getElementById('resultadoTipoCambio').classList.add('hidden');
                });
            });

            // Inicializar fechas
            const hoy = new Date();
            document.getElementById('fechaConsulta').valueAsDate = hoy;
            document.getElementById('mes').value = hoy.getMonth() + 1;
            document.getElementById('anio').value = hoy.getFullYear();

            // Funciones auxiliares globales
            function getNombreMes(mes) {
                const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ];
                return meses[mes - 1];
            }

            function formatearFecha(fechaISO) {
                const fecha = new Date(fechaISO + 'T00:00:00');
                return fecha.toLocaleDateString('es-PE', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }

            // Función para copiar precio al portapapeles
            window.copiarPrecio = async function(precio, tipo) {
                try {
                    await navigator.clipboard.writeText(precio);
                    mostrarNotificacion(`Precio de ${tipo} copiado: S/ ${precio}`);
                } catch (err) {
                    console.error('Error al copiar:', err);
                    mostrarNotificacion('Error al copiar al portapapeles', 'error');
                }
            }

            // Función para mostrar notificaciones
            function mostrarNotificacion(mensaje, tipo = 'success') {
                const notif = document.createElement('div');
                notif.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 z-50 ${
                tipo === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
                notif.innerHTML = `
                <div class="flex items-center gap-2">
                    ${tipo === 'success' ? `
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            ` : `
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            `}
                    <span>${mensaje}</span>
                </div>
            `;

                document.body.appendChild(notif);
                setTimeout(() => notif.style.transform = 'translateX(0)', 10);
                setTimeout(() => {
                    notif.style.transform = 'translateX(400px)';
                    setTimeout(() => notif.remove(), 300);
                }, 3000);
            }

            // Función para consultar tipo de cambio de un mes
            async function consultarTipoCambioMes(month, year, fechaEspecifica = null) {
                const resultadoDiv = document.getElementById('resultadoTipoCambio');

                resultadoDiv.innerHTML = '<p class="text-gray-600 text-center">Consultando...</p>';
                resultadoDiv.classList.remove('hidden');

                try {
                    const response = await fetch('{{ route('consultas.tipoCambio') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            month,
                            year
                        })
                    });

                    const result = await response.json();

                    if (result.success && result.data) {
                        const datos = Array.isArray(result.data) ? result.data : [result.data];
                        let datosFiltrados = datos;

                        if (fechaEspecifica) {
                            datosFiltrados = datos.filter(item => item.date === fechaEspecifica);
                        }

                        if (fechaEspecifica && datosFiltrados.length > 0) {
                            // Mostrar solo una fecha
                            const item = datosFiltrados[0];
                            let html = `
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6">
                                <div class="text-center mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Tipo de Cambio - ${item.date}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-white rounded-lg p-4 text-center shadow-sm relative group">
                                        <p class="text-sm text-gray-600 mb-1">Compra</p>
                                        <p class="text-3xl font-bold text-blue-600">S/ ${item.buy_price}</p>
                                        <button onclick="copiarPrecio('${item.buy_price}', 'compra')" 
                                            class="absolute top-2 right-2 p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition opacity-0 group-hover:opacity-100"
                                            title="Copiar precio de compra">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 text-center shadow-sm relative group">
                                        <p class="text-sm text-gray-600 mb-1">Venta</p>
                                        <p class="text-3xl font-bold text-green-600">S/ ${item.sell_price}</p>
                                        <button onclick="copiarPrecio('${item.sell_price}', 'venta')" 
                                            class="absolute top-2 right-2 p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition opacity-0 group-hover:opacity-100"
                                            title="Copiar precio de venta">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 text-center mt-4">Fuente: SUNAT</p>
                            </div>
                        `;
                            resultadoDiv.innerHTML = html;
                        } else {
                            // Mostrar tabla del mes
                            let html = `
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                    <h3 class="font-semibold text-gray-800">Tipo de Cambio - ${getNombreMes(month)} ${year}</h3>
                                </div>
                                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 sticky top-0">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compra</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Venta</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                        `;

                            datosFiltrados.forEach(item => {
                                html += `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">${formatearFecha(item.date)}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-blue-600 cursor-pointer group" 
                                        onclick="copiarPrecio('${item.buy_price}', 'compra')"
                                        title="Click para copiar">
                                        <span class="group-hover:underline">S/ ${item.buy_price}</span>
                                        <svg class="w-4 h-4 inline ml-1 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-green-600 cursor-pointer group" 
                                        onclick="copiarPrecio('${item.sell_price}', 'venta')"
                                        title="Click para copiar">
                                        <span class="group-hover:underline">S/ ${item.sell_price}</span>
                                        <svg class="w-4 h-4 inline ml-1 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </td>
                                </tr>
                            `;
                            });

                            html += `
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 text-center">
                                    Fuente: SUNAT - Total de registros: ${datosFiltrados.length}
                                </div>
                            </div>
                        `;
                            resultadoDiv.innerHTML = html;
                        }
                    } else {
                        resultadoDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-red-800 text-center">${result.error || 'No se encontraron datos para la fecha seleccionada'}</p>
                        </div>
                    `;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    resultadoDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-red-800 text-center">Error al realizar la consulta</p>
                    </div>
                `;
                }
            }

            // Botón Consultar Dólar Hoy
            document.getElementById('btnConsultarDolar').addEventListener('click', async function() {
                const resultadoDiv = document.getElementById('resultadoTipoCambio');

                resultadoDiv.innerHTML = '<p class="text-gray-600 text-center">Consultando precio del dólar...</p>';
                resultadoDiv.classList.remove('hidden');

                try {
                    const response = await fetch('{{ route('consultas.tipoCambio') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        }
                    });

                    const result = await response.json();

                    if (result.success && result.data) {
                        const item = result.data;
                        const fecha = item.date || new Date().toLocaleDateString('es-PE');
                        const compra = item.buy_price || 'N/A';
                        const venta = item.sell_price || 'N/A';

                        let html = `
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6">
                            <div class="text-center mb-4">
                                <p class="text-sm text-gray-600 mb-2">Tipo de Cambio - ${fecha}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white rounded-lg p-4 text-center shadow-sm relative group">
                                    <p class="text-sm text-gray-600 mb-1">Compra</p>
                                    <p class="text-3xl font-bold text-blue-600">S/ ${compra}</p>
                                    <button onclick="copiarPrecio('${compra}', 'compra')" 
                                        class="absolute top-2 right-2 p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition opacity-0 group-hover:opacity-100"
                                        title="Copiar precio de compra">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="bg-white rounded-lg p-4 text-center shadow-sm relative group">
                                    <p class="text-sm text-gray-600 mb-1">Venta</p>
                                    <p class="text-3xl font-bold text-green-600">S/ ${venta}</p>
                                    <button onclick="copiarPrecio('${venta}', 'venta')" 
                                        class="absolute top-2 right-2 p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition opacity-0 group-hover:opacity-100"
                                        title="Copiar precio de venta">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 text-center mt-4">Fuente: SUNAT</p>
                        </div>
                    `;
                        resultadoDiv.innerHTML = html;
                    } else {
                        resultadoDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-red-800 text-center">${result.error || 'Error al consultar el precio del dólar'}</p>
                        </div>
                    `;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    resultadoDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-red-800 text-center">Error al realizar la consulta</p>
                    </div>
                `;
                }
            });

            // Formulario Consultar por Fecha
            document.getElementById('formConsultarFecha').addEventListener('submit', async function(e) {
                e.preventDefault();
                const fechaInput = document.getElementById('fechaConsulta').value;
                const fecha = new Date(fechaInput + 'T00:00:00');
                const month = fecha.getMonth() + 1;
                const year = fecha.getFullYear();

                await consultarTipoCambioMes(month, year, fechaInput);
            });

            // Formulario Consultar Mes
            document.getElementById('formConsultarMes').addEventListener('submit', async function(e) {
                e.preventDefault();
                const month = document.getElementById('mes').value;
                const year = document.getElementById('anio').value;

                await consultarTipoCambioMes(month, year);
            });
        </script>
    </body>

    </html>
</x-app-layout>
