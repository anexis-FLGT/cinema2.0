<?php

return [
    /*
    |--------------------------------------------------------------------------
    | YooKassa Configuration
    |--------------------------------------------------------------------------
    |
    | Конфигурация для интеграции с платежной системой ЮKassa
    | Для тестового режима используйте тестовые данные из личного кабинета
    |
    */

    'shop_id' => env('YOOKASSA_SHOP_ID', '1191002'),
    'secret_key' => env('YOOKASSA_SECRET_KEY', 'test_2pKbZMvrqpT6EL76sJaqNnqHkQu1beXQiGbdl9hf1xs'),
    
    // URL для возврата после оплаты
    'return_url' => env('YOOKASSA_RETURN_URL', '/payment/success'),
    
    // Стоимость билета
    'ticket_price' => env('TICKET_PRICE', 500.00),
    
    // Тестовый режим
    'test_mode' => env('YOOKASSA_TEST_MODE', true),
];





