<?php

use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Messaging;

Route::get('/', function () {

    $firebase = (new Factory())->createMessaging();
    app(Messaging::class);

    //    dd($firebase);

    return view('welcome');
});
