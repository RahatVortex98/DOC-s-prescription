<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Contracts\View\View;

class ChamberController extends Controller
{
    public function qrStandee(Chamber $chamber): View
    {
        $bookingUrl = route('appointment.public_book', $chamber->id);

        return view('chambers.qr-standee', [
            'chamber' => $chamber,
            'bookingUrl' => $bookingUrl,
        ]);
    }
}
