@extends('layouts.app')

@section('title', 'Контакты')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/contacts.css') }}">

<div class="contacts-page">
    <div class="container py-5">
        <div class="contacts-card mx-auto">
            <h2 class="text-center mb-5 fw-bold">Свяжитесь с нами</h2>

            <div class="row g-5 align-items-center">
                <!-- Левая часть: контактная информация -->
                <div class="col-md-6">
                    <div class="contact-info">
                        <h4 class="section-title mb-4">Наши контакты</h4>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-geo-alt-fill"></i> <strong>Адрес:</strong> г. Иркутск, ул. Ленина, 5а</li>
                            <li><i class="bi bi-telephone-fill"></i> <strong>Телефон:</strong> 8 (3952) 34-38-95</li>
                            <li><i class="bi bi-envelope-fill"></i> <strong>Email:</strong> info@cinemacity.ru</li>
                            <li><i class="bi bi-clock-fill"></i> <strong>Часы работы:</strong> ежедневно с 10:00 до 23:00</li>
                        </ul>
                    </div>
                </div>

                <!-- Правая часть: соцсети -->
                <div class="col-md-6">
                    <h4 class="section-title mb-4">Мы в социальных сетях</h4>
                    <div class="d-flex gap-4 flex-wrap social-links">
                        <a href="#" class="social-btn" title="Telegram"><i class="bi bi-telegram"></i></a>
                        <a href="#" class="social-btn" title="YouTube"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="social-btn" title="Instagram"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
