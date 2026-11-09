<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Contracts\View\View;

class ChamberController extends Controller
{
    public function qrStandee(Chamber $chamber): View
    {
        $chamber->load('tenant');
        $slug = $chamber->tenant ? $chamber->tenant->slug : 'dr-mahbubur';
        $bookingUrl = route('appointment.public_book', ['tenant' => $slug, 'chamber' => $chamber->id]);

        return view('chambers.qr-standee', [
            'chamber' => $chamber,
            'bookingUrl' => $bookingUrl,
        ]);
    }
}
