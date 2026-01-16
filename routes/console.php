<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('voucher:update-expired')
    ->hourly();

Schedule::command('user_voucher:update-expired')
    ->hourly()
    ->withoutOverlapping();
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
