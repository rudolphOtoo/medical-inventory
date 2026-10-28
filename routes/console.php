<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('db:backup')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onFailure(function () {
        report('The scheduled database backup failed.');
    });
