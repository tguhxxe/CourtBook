<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('courtbook:expire')->everyMinute()->withoutOverlapping();
Schedule::command('courtbook:reconcile')->everyFiveMinutes()->withoutOverlapping();
