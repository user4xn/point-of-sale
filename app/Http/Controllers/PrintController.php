<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ReceiptService;

class PrintController extends Controller
{
    public function test() {
        return Inertia::render('Print/Test', [
            'struk' => ReceiptService::testPrint(),
        ]);
    }
}
