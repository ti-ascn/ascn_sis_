<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<script>
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
            '(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }

</script>

@php
$isAuditoria = Auth::check() && Auth::user()->area && Auth::user()->area->nombre === 'AUDITORIA';
@endphp

<head>
    <meta charset="utf-8" meta name="robots" content="noindex, nofollow">
    <meta name="google-site-verification" content="Mnl5ydfldKZkxf2NNQt9HZr87BjoauA-FLotFWMLShY" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{ $isAuditoria ? asset('img/sistema-ico.ico') : asset('img/asecon.ico') }}">

    <title>{{ $isAuditoria ? 'SISTEMA' : config('app.name') }}</title>


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    {{-- DATA TABLES
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.0/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.css" />
    BOTONES DE LA DATA TABLE COMO PDF EXCEL COPIAR IMPRIMIR
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css"> --}}

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link href="{{ asset('/js/datatables/css/datatables.css') }}" rel="stylesheet">

    <!-- CDN CSS SELECT 2 -->
    <link href="{{ asset('/js/select2/css/select2.min.css') }}" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Tooltip mejorado (más grande y legible) ── */
        .tooltip {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        /* Texto del tooltip */
        .tooltip .tooltip-text {
            visibility: hidden;
            opacity: 0;
            background-color: #1f2937;
            color: #fff;
            text-align: center;
            border-radius: 8px;

            /* 🔥 mejoras */
            padding: 6px 10px;
            font-size: 0.9rem;
            /* antes 0.7 */
            font-weight: 600;
            line-height: 1.2;

            white-space: nowrap;
            position: absolute;
            bottom: calc(100% + 8px);
            /* más separación */
            left: 50%;
            transform: translateX(-50%);
            transition: opacity 0.2s ease, transform 0.2s ease;
            pointer-events: none;
            z-index: 50;
        }

        /* Flechita */
        .tooltip .tooltip-text::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            /* un poquito más grande */
            border-style: solid;
            border-color: #1f2937 transparent transparent transparent;
        }

        /* Animación más suave */
        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
            transform: translateX(-50%) translateY(-2px);
            /* leve subida */
        }

        [x-cloak] {
            display: none !important;
        }

        nav {
            position: sticky;
            top: 0;
            z-index: 50;
            background-color: white;
            /* Por si el fondo cambia al hacer scroll */
        }

        .dark nav {
            background-color: #22242a;
        }

        /* select2 estilo más parecido a Tailwind */
        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 1px solid #d1d5db;
            /* border-gray-300 */
            border-radius: 0.375rem;
            /* rounded-md */
            height: 38px;
            padding: 0.25rem 0.75rem;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            /* text-sm */
        }

        .select2-selection__arrow {
            top: 6px;
            right: 10px;
        }

        .dropdown-menu-custom {
            z-index: 1050;
            /* Por encima de DataTables */
        }

        /* 🔹 Resetear estilos de paginación de DataTables */
        div.dt-paging {
            position: relative !important;
            /* evita que flote */
            z-index: 0 !important;
            /* lo manda al fondo */
            background: transparent !important;
            /* fuera color raro */
            box-shadow: none !important;
        }

        /* 🔹 Los botones de paginación */
        div.dt-paging button,
        div.dt-paging a {
            z-index: 0 !important;
            position: relative !important;
        }

        /* ── Modern Scrollbars (Gold Theme) ── */
        ::-webkit-scrollbar {
            width: 8px;
            height: 10px;
            /* Un poco más alta para que se vea bien abajo */
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d4af37;
            /* Color Gold */
            border-radius: 10px;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #b8860b;
            /* Darker gold on hover */
        }

        /* Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: #d4af37 rgba(0, 0, 0, 0.05);
        }

        /* ── DataTables Double Scroll Wrapper (Bottom Mirror) ── */
        .dt-double-scroll-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            height: 14px;
            margin-top: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .dark .dt-double-scroll-wrapper {
            background: #22242a;
            border-color: #1f2937;
        }

        .dt-double-scroll-content {
            height: 14px;
        }

        /* Ocultar scrollbar nativo del DataTables (el espejo inferior lo reemplaza) */
        .dataTables_scrollBody,
        .dt-scroll-body,
        .overflow-x-auto {
            overflow-x: auto !important;
            padding-bottom: 0;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE/Edge */
        }

        .dataTables_scrollBody::-webkit-scrollbar,
        .dt-scroll-body::-webkit-scrollbar,
        .overflow-x-auto::-webkit-scrollbar {
            display: none;
            /* Chrome/Safari */
        }

        /* ── Sticky DataTables UI ── */
        .dt-sticky-header {
            position: sticky;
            z-index: 40;
            background: white;
            transition: top 0.2s;
        }

        .dark .dt-sticky-header {
            background: #22242a !important;
        }

        /* Asegurar que las celdas de cabecera sean sticky */
        .dataTables_wrapper thead th {
            position: sticky;
            z-index: 30;
            background: #f3f4f6 !important;
            /* bg-gray-100 */
            box-shadow: inset 0 -1px 0 #d1d5db;
        }

        .dark .dataTables_wrapper thead th {
            background: #2a2d35 !important;
            box-shadow: inset 0 -1px 0 #374151;
        }

        /* Scrollbar espejo fijado en la parte inferior de la ventana */
        .dt-double-scroll-wrapper.sticky-active {
            position: sticky;
            bottom: 0;
            z-index: 45;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .dark .dt-double-scroll-wrapper.sticky-active {
            background: #22242a;
            border-color: #1f2937;
        }

        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }

        /* ── Dark Mode Overrides for DataTables ── */
        .dark .dataTables_wrapper,
        .dark .dt-container {
            color: #e5e7eb;
        }

        .dark .dataTables_wrapper thead th,
        .dark .dt-container thead th {
            background-color: #2a2d35 !important;
            color: #f3f4f6 !important;
            border-bottom: 1px solid #374151;
        }

        .dark .dataTables_wrapper tbody td,
        .dark .dt-container tbody td {
            background-color: #22242a !important;
            color: #d1d5db !important;
            border-bottom: 1px solid #1f2937;
        }

        .dark .dataTables_wrapper tbody tr:hover td,
        .dark .dt-container tbody tr:hover td {
            background-color: #2a2d35 !important;
        }

        /* DataTables Paging */
        .dark .dt-paging-button {
            color: #9ca3af !important;
            background-color: transparent !important;
        }

        .dark .dt-paging-button.current {
            background-color: #2a2d35 !important;
            color: white !important;
        }

        .dark .dt-paging-button:hover {
            background-color: #2a2d35 !important;
        }

        /* Search and Info */
        .dark .dataTables_filter input,
        .dark .dt-search input {
            background-color: #2a2d35;
            color: white;
            border: 1px solid #374151;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
        }

        .dark .dataTables_info,
        .dark .dt-info {
            color: #9ca3af;
        }

        /* Sticky Headers and Double Scroll in Dark Mode */
        .dark .dt-sticky-header,
        .dark .dt-double-scroll-wrapper,
        .dark .dt-double-scroll-wrapper.sticky-active,
        .dark .dt-buttons,
        .dark .dt-layout-row:first-child {
            background-color: #22242a !important;
            border-bottom-color: #374151 !important;
            border-top-color: #374151 !important;
        }

        /* Select2 Dark Mode Fixes */
        .dark .select2-container--default .select2-selection--single {
            background-color: #2a2d35 !important;
            border-color: #374151 !important;
            color: #f3f4f6 !important;
        }

        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f3f4f6 !important;
        }

        .dark .select2-dropdown {
            background-color: #2a2d35 !important;
            border-color: #374151 !important;
            color: #f3f4f6 !important;
        }

        .dark .select2-results__option {
            background-color: #2a2d35 !important;
            color: #f3f4f6 !important;
        }

        .dark .select2-results__option--highlighted[aria-selected] {
            background-color: #4b5563 !important;
            color: white !important;
        }

        /* Modern Scrollbars Dark Mode */
        .dark ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .dark .dt-double-scroll-wrapper {
            background: #22242a;
            border-top: 1px solid #1f2937;
        }

        /* Support for hardcoded Tailwind classes in legacy views */
        .dark .text-gray-400,
        .dark .text-gray-500,
        .dark .text-gray-600,
        .dark .text-gray-700,
        .dark .text-gray-800,
        .dark .text-gray-900,
        .dark .text-slate-400,
        .dark .text-slate-500,
        .dark .text-slate-600,
        .dark .text-slate-700,
        .dark .text-slate-800,
        .dark .text-slate-900 {
            color: #f3f4f6 !important;
        }

        .dark ::placeholder {
            color: #9ca3af !important;
        }

        .dark .bg-white:not(.sidebar-logo):not(.select2-container):not(header) {
            background-color: #2a2d35 !important;
        }

        .dark .border-gray-100,
        .dark .border-gray-200,
        .dark .border-b {
            border-color: #374151 !important;
        }

        /* DataTables Controls Refinement */
        .dark .dataTables_length,
        .dark .dataTables_filter,
        .dark .dataTables_info,
        .dark .dataTables_paginate {
            color: #d1d5db !important;
            background-color: #22242a !important;
            padding: 0.5rem 0;
            border-color: #374151 !important;
        }

        .dark .dataTables_wrapper .dataTables_filter input,
        .dark .dataTables_wrapper .dataTables_length select {
            border-color: #374151 !important;
            background-color: #2a2d35 !important;
            color: #f3f4f6 !important;
        }

        .dark .dt-layout-row,
        .dark .dataTables_wrapper .dt-layout-row {
            background-color: #22242a !important;
            border-color: #374151 !important;
            border-bottom-width: 0 !important;
        }

        .dark .dt-layout-cell {
            padding: 0.75rem 1rem !important;
            border: none !important;
        }

        /* ── Modern Hover and Link Fixes for Dark Mode ── */
        .dark .empresa-link,
        .dark a.text-gray-800,
        .dark a.text-gray-700 {
            color: #93c5fd !important;
            /* Lighter blue for links */
        }

        .dark .empresa-link:hover,
        .dark a:hover {
            color: #bfdbfe !important;
            /* Even lighter on hover */
        }

        .dark .mini-estado {
            color: #9ca3af !important;
        }

        /* Catch-all for hardcoded dark colors if possible */
        .dark [style*="color: #111827"],
        .dark [style*="color:#111827"] {
            color: #f3f4f6 !important;
        }

        /* ── Native Inputs / Selects / Textareas Dark Mode ── */
        .dark input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="range"]),
        .dark textarea,
        .dark select {
            background-color: #2a2d35 !important;
            color: #f3f4f6 !important;
            border-color: #374151 !important;
        }

        .dark .bg-emerald-100 {
            background-color: #a7f3d0 !important;
            color: #065f46 !important;
        }

        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #9ca3af !important;
        }

        /* ── Labels in Dark Mode ── */
        .dark label {
            color: #d1d5db;
        }

        /* ── Table header and Badge bg fixes ── */
        .dark .bg-gray-100,
        .dark .bg-gray-50,
        .dark .bg-blue-50,
        .dark .bg-blue-100,
        .dark .bg-green-50,
        .dark .bg-green-100,
        .dark .bg-emerald-50,
        .dark .bg-emerald-100,
        .dark .bg-orange-50,
        .dark .bg-orange-100,
        .dark .bg-amber-50,
        .dark .bg-amber-100,
        .dark .bg-yellow-50,
        .dark .bg-yellow-100,
        .dark .bg-red-50,
        .dark .bg-red-100,
        .dark .bg-indigo-50,
        .dark .bg-indigo-100,
        .dark .bg-purple-50,
        .dark .bg-purple-100,
        .dark .bg-violet-50,
        .dark .bg-violet-100,
        .dark .bg-teal-50,
        .dark .bg-teal-100,
        .dark tr.bg-gray-100,
        .dark tr.bg-gray-50 {
            background-color: #1a1c21 !important;
            color: #f3f4f6 !important;
            border-color: #374151 !important;
        }

        .dark .text-blue-800,
        .dark .text-green-800,
        .dark .text-emerald-800,
        .dark .text-orange-800,
        .dark .text-yellow-800,
        .dark .text-red-800,
        .dark .text-indigo-800 {
            color: #d1d5db !important;
        }

        /* ── Action Color Refinements for Dark Mode (preserving hue but adjusting brightness) ── */
        .dark .text-green-600,
        .dark .text-emerald-600,
        .dark .hover\:text-green-800:hover,
        .dark .hover\:text-emerald-800:hover {
            color: #4ade80 !important;
        }

        /* green-400 */
        .dark .text-blue-600,
        .dark .hover\:text-blue-800:hover {
            color: #60a5fa !important;
        }

        /* blue-400 */
        .dark .text-red-600,
        .dark .hover\:text-red-800:hover {
            color: #f87171 !important;
        }

        /* red-400 */
        .dark .text-orange-600,
        .dark .hover\:text-orange-800:hover {
            color: #fb923c !important;
        }

        /* orange-400 */
        .dark .text-yellow-600,
        .dark .hover\:text-yellow-800:hover {
            color: #fbbf24 !important;
        }

        /* yellow-400 */
        .dark .text-indigo-600,
        .dark .hover\:text-indigo-800:hover {
            color: #818cf8 !important;
        }

        /* indigo-400 */

        .dark .text-emerald-500,
        .dark .text-orange-500,
        .dark .text-blue-500,
        .dark .text-violet-500,
        .dark .text-amber-500,
        .dark .text-teal-500 {
            color: #93c5fd !important;
        }

        /* ── Date Picker Fixes ── */
        .dark input[type="date"]::-webkit-calendar-picker-indicator,
        .dark input[type="month"]::-webkit-calendar-picker-indicator,
        .dark input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.5);
            cursor: pointer;
        }

        /* ── Table rows hover fix ── */
        .dark .hover\:bg-gray-50:hover,
        .dark tr:hover td,
        .dark #tabla-abonos-facturas tbody tr:hover td {
            background-color: #2d3039 !important;
        }

        /* Ultra-high specificity selection overrides to beat hardcoded view styles and hover states */
        .dark tr.fila-seleccionada td,
        .dark table tr.fila-seleccionada td,
        .dark tr.fila-seleccionada:hover td,
        .dark table tr.fila-seleccionada:hover td,
        html.dark .fila-seleccionada td,
        html.dark tr.fila-seleccionada td,
        html.dark tr.fila-seleccionada:hover td {
            background-color: #1d4ed8 !important;
            /* blue-700 - strong blue */
            color: #ffffff !important;
        }

        .dark .empresa-link {
            color: #93c5fd !important;
        }

        /* ── Select2 options list ── */
        .dark .select2-results__option {
            background-color: #22242a;
            color: #f3f4f6;
        }

        .dark .select2-results__option--highlighted {
            background-color: #2a2d35 !important;
            color: #f3f4f6 !important;
        }

        .dark .select2-search--dropdown .select2-search__field {
            background-color: #2a2d35;
            color: #f3f4f6;
            border-color: #374151;
        }

        /* ── Accordion nav links text color ── */
        .dark .nav-accordion-link,
        .dark .block.px-4.py-2.text-sm {
            color: #d1d5db !important;
        }

        .dark .block.px-4.py-2.text-sm.text-gray-500 {
            color: #d1d5db !important;
        }

        .dark .block.px-4.py-2.text-sm:hover {
            color: #fbbf24 !important;
        }

        /* ── Card / Paper backgrounds ── */
        .dark .shadow-lg.rounded-lg,
        .dark .shadow.rounded-lg,
        .dark .shadow-sm.rounded-lg {
            background-color: #2a2d35;
        }

        /* ── Preservar colores de badges en modo oscuro ── */
        .dark .bg-purple-100.text-purple-700,
        .dark .bg-purple-100 {
            background-color: #e9d5ff !important;
            color: #7e22ce !important;
        }

        .dark .bg-green-100.text-green-700,
        .dark .bg-green-100 {
            background-color: #dcfce7 !important;
            color: #15803d !important;
        }

        .dark .bg-blue-100.text-blue-700,
        .dark .bg-blue-100 {
            background-color: #dbeafe !important;
            color: #1d4ed8 !important;
        }

        .dark .bg-yellow-100.text-yellow-700,
        .dark .bg-yellow-100 {
            background-color: #fef3c7 !important;
            color: #a16207 !important;
        }

        .dark .bg-red-100.text-red-700,
        .dark .bg-red-100 {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
        }

        .dark .bg-emerald-100.text-emerald-700,
        .dark .bg-emerald-100 {
            background-color: #d1fae5 !important;
            color: #047857 !important;
        }

        .dark .bg-indigo-100.text-indigo-700,
        .dark .bg-indigo-100 {
            background-color: #e0e7ff !important;
            color: #4338ca !important;
        }

        .dark .bg-orange-100.text-orange-700,
        .dark .bg-orange-100 {
            background-color: #ffedd5 !important;
            color: #c2410c !important;
        }

        .dark .bg-pink-100.text-pink-700,
        .dark .bg-pink-100 {
            background-color: #fce7f3 !important;
            color: #be185d !important;
        }

        .dark .bg-teal-100.text-teal-700,
        .dark .bg-teal-100 {
            background-color: #ccfbf1 !important;
            color: #0f766e !important;
        }

        /* Preservar bordes de badges */
        .dark .border-purple-200 {
            border-color: #d8b4fe !important;
        }

        .dark .border-green-200 {
            border-color: #bbf7d0 !important;
        }

        .dark .border-blue-200 {
            border-color: #bfdbfe !important;
        }

        .dark .border-yellow-200 {
            border-color: #fde68a !important;
        }

        .sidebar-transition {
            transition: width 0.3s ease-in-out, transform 0.3s ease-in-out, visibility 0.3s ease-in-out, border-color 0.3s ease-in-out;
        }

    </style>
