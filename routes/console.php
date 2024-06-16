<?php

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('registerCommands', function () {
    $bot = TelegraphBot::find(1);
    $bot->registerCommands(['start' => 'Запустить бота'])->send();
});

Artisan::command('unregisterCommands', function () {
    $bot = TelegraphBot::find(1);
    $bot->unregisterCommands(['start' => 'Command unregistered'])->send();
});
