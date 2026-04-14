<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('subscriptions:check-expired')->dailyAt('02:00');
