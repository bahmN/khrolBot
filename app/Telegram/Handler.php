<?php

namespace App\Telegram;

use App\Http\Controllers\PaymentController;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class Handler extends WebhookHandler {
    public function start() {
        if (!empty($this->message)) {
            $this->chat->deleteMessage($this->message->id())->send();
        }

        $this->chat->message(__('greeting'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(trans_choice('greetingButtons', 0))
                            ->action('selectRate'),
                    ])
                    ->row([
                        Button::make(trans_choice('greetingButtons', 1))
                            ->action('aboutMe'),
                    ])
                    ->row([
                        Button::make(trans_choice('greetingButtons', 2))
                            ->url('https://t.me/Victorez'),
                    ])

            )
            ->send();
    }

    public function selectRate() {
        $this->chat->message(__('rate'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(trans_choice('rateButtons', 0))
                            ->action('selectType')
                            ->param('rate', '1'),
                    ])
                    ->row([
                        Button::make(trans_choice('rateButtons', 1))
                            ->action('selectType')
                            ->param('rate', '6'),
                    ])
                    ->row([
                        Button::make(trans_choice('rateButtons', 2))
                            ->action('selectType')
                            ->param('rate', '12'),
                    ])
                    ->row([
                        Button::make(__('backButton'))
                            ->action(('start'))
                    ])
            )
            ->send();
    }

    public function selectType() {
        $rate = $this->data->get('rate');
        if ($rate == '1') {
            $cost = 15;
            $period = 1;
        } else if ($rate == '6') {
            $cost = 75;
            $period = 6;
        } else if ($rate == '12') {
            $cost = 150;
            $period = 12;
        }

        $payment = new PaymentController();
        $paymentUrl = $payment->paymentLink($cost, $this->chat->chat_id);

        $this->chat->message(__('type'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(trans_choice('typeButtons', 0))
                            ->action('buyAccess')
                            ->param('type', 'card')
                    ])
                    ->row([
                        Button::make(trans_choice('typeButtons', 1))
                            ->webApp($paymentUrl)
                    ])
                    ->row([
                        Button::make(__('backButton'))
                            ->action('selectRate')
                    ])
            )
            ->send();
    }

    public function accessMessage($chatId, $invitationUrl) {
        $chat = TelegraphChat::find($chatId);

        $chat->message(__('congratulation'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(__('congratulationButton'))
                            ->url($invitationUrl)
                    ])
            )->send();
    }

    public function aboutMe() {
        $this->chat->message(__('aboutMe'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(__('backButton'))
                            ->action('start')
                    ])
            )
            ->send();
    }
}
