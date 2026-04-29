<?php

use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Scramble::registerUiRoute('api/docs');
Scramble::registerJsonSpecificationRoute('api/docs.json');
