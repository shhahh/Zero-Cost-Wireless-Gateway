<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\PhoneController;
use App\Models\SmsQueue; // Ye line sabse upar honi chahiye

// ... Baki routes ...

Route::get('/add-test-sms', function () {
    // 1. Database mein message daalo (Pending status ke saath)
    SmsQueue::create([
        'mobile' => '+919634783662', // <--- YAHAN APNA NUMBER DAALNA
        'message' => 'Hello Sir! Your Order #123 is confirmed. Thanks for shopping.',
        'status' => 'pending' // Abhi line mein hai
    ]);

    return "✅ Message is in the Queue ! Now open the terminal and check.";
});

Route::get('/auto-detect', [PhoneController::class, 'autoDetect'])->name('phone.detect');

Route::get('/sms', [SmsController::class, 'index']);
Route::post('/send-sms', [SmsController::class, 'send'])->name('sms.send');
Route::post('/connect-wireless', [SmsController::class, 'connectWireless'])->name('sms.connect'); 