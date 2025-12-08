<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Билет №{{ $bookingId }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
        }
        
        .ticket {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 3px solid #000;
            overflow: hidden;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #dc3545;
        }
        
        .header p {
            font-size: 12px;
            color: #666;
        }
        
        .ticket-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        
        .ticket-row {
            display: table-row;
        }
        
        .ticket-label {
            display: table-cell;
            width: 40%;
            padding: 8px 5px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .ticket-value {
            display: table-cell;
            padding: 8px 5px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        
        .movie-title {
            font-size: 20px;
            font-weight: bold;
            color: #dc3545;
            margin: 15px 0;
            text-align: center;
            padding: 12px 10px;
            background: #f8f9fa;
            border: 2px solid #000;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        
        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px 10px;
            border: 2px dashed #000;
            overflow: hidden;
        }
        
        .qr-code {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            margin: 10px 0;
            padding: 8px;
            background: #f8f9fa;
            border: 1px solid #000;
            display: inline-block;
            word-wrap: break-word;
            word-break: break-all;
            max-width: 100%;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .footer p {
            margin: 3px 0;
            word-wrap: break-word;
        }
        
        .important {
            background: #fff3cd;
            padding: 12px 10px;
            border: 2px solid #ffc107;
            margin: 15px 0;
            font-weight: bold;
            font-size: 11px;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            letter-spacing: 2px;
            text-align: center;
            margin: 12px 0;
            padding: 8px 5px;
            background: #f8f9fa;
            border: 1px solid #000;
            word-break: break-all;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="ticket">
        {{-- Заголовок --}}
        <div class="header">
            <h1>MaxTicket</h1>
            <p>Кинотеатр нового поколения</p>
        </div>

        {{-- Название фильма --}}
        <div class="movie-title">
            {{ $movie->movie_title ?? 'Фильм не найден' }}
        </div>

        {{-- Информация о билете --}}
        <div class="ticket-info">
            <div class="ticket-row">
                <div class="ticket-label">Номер билета:</div>
                <div class="ticket-value"><strong>{{ $bookingId }}</strong></div>
            </div>
            
            <div class="ticket-row">
                <div class="ticket-label">Дата и время сеанса:</div>
                <div class="ticket-value">
                    <strong>{{ \Carbon\Carbon::parse($session->date_time_session)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}</strong>
                </div>
            </div>
            
            <div class="ticket-row">
                <div class="ticket-label">Зал:</div>
                <div class="ticket-value"><strong>{{ $hall->hall_name ?? 'Не указан' }}</strong></div>
            </div>
            
            <div class="ticket-row">
                <div class="ticket-label">Место:</div>
                <div class="ticket-value">
                    <strong>Ряд {{ $seat->row_number ?? '?' }}, Место {{ $seat->seat_number ?? '?' }}</strong>
                </div>
            </div>
            
            <div class="ticket-row">
                <div class="ticket-label">Стоимость:</div>
                <div class="ticket-value">
                    <strong>{{ number_format($payment->amount ?? 0, 0, ',', ' ') }} ₽</strong>
                </div>
            </div>
            
            <div class="ticket-row">
                <div class="ticket-label">ФИО зрителя:</div>
                <div class="ticket-value">
                    <strong>{{ $user->last_name }} {{ $user->first_name }} {{ $user->middle_name ?? '' }}</strong>
                </div>
            </div>
            
            @if($movie->age_limit)
            <div class="ticket-row">
                <div class="ticket-label">Возрастное ограничение:</div>
                <div class="ticket-value"><strong>{{ $movie->age_limit }}</strong></div>
            </div>
            @endif
            
            <div class="ticket-row">
                <div class="ticket-label">Дата бронирования:</div>
                <div class="ticket-value">
                    {{ \Carbon\Carbon::parse($booking->created_ad)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}
                </div>
            </div>
        </div>

        {{-- Штрих-код --}}
        <div class="barcode">
            {{ str_pad($booking->id_booking, 12, '0', STR_PAD_LEFT) }}
        </div>

        {{-- QR код (текстовый) --}}
        <div class="qr-section">
            <div style="font-weight: bold; margin-bottom: 10px;">Код для входа:</div>
            <div class="qr-code">
                TKT-{{ $bookingId }}-{{ strtoupper(substr(md5($booking->id_booking . $booking->user_id), 0, 8)) }}
            </div>
        </div>

        {{-- Важная информация --}}
        <div class="important">
            ⚠️ ВАЖНО: Приходите за 15 минут до начала сеанса. 
            При входе в зал предъявите этот билет или его электронную версию на экране устройства.
        </div>

        {{-- Подвал --}}
        <div class="footer">
            <p>Билет действителен только на указанный сеанс</p>
            <p>При утере билета обратитесь в кассу кинотеатра</p>
        </div>
    </div>
</body>
</html>

