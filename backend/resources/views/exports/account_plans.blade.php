<html>
<head>
    <title>Plan Comptable</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Plan Comptable</h2>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Code</th>
            </tr>
        </thead>
        <tbody>
        @foreach($accountPlans as $plan)
            <tr>
                <td>{{ $plan->name }}</td>
                <td>{{ $plan->code }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
