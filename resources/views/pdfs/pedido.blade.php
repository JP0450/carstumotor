<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pedido #{{ $pedido->id }}</h1>
        <p>Cliente: {{ $pedido->user->name ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Vehículo</th>
                <th>Cantidad</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->items ?? [] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['price'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Total: {{ $pedido->total ?? '0' }}</p>
</body>
</html>
