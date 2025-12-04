@extends('layouts.app')

@section('title', 'Подтверждение оплаты')

@section('content')
<div class="container my-5" style="color: var(--text-primary);">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                <div class="card-body text-center" style="color: var(--text-primary);">
                    <h3 class="mb-4">Подготовка к оплате...</h3>
                    <div class="spinner-border text-primary mb-4" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p>Перенаправление на страницу оплаты...</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Автоматическая отправка формы --}}
<form id="payment-form" method="POST" action="{{ route('payment.create') }}" style="display: none;">
    @csrf
    <input type="hidden" name="session_id" value="{{ $session_id }}">
    @foreach($seat_ids as $seatId)
        <input type="hidden" name="seat_ids[]" value="{{ $seatId }}">
    @endforeach
</form>

<script>
    // Автоматически отправляем форму при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('payment-form').submit();
    });
</script>
@endsection




