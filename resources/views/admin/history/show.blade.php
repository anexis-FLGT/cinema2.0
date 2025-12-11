@extends('admin.layouts.admin')

@section('title', 'Детали бронирования')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-ticket-perforated me-2"></i>Детали бронирования #{{ $booking->id_booking }}
        </h2>
        <a href="{{ route('admin.history.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Назад к истории
        </a>
    </div>

    <div class="row g-4">
        {{-- Информация о бронировании --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Информация о бронировании</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">ID бронирования:</th>
                            <td>{{ $booking->id_booking }}</td>
                        </tr>
                        <tr>
                            <th>Дата создания:</th>
                            <td>
                                @if($booking->created_ad)
                                    {{ \Carbon\Carbon::parse($booking->created_ad)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}
                                @else
                                    <span class="text-muted">Не указана</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Зал:</th>
                            <td>
                                @if($booking->session && $booking->session->hall)
                                    {{ $booking->session->hall->hall_name }}
                                @else
                                    <span class="text-muted">Зал удалён</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Место:</th>
                            <td>
                                @if($booking->seat)
                                    Ряд {{ $booking->seat->row_number }}, Место {{ $booking->seat->seat_number }}
                                @else
                                    <span class="text-muted">Место удалено</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Информация о пользователе --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Информация о пользователе</h5>
                </div>
                <div class="card-body">
                    @if($booking->user)
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">ФИО:</th>
                                <td>{{ $booking->user->last_name }} {{ $booking->user->first_name }} {{ $booking->user->middle_name ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Телефон:</th>
                                <td>{{ $booking->user->phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Логин:</th>
                                <td>{{ $booking->user->login }}</td>
                            </tr>
                            <tr>
                                <th>Роль:</th>
                                <td>{{ $booking->user->role->role_name ?? '—' }}</td>
                            </tr>
                        </table>
                    @else
                        <p class="text-muted">Пользователь удалён</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Информация о сеансе --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-film me-2"></i>Информация о сеансе</h5>
                </div>
                <div class="card-body">
                    @if($booking->session)
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Фильм:</th>
                                <td>
                                    @if($booking->session->movie)
                                        {{ $booking->session->movie->movie_title }}
                                    @else
                                        <span class="text-muted">Фильм удалён</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Дата и время:</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($booking->session->date_time_session)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Зал:</th>
                                <td>
                                    @if($booking->session->hall)
                                        {{ $booking->session->hall->hall_name }}
                                    @else
                                        <span class="text-muted">Зал удалён</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    @else
                        <p class="text-muted">Сеанс удалён</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Информация о платеже --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Информация о платеже</h5>
                </div>
                <div class="card-body">
                    @if($booking->payment)
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Статус:</th>
                                <td>
                                    @if($booking->payment->payment_status === 'оплачено')
                                        <span class="badge bg-success">Оплачено</span>
                                    @elseif($booking->payment->payment_status === 'ожидание')
                                        <span class="badge bg-warning">Ожидание</span>
                                    @elseif($booking->payment->payment_status === 'отменено')
                                        <span class="badge bg-danger">Отменено</span>
                                    @elseif($booking->payment->payment_status === 'ожидает_подтверждения')
                                        <span class="badge bg-info">Ожидает подтверждения</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $booking->payment->payment_status }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Сумма:</th>
                                <td>
                                    @if($booking->payment->amount)
                                        <strong>{{ number_format($booking->payment->amount, 0, ',', ' ') }} ₽</strong>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>ID платежа:</th>
                                <td>{{ $booking->payment->payment_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Дата создания:</th>
                                <td>
                                    @if($booking->payment->created_at)
                                        {{ \Carbon\Carbon::parse($booking->payment->created_at)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Дата обновления:</th>
                                <td>
                                    @if($booking->payment->updated_at)
                                        {{ \Carbon\Carbon::parse($booking->payment->updated_at)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    @else
                        <p class="text-muted">Платеж не создан</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

