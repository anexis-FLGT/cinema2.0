<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет по посещаемости</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 14px;
            color: #666;
        }
        .summary {
            background-color: #f0f0f0;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .summary .amount {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Отчет по посещаемости</h1>
        <p>Период: {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}</p>
        <p>Дата формирования: {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <div class="summary">
        <h2>Общее количество проданных билетов</h2>
        <div class="amount">{{ $totalTickets }}</div>
    </div>

    @if($hallOccupancy->count() > 0)
    <div class="section-title">Заполняемость залов</div>
    <table>
        <thead>
            <tr>
                <th>Фильм</th>
                <th>Зал</th>
                <th class="text-right">Заполняемость</th>
                <th class="text-right">Мест занято</th>
                <th class="text-right">Всего мест</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hallOccupancy as $session)
                <tr>
                    <td>{{ $session['movie_title'] }}</td>
                    <td>{{ $session['hall_name'] }}</td>
                    <td class="text-right">{{ $session['occupancy'] }}%</td>
                    <td class="text-right">{{ $session['booked_seats'] }}</td>
                    <td class="text-right">{{ $session['total_seats'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($popularSessions->count() > 0)
    <div class="section-title">Популярные сеансы</div>
    <table>
        <thead>
            <tr>
                <th>Фильм</th>
                <th>Дата и время</th>
                <th class="text-right">Количество билетов</th>
            </tr>
        </thead>
        <tbody>
            @foreach($popularSessions as $session)
                <tr>
                    <td>{{ $session->movie_title }}</td>
                    <td>{{ \Carbon\Carbon::parse($session->date_time_session)->format('d.m.Y H:i') }}</td>
                    <td class="text-right">{{ $session->tickets_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif


</body>
</html>

