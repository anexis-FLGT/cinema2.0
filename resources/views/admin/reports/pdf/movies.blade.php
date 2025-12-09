<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет по фильмам</title>
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
        <h1>Отчет по фильмам</h1>
        <p>Период: {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}</p>
        <p>Дата формирования: {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    @if($popularMovies->count() > 0)
    <div class="section-title">Самые популярные фильмы</div>
    <table>
        <thead>
            <tr>
                <th>№</th>
                <th>Фильм</th>
                <th class="text-right">Количество билетов</th>
            </tr>
        </thead>
        <tbody>
            @foreach($popularMovies as $index => $movie)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $movie->movie_title }}</td>
                    <td class="text-right">{{ $movie->tickets_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($profitableMovies->count() > 0)
    <div class="section-title">Самые прибыльные фильмы</div>
    <table>
        <thead>
            <tr>
                <th>№</th>
                <th>Фильм</th>
                <th class="text-right">Выручка</th>
                <th class="text-right">Количество билетов</th>
            </tr>
        </thead>
        <tbody>
            @foreach($profitableMovies as $index => $movie)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $movie->movie_title }}</td>
                    <td class="text-right">{{ number_format($movie->revenue, 2, ',', ' ') }} ₽</td>
                    <td class="text-right">{{ $movie->tickets_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($movieOccupancy->count() > 0)
    <div class="section-title">Средняя заполняемость по фильмам</div>
    <table>
        <thead>
            <tr>
                <th>Фильм</th>
                <th class="text-right">Количество сеансов</th>
                <th class="text-right">Средняя заполняемость</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movieOccupancy as $movie)
                <tr>
                    <td>{{ $movie['movie_title'] }}</td>
                    <td class="text-right">{{ $movie['sessions_count'] }}</td>
                    <td class="text-right">{{ $movie['avg_occupancy'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Сгенерировано системой MaxTicket</p>
    </div>
</body>
</html>

