<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('subscriptions:check-expired')->dailyAt('02:00');
Schedule::command('torneos:baja-por-impago')->dailyAt('03:00');
