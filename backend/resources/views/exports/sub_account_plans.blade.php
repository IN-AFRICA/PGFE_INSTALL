<html>
<head>
    <title>Sous-comptes</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Sous-comptes</h2>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Code</th>
                <th>Plan Comptable</th>
            </tr>
        </thead>
        <tbody>
        @foreach($subAccountPlans as $plan)
            <tr>
                <td>{{ $plan->name }}</td>
                <td>{{ $plan->code }}</td>
                <td>{{ $plan->accountPlan->name ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
