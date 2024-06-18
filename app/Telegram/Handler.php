<?php

namespace App\Telegram;

use App\Http\Controllers\PaymentController;
use App\Models\Chat;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;

class Handler extends WebhookHandler {
    public function start() {
        if (!empty($this->message)) {
            $this->chat->deleteMessage($this->message->id())->send();
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
        } else {
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
        $chatModel = Chat::where('chat_id', $this->chat->chat_id)->first();

        $rate = $this->data->get('rate') ?? $chatModel->rate;
        $chatModel->rate = $rate;
        $chatModel->save();

        $this->chat->message(__('type'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(trans_choice('typeButtons', 0))
                            ->webApp('https://sevenme.es/public/webApp?rate=' . $rate . '&chat_id=' . $this->chat->chat_id)
                    ])
                    ->row([
                        Button::make(trans_choice('typeButtons', 1))
                            ->action('payUSDT')
                    ])
                    ->row([
                        Button::make(__('backButton'))
                            ->action('selectRate')
                    ])
            )
            ->send();
    }

    public function payUSDT() {
        $chatModel = Chat::where('chat_id', $this->chat->chat_id)->first();
        $payment = new PaymentController();
        $paymentUSDT = $payment->payUSDT($this->chat->chat_id, $chatModel->rate);

        $this->chat->message(__('buyUSDT20'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(trans_choice('buyUSDT20Button', 0))
                            ->webApp($paymentUSDT)
                    ])
                    ->row([
                        Button::make(trans_choice('buyUSDT20Button', 1))
                            ->action('checkUSDT')
                    ])
                    ->row([
                        Button::make(__('backButton'))
                            ->action('selectType')
                    ])
            )
            ->send();
    }

    public function checkUSDT() {
        $this->chat->message(__('hashTransaction'))->send();
    }

    protected function handleChatMessage(Stringable $text): void {
        $chatModel = Chat::where('chat_id', $this->chat->chat_id)->first();
        $payment = new PaymentController();
        $result = $payment->checkHashTransaction($text);

        if (
            isset($result['trc20TransferInfo'][0]['to_address'], $result['contractRet']) &&
            $result['trc20TransferInfo'][0]['to_address'] == env('USDT_TRC20_WALLET') &&
            $result['contractRet'] == 'SUCCESS'
        ) {
            $this->rules($this->chat->chat_id);
        } else if (
            isset($result['trc20TransferInfo'][0]['to_address']) &&
            $result['trc20TransferInfo'][0]['to_address'] != env('USDT_TRC20_WALLET')
        ) {
            $this->chat->message(trans_choice('unconfirmed', 0))->send();
        } else {
            $this->chat->message(trans_choice('unconfirmed', 1))->send();
        }
    }

    public function rules($chatId) {
        TelegraphChat::find($chatId)->message(__('rules'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(__('rulesButton'))
                            ->action('accessMessage')
                    ])
            )
            ->send();
    }

    public function accessMessage() {
        $chatModel = Chat::where('chat_id', $this->chat->chat_id)->first();

        $this->chat->message(__('access'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(__('accessButton'))
                            ->url($chatModel->invitation_url)
                    ])
            )
            ->send();

        if ($chatModel->contract_id) {
            $this->menu();
        }
    }

    public function menu() {
        sleep(2);
        $this->chat->message(__('menu'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(__('unsubscribeButton'))
                            ->action('unsubscribe')
                    ])
                    ->row([
                        Button::make(__('returnToSubscriptionButton'))
                            ->action('selectRate')
                    ])
                    ->row([
                        Button::make(trans_choice('greetingButtons', 2))
                            ->url('https://t.me/Victorez'),
                    ])
            )
            ->send();
    }

    public function unsubscribe() {
        $this->chat->message(__('confirm'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(trans_choice('confirmButton', 0))
                            ->action('unsubscribeNotification')
                    ])
                    ->row([
                        Button::make(trans_choice('confirmButton', 1))
                            ->action('menu')
                    ])
            )
            ->send();
    }

    public function unsubscribeNotification() {
        $payment = new PaymentController();
        $payment->unsubscribe($this->chat->chat_id);
        $this->chat->message(__('unsubscribeNotification'))->send();
        $this->menu();
    }

    public function aboutMe() {
        $this->chat->message(__('aboutMe'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(trans_choice('greetingButtons', 0))
                            ->action('selectRate'),
                    ])
                    ->row([
                        Button::make(__('backButton'))
                            ->action('start')
                    ])
            )
            ->send();
    }

    public function notification($chatId) {
        $chat = TelegraphChat::find($chatId);
        $chat->message('Скоро закончится подписка')->send();
    }
}