</head>

<body class="font-sans antialiased dark:bg-[#22242a] dark:text-gray-100">
    <!-- Loader -->
    <div id="loader" class="fixed inset-0 flex items-center justify-center bg-white dark:bg-[#22242a] z-50">
        <div class="w-12 h-12 border-4 border-yellow-500 border-dashed rounded-full animate-spin"></div>
    </div>
    <div class="min-h-screen flex bg-gray-50 overflow-hidden" x-data="{
        sidebarOpen: window.innerWidth >= 1024 ? (localStorage.getItem('sidebar-open') !== null ? localStorage.getItem('sidebar-open') === 'true' : true) : false,
        isMobile: window.innerWidth < 1024,
        init() {
            this.$watch('sidebarOpen', value => {
                if (!this.isMobile) {
                    localStorage.setItem('sidebar-open', value);
                }
            });
        }
    }" @resize.window="isMobile = window.innerWidth < 1024; if(!isMobile) sidebarOpen = (localStorage.getItem('sidebar-open') !== null ? localStorage.getItem('sidebar-open') === 'true' : true)" x-cloak>

        <!-- Sidebar Navigation -->
        <div x-cloak class="sidebar-transition fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-200 lg:relative shrink-0 shadow-xl lg:shadow-none overflow-hidden" :class="{
                'w-72 translate-x-0 visible lg:transform-none': sidebarOpen,
                'w-0 -translate-x-full border-r-0 lg:translate-x-0 invisible lg:visible lg:transform-none': !sidebarOpen && !isMobile,
                'w-72 -translate-x-full invisible': !sidebarOpen && isMobile
            }">
            @include('layouts.navigation')
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden relative">
            <!-- Fixed Light Header -->
            <header class="min-h-[64px] lg:min-h-[80px] py-2 shrink-0 flex items-center justify-between px-3 lg:px-6 bg-white dark:bg-[#22242a] border-b border-gray-200 dark:border-gray-800 z-30">
                <div class="flex-1 flex items-center gap-2 lg:gap-4 min-w-0">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors shrink-0">
                        <i class="bi bi-list text-2xl"></i>
                    </button>

                    <div class="flex-1 flex items-center gap-1.5 lg:gap-3 min-w-0">
                        @if (!request()->routeIs('dashboard'))
                        <button onclick="window.history.back()" class="flex items-center justify-center w-7 h-7 lg:w-8 lg:h-8 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:bg-amber-50 dark:hover:bg-amber-900/40 hover:text-amber-600 dark:hover:text-amber-500 transition-all border border-gray-100 dark:border-gray-700 shadow-sm shrink-0" title="Volver atrás">
                            <i class="bi bi-arrow-left text-base lg:text-lg"></i>
                        </button>
                        @endif

                        <div class="flex flex-col min-w-0">
                            <div class="text-xs lg:text-base font-black text-gray-800 dark:text-gray-100 leading-tight uppercase tracking-tight">
                                @isset($header)
                                {{ $header }}
                                @else
                                Panel Principal
                                @endisset
                            </div>

                            <!-- Enhanced Breadcrumbs (Visual Only) -->
                            <nav class="flex text-[8px] lg:text-[10px] mt-0.5 items-center gap-1 lg:gap-2 overflow-hidden" aria-label="Breadcrumb">
                                <span class="flex items-center text-gray-400 dark:text-gray-400 font-bold uppercase tracking-wider shrink-0">
                                    <i class="bi bi-house-door-fill mr-0.5 lg:mr-1 text-[9px] lg:text-[11px]"></i>
                                    Inicio
                                </span>

                                @php
                                $routeName = request()->route()->getName();
                                $segments = explode('.', $routeName);
                                $filteredSegments = array_filter($segments, function ($s) {
                                return !in_array($s, ['index', 'dashboard', 'all', 'panel']);
                                });
                                @endphp

                                @foreach ($filteredSegments as $segment)
                                <span class="text-gray-300 dark:text-gray-500 shrink-0"><i class="bi bi-chevron-right"></i></span>
                                <span class="text-gray-400 dark:text-gray-400 font-bold uppercase tracking-wider truncate">
                                    {{ str_replace(['_', '-'], ' ', $segment) }}
                                </span>
                                @endforeach

                                @if (str_contains($routeName, 'create'))
                                <span class="text-gray-300 dark:text-gray-600 shrink-0"><i class="bi bi-chevron-right"></i></span>
                                <span class="px-1 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-500 rounded-md font-black uppercase tracking-widest text-[7px] lg:text-[9px] shrink-0">Crear</span>
                                @elseif(str_contains($routeName, 'edit'))
                                <span class="text-gray-300 dark:text-gray-600 shrink-0"><i class="bi bi-chevron-right"></i></span>
                                <span class="px-1 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-500 rounded-md font-black uppercase tracking-widest text-[7px] lg:text-[9px] shrink-0">Editar</span>
                                @elseif(str_contains($routeName, 'show'))
                                <span class="text-gray-300 dark:text-gray-600 shrink-0"><i class="bi bi-chevron-right"></i></span>
                                <span class="px-1.5 lg:px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-500 rounded-md font-black uppercase tracking-widest text-[8px] lg:text-[9px] shrink-0">Detalles</span>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 lg:gap-4 shrink-0 ml-2">
                    <!-- User Profile -->
                    <div class="flex items-center gap-2 lg:gap-3 pl-2 lg:pl-4 border-l border-gray-100">
                        <div class="hidden md:flex flex-col items-end">
                            <span class="text-xs lg:text-sm font-bold text-gray-800 leading-none">{{ Auth::user()->name }}</span>
                            <span class="text-[9px] lg:text-[10px] text-amber-600 font-bold uppercase tracking-widest mt-1">{{ Auth::user()->area->nombre }}</span>
                        </div>
                        <a href="{{ route('profile.edit') }}">
                            <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-xl bg-amber-500 flex items-center justify-center text-white font-black shadow-lg shadow-amber-200/50 dark:shadow-amber-900/20 shrink-0">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Page Content (Scrollable Area) -->
            <main class="flex-1 overflow-y-auto p-3 lg:p-6 custom-scrollbar relative bg-gray-50/50 dark:bg-[#1a1c21]">
                <div class="w-full">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Mobile Overlay Backdrop -->
        <div x-show="isMobile && sidebarOpen" x-transition:opacity class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40" @click="sidebarOpen = false" style="display: none;">
        </div>
    </div>
    <!-- Scripts -->
    {{-- <script src="https://code.jquery.com/jquery-3.7.1.js"></script> --}}
    <script src="{{ asset('/js/jquery-3.7.1.js') }}"></script>

    <script src="{{ asset('/js/datatables/datatables.js') }}"></script>
    <script src="{{ asset('/js/datatables/excel-filter.js') }}"></script>
    <script src="{{ asset('/js/select2/js/select2.min.js') }}"></script>
    {{-- PARA LOS GRAFICOS --}}
    <script src="{{ asset('/js/chart.js') }}"></script>

    {{-- <script src="//cdn.datatables.net/2.2.0/js/dataTables.min.js"></script> --}}

    {{-- PARA LAS TABLAS RESPONSIVE
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.dataTables.js"></script>
    IMPLEMENTACION DE BOTONES
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script> --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".format-input").forEach(function(input) {
                input.addEventListener("input", function() {
                    let value = input.value.replace(/,/g, '');
                    if (!isNaN(value) && value !== '') {
                        input.value = Number(value).toLocaleString('en-US', {
                            minimumFractionDigits: 2
                            , maximumFractionDigits: 2
                        });
                    }
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".format-number").forEach(function(element) {
                let value = element.textContent.trim();
                if (!isNaN(value) && value !== '') {
                    element.textContent = Number(value).toLocaleString('en-US', {
                        minimumFractionDigits: 2
                        , maximumFractionDigits: 2
                    });
                }
            });
        });

    </script>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                paging: true, // Habilitar paginación
                searching: true, // Habilitar barra de búsqueda
                ordering: true, // Habilitar ordenamiento
                responsive: true, // Hacer la tabla responsive
                //IMPLEMENTACION DE BOTONES
                /*dom: 'Bfrtip', // Activa el diseño para los botones
                buttons: [
                    //'copy', // Copiar datos al portapapeles
                    //'excel', // Exportar a Excel
                    //'csv', // Exportar a CSV
                    //'pdf', // Exportar a PDF
                    //'print', // Imprimir la tabla
                    {
                        extend: 'excel',
                        title: function() {
                            const fecha = new Date();
                            const fechaFormateada = fecha.toISOString().slice(0,
                                10); // Formato: YYYY-MM-DD
                            return `Reporte_ASECON_${fechaFormateada}`;
                        }
                    },
                    {
                        extend: 'pdf',
                        title: function() {
                            const fecha = new Date();
                            const fechaFormateada = fecha.toISOString().slice(0,
                                10); // Formato: YYYY-MM-DD
                            return `Reporte_ASECON_${fechaFormateada}`;
                        },
                        //orientation: 'landscape', // Opcional: orientación horizontal
                        pageSize: 'A4' // Tamaño de página
                    }

                ],*/
                language: {
                    url: "{{ asset('/js/datatables/lang/es-ES.json') }}" // Textos en español
                }
            });
        });

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function setupFlashMessage(message, autoDelay = 5000) {
                if (!message) return;

                const progressBar = message.querySelector('.progress-bar');
                let timeout;
                let startTime;
                let totalTime;

                function startTimer(time, fromPercent = 100) {
                    clearTimeout(timeout);
                    totalTime = time;
                    startTime = Date.now();

                    timeout = setTimeout(() => dismissMessage(message), time);

                    if (progressBar) {
                        progressBar.style.transition = 'none';
                        progressBar.style.width = fromPercent + '%';
                        progressBar.offsetWidth;
                        progressBar.style.transition = `width ${time}ms linear`;
                        progressBar.style.width = '0%';
                    }
                }



                function dismissMessage(element) {
                    element.classList.add('opacity-0', 'h-0', 'py-0', 'mb-0', 'border-0', 'overflow-hidden');
                    setTimeout(() => element.remove(), 500);
                }

                // Auto cerrar
                startTimer(autoDelay);

                // Pausar al hover
                let pausedPercent = 100;

                message.addEventListener('mouseenter', () => {
                    clearTimeout(timeout);
                    if (!progressBar) return;

                    const barWidth = progressBar.getBoundingClientRect().width;
                    const parentWidth = progressBar.parentElement.getBoundingClientRect().width;

                    pausedPercent = (barWidth / parentWidth) * 100;

                    progressBar.style.transition = 'none';
                    progressBar.style.width = pausedPercent + '%';
                });

                message.addEventListener('mouseleave', () => {
                    if (!progressBar) return;

                    const remaining = Math.max(totalTime * (pausedPercent / 100), 200);
                    startTimer(remaining, pausedPercent);
                });

                // Click para cerrar (excepto botón)
                message.addEventListener('click', (e) => {
                    if (!e.target.closest('button')) {
                        clearTimeout(timeout);
                        dismissMessage(message);
                    }
                });

                // Botón X
                const closeBtn = message.querySelector('button');
                if (closeBtn) {
                    closeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        clearTimeout(timeout);
                        dismissMessage(message);
                    });
                }
            }

            setupFlashMessage(document.getElementById('success-message'), 5000);
            setupFlashMessage(document.getElementById('error-message'), 5000);
        });

    </script>
    {{-- BLOQUEAR BOTON --}}
    <script src="{{ asset('/js/blockForm.js') }}"></script>
    <script>
        window.addEventListener("load", function() {
            document.getElementById("loader").style.display = "none";
        });

    </script>
    <script>
        document.addEventListener('keydown', function(e) {
            // Usar "/" como atajo
            if (e.altKey && e.key === 'f' && !e.target.matches('input, textarea')) {
                e.preventDefault(); // evita que escriba la barra
                const searchInput = document.querySelector('input[type="search"]');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select(); // opcional: selecciona el texto si ya había algo
                }
            }
        });

        // ── Global DataTables Improvements ──
        function applyDataTableUX() {
            const navHeight = $('nav').outerHeight() || 64;

            $('.dt-scroll-body, .dataTables_scrollBody, .overflow-x-auto').each(function() {
                const $container = $(this);
                const $wrapper = $container.closest('.dataTables_wrapper, .dt-container');

                if ($container.data('double-scroll-init')) {
                    // Actualizar posiciones si ya existe
                    const $mirror = $wrapper.find('.dt-double-scroll-wrapper');
                    $mirror.addClass('sticky-active');
                    $wrapper.find('.dataTables_filter, .dt-buttons, .dt-layout-row:first-child').css({
                        'position': 'sticky'
                        , 'top': navHeight + 'px'
                        , 'z-index': 35
                        , 'background': document.documentElement.classList.contains('dark') ? '#22242a' : 'white'
                        , 'padding': '10px 0'
                    });
                    $wrapper.find('thead th').css('top', (navHeight + 50) + 'px');
                    return;
                }

                // 1. Double Scroll (Bottom Mirror)
                const $mirror = $(
                    '<div class="dt-double-scroll-wrapper sticky-active"><div class="dt-double-scroll-content"></div></div>'
                );
                $mirror.insertAfter($container);

                const updateScroll = () => {
                    const scrollWidth = $container[0].scrollWidth;
                    const clientWidth = $container[0].clientWidth;
                    $mirror.find('.dt-double-scroll-content').width(scrollWidth);
                    $mirror.toggle(scrollWidth > clientWidth);
                };

                $container.on('scroll', function() {
                    $mirror.scrollLeft($container.scrollLeft());
                });

                $mirror.on('scroll', function() {
                    $container.scrollLeft($mirror.scrollLeft());
                });

                // 2. Sticky Header Logic
                const $filters = $wrapper.find('.dataTables_filter, .dt-buttons, .dt-layout-row:first-child');
                $filters.css({
                    'position': 'sticky'
                    , 'top': navHeight + 'px'
                    , 'z-index': 35
                    , 'background': document.documentElement.classList.contains('dark') ? '#22242a' : 'white'
                    , 'padding': '10px 0'
                });

                $wrapper.find('thead th').css('top', (navHeight + 50) + 'px');

                $(window).on('resize', () => {
                    updateScroll();
                });
                setTimeout(() => {
                    updateScroll();
                }, 500);
                $container.data('double-scroll-init', true);
            });
        }

        // Run UX improvements after any DataTable init
        $(document).on('init.dt', function() {
            setTimeout(applyDataTableUX, 200);
        });

        // Periodic check for dynamic content
        setInterval(applyDataTableUX, 2000);

    </script>
    <x-ajax-toast />
</body>

</html>
