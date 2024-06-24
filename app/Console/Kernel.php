<?php

namespace App\Console;

use App\Http\Controllers\PaymentController;
use App\Models\Chat;
use App\Telegram\Handler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel {
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->call(function () {
            $tgHandler = new Handler();
            /*
                7 дней до конца подписки
            */
            $daysToEnd7 = date('Y-m-d', strtotime('+7 days', time()));
            $chats7 =  DB::table('telegraph_chats')
                ->where('valid_until', '=', $daysToEnd7)
                ->get();

            // рассылаем уведомления 
            foreach ($chats7 as $chat) {
                if (!$chat->contract_id) {
                    $tgHandler->notificationDaysToEnd($chat->chat_id, 7);
                }
            }

            /*
                1 день до конца подписки
            */
            $daysToEnd1 = date('Y-m-d', strtotime('+1 days', time()));
            $chats1 =  DB::table('telegraph_chats')
                ->where('valid_until', '=', $daysToEnd1)
                ->get();

            // рассылаем уведомления 
            foreach ($chats1 as $chat) {
                if (!$chat->contract_id) {
                    $tgHandler->notificationDaysToEnd($chat->chat_id, 1);
                }
            }

            /*
                Баним, если подписка просрочена
            */
            $today = date('Y-m-d');
            $chats = DB::table('telegraph_chats')
                ->where('valid_until', '<=', $today)
                ->get();
            $paymentController = new PaymentController();

            foreach ($chats as $chat) {
                if (!$chat->contract_id) {
                    $telegramToken = env('TELEGRAM_TOKEN');
                    $data = http_build_query([
                        'user_id' => $chat->chat_id,
                        'chat_id' => '-1002215378896',
                    ]);

                    $paymentController->sendRequest("https://api.telegram.org/bot$telegramToken/banChatMember", $data);

                    $chatModel = Chat::where('chat_id', $chat->chat_id)->first();
                    $chatModel->invitation_url = null;
                    $chatModel->is_banned = 1;
                    $chatModel->valid_until = null;
                    $chatModel->email = null;
                    $chatModel->rate = null;
                    $chatModel->save();

                    $tgHandler->accessLimit($chat->chat_id);
                }
            }
        })->daily();
    }

    protected function scheduleTimezone(): DateTimeZone|string|null {
        return 'Europe/Moscow';
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
