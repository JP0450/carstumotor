<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte financiero</title>
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
        <h1>Reporte financiero</h1>
        <p>{{ $period ?? '' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cuenta</th>
                <th>Débito</th>
                <th>Crédito</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr>
                    <td>{{ $line['name'] }}</td>
                    <td>{{ $line['debit'] }}</td>
                    <td>{{ $line['credit'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
