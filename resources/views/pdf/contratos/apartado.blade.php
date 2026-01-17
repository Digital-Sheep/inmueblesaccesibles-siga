<!DOCTYPE html>
<html>
<head>
    <title>Contrato de Apartado</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; font-weight: bold; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">CONTRATO DE APARTADO</div>

    <p>
        En la ciudad de {{ $propiedad->municipio->nombre ?? '________' }}, a {{ now()->format('d/m/Y') }},
        comparecen por una parte <strong>INMUEBLES ACCESIBLES</strong>...
    </p>

    <p>
        El cliente: <strong>{{ $cliente->nombre_completo }}</strong><br>
        Aparta la propiedad ubicada en: <strong>{{ $propiedad->direccion_completa }}</strong>
    </p>

    <p>Monto de Apartado: $ {{ number_format($proceso->monto_apartado ?? 0, 2) }}</p>
</body>
</html>
