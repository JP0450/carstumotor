<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cita #{{ $cita->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cita #{{ $cita->id }}</h1>
        <p>Usuario: {{ $cita->user->name ?? '-' }}</p>
        <p>Vehículo: {{ $cita->vehiculo->modelo ?? '-' }}</p>
        <p>Fecha: {{ $cita->fecha_cita ?? '-' }}</p>
    </div>

    <p>Observaciones: {{ $cita->observaciones ?? '-' }}</p>
</body>
</html>
