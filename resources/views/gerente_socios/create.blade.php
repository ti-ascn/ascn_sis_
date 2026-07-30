<form method="POST"
    action="{{ $modo === 'edit'
        ? route('gerente_socios.update', $empresa->id_empresas_clientes)
        : route('gerente_socios.store') }}">
    @csrf
    @if ($modo === 'edit')
        @method('PUT')
    @endif