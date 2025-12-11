<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет по выручке</title>
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
            color: #28a745;
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
        <h1>Отчет по выручке</h1>
        <p>Период: {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}</p>
        <p>Дата формирования: {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <div class="summary">
        <h2>Общая выручка</h2>
        <div class="amount">{{ number_format($totalRevenue, 2, ',', ' ') }} ₽</div>
    </div>

    @if($revenueByMovie->count() > 0)
    <div class="section-title">Выручка по фильмам</div>
    <table>
        <thead>
            <tr>
                <th>Фильм</th>
                <th class="text-right">Выручка</th>
                <th class="text-right">Количество билетов</th>
            </tr>
        </thead>
        <tbody>
            @foreach($revenueByMovie as $movie)
                <tr>
                    <td>{{ $movie->movie_title }}</td>
                    <td class="text-right">{{ number_format($movie->revenue, 2, ',', ' ') }} ₽</td>
                    <td class="text-right">{{ $movie->bookings_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($revenueByHall->count() > 0)
    <div class="section-title">Выручка по залам</div>
    <table>
        <thead>
            <tr>
                <th>Зал</th>
                <th class="text-right">Выручка</th>
                <th class="text-right">Количество билетов</th>
            </tr>
        </thead>
        <tbody>
            @foreach($revenueByHall as $hall)
                <tr>
                    <td>{{ $hall->hall_name }}</td>
                    <td class="text-right">{{ number_format($hall->revenue, 2, ',', ' ') }} ₽</td>
                    <td class="text-right">{{ $hall->bookings_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($dailyRevenue->count() > 0)
    <div class="section-title">Динамика выручки по дням</div>
    <table>
        <thead>
            <tr>
                <th>Дата</th>
                <th class="text-right">Выручка</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyRevenue as $day)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($day->date)->format('d.m.Y') }}</td>
                    <td class="text-right">{{ number_format($day->revenue, 2, ',', ' ') }} ₽</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif


</body>
</html>

