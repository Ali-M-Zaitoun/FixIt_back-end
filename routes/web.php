<?php

use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Messaging;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/whoami', function () {
    return [
        'port' => $_SERVER['SERVER_PORT'],
        'pid'  => getmypid(),
        'time' => now()->toDateTimeString(),
    ];
});
