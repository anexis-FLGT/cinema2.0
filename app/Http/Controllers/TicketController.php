<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class TicketController extends Controller
{
    /**
     * Генерация PDF билета
     */
    public function generatePdf($bookingId)
    {
        $user = Auth::user();
        
        // Получаем бронирование с необходимыми связями
        $booking = Booking::with(['session.movie', 'session.hall', 'seat', 'user', 'payment'])
            ->where('id_booking', $bookingId)
            ->where('user_id', $user->id_user)
            ->firstOrFail();

        // Проверяем, что билет оплачен
        if (!$booking->payment || $booking->payment->payment_status !== 'оплачено') {
            return redirect()->route('user.dashboard')
                ->with('error', 'Билет должен быть оплачен для печати.');
        }

        // Данные для билета
        $ticketData = [
            'booking' => $booking,
            'movie' => $booking->session->movie,
            'session' => $booking->session,
            'hall' => $booking->session->hall,
            'seat' => $booking->seat,
            'user' => $booking->user,
            'payment' => $booking->payment,
            'bookingId' => str_pad($booking->id_booking, 8, '0', STR_PAD_LEFT),
        ];

        // Генерируем PDF
        $pdf = PDF::loadView('tickets.pdf', $ticketData);
        
        // Имя файла
        $filename = 'ticket_' . $booking->id_booking . '_' . date('YmdHis') . '.pdf';
        
        // Возвращаем PDF для скачивания
        return $pdf->download($filename);
    }
}

