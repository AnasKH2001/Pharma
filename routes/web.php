<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-email', function () {
    try {
        Mail::raw('This is a test email from Laravel to Mailpit!', function ($message) {
            $message->to('test@example.com')
                    ->subject('Mailpit Test Email');
        });
        
        return 'Email sent successfully! Check http://localhost:8025';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});