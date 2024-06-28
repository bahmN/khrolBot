<?php

namespace App\Telegram;

use App\Http\Controllers\PaymentController;
use App\Models\Chat;
use App\Models\TextManager;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class Handler extends WebhookHandler {
    public function start() {
        if (!empty($this->message)) {
            $chat = TelegraphChat::find($this->message->chat()->id());
            $chat->deleteMessage($this->message->id())->send();
            $chat->message($this->getText('greeting')[0])
                ->keyboard(
                    Keyboard::make()
                        ->row([
                            Button::make($this->getText('greetingButtons')[0])
                                ->action('selectRate'),
                        ])
                        ->row([
                            Button::make($this->getText('greetingButtons')[1])
                                ->action('aboutMe'),
                        ])
                        ->row([
                            Button::make($this->getText('greetingButtons')[2])
                                ->url('https://t.me/Victorez'),
                        ])
                )
                ->send();
        } else {
            $this->chat->message($this->getText('greeting')[0])
                ->keyboard(
                    Keyboard::make()
                        ->row([
                            Button::make($this->getText('greetingButtons')[0])
                                ->action('selectRate'),
                        ])
                        ->row([
                            Button::make($this->getText('greetingButtons')[1])
                                ->action('aboutMe'),
                        ])
                        ->row([
                            Button::make($this->getText('greetingButtons')[2])
                                ->url('https://t.me/Victorez'),
                        ])

                )
                ->send();
        }
    }

    public function selectRate() {
        $this->chat->message($this->getText('rate')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('rateButtons')[0])
                            ->action('selectType')
                            ->param('rate', '1'),
                    ])
                    ->row([
                        Button::make($this->getText('rateButtons')[1])
                            ->action('selectType')
                            ->param('rate', '6'),
                    ])
                    ->row([
                        Button::make($this->getText('rateButtons')[2])
                            ->action('selectType')
                            ->param('rate', '12'),
                    ])
                    ->row([
                        Button::make($this->getText('backButton')[0])
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

        $this->chat->message($this->getText('type')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('typeButtons')[0])
                            ->webApp('https://sevenme.es/public/webApp?rate=' . $rate . '&chat_id=' . $this->chat->chat_id)
                    ])
                    ->row([
                        Button::make($this->getText('typeButtons')[1])
                            ->action('payUSDT')
                    ])
                    ->row([
                        Button::make($this->getText('backButton')[0])
                            ->action('selectRate')
                    ])
            )
            ->send();
    }

    public function payUSDT() {
        $chatModel = Chat::where('chat_id', $this->chat->chat_id)->first();
        $payment = new PaymentController();
        $paymentUSDT = $payment->payUSDT($this->chat->chat_id, $chatModel->rate);

        $this->chat->message($this->getText('buyUSDT20')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('buyUSDT20Button')[0])
                            ->webApp($paymentUSDT)
                    ])
                    ->row([
                        Button::make($this->getText('buyUSDT20Button')[1])
                            ->action('checkUSDT')
                    ])
                    ->row([
                        Button::make($this->getText('backButton')[0])
                            ->action('selectType')
                    ])
            )
            ->send();
    }

    public function checkUSDT() {
        $this->chat->message($this->getText('hashTransaction')[0])->send();
    }

    protected function handleChatMessage(Stringable $text): void {
        $payment = new PaymentController();
        $result = $payment->checkHashTransaction($this->chat->chat_id, $text);

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
            $this->chat->message($this->getText('unconfirmed')[0])->send();
        } else {
            $this->chat->message($this->getText('unconfirmed')[1])->send();
        }
    }

    public function rules($chatId) {
        TelegraphChat::find($chatId)->message($this->getText('rules')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('rulesButton')[0])
                            ->action('accessMessage')
                    ])
            )
            ->send();
    }

    public function accessMessage() {
        $chatModel = Chat::where('chat_id', $this->chat->chat_id)->first();

        $this->chat->message($this->getText('access')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('accessButton')[0])
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
        $this->chat->message($this->getText('menu')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('unsubscribeButton')[0])
                            ->action('unsubscribe')
                    ])
                    ->row([
                        Button::make($this->getText('returnToSubscriptionButton')[0])
                            ->action('selectRate')
                    ])
                    ->row([
                        Button::make($this->getText('greetingButtons')[2])
                            ->url('https://t.me/Victorez'),
                    ])
            )
            ->send();
    }

    public function unsubscribe() {
        $this->chat->message($this->getText('confirm')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('confirmButton')[0])
                            ->action('unsubscribeNotification')
                    ])
                    ->row([
                        Button::make($this->getText('confirmButton')[1])
                            ->action('menu')
                    ])
            )
            ->send();
    }

    public function unsubscribeNotification() {
        $payment = new PaymentController();
        $payment->unsubscribe($this->chat->chat_id);
        $this->chat->message($this->getText('unsubscribeNotification')[0])->send();
        $this->menu();
    }

    public function aboutMe() {
        $this->chat->message($this->getText('aboutMe')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('greetingButtons')[0])
                            ->action('selectRate'),
                    ])
                    ->row([
                        Button::make($this->getText('backButton')[0])
                            ->action('start')
                    ])
            )
            ->send();
    }

    public function notificationDaysToEnd($chatId, $daysToEnd) {
        $chat = TelegraphChat::find($chatId);
        $chat->message("⚠️Ваша подписка заканчивается через $daysToEnd дней. Скорее оплатите подписку, чтобы продлить доступ к каналу *СВОИ ЛЮДИ*!")
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('greetingButtons')[0])
                            ->action('selectRate'),
                    ])
            )
            ->send();
    }

    public function accessLimit($chatId) {
        $chat = TelegraphChat::find($chatId);
        $chat->message($this->getText('accessClosed')[0])
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make($this->getText('greetingButtons')[0])
                            ->action('selectRate'),
                    ])
            )
            ->send();
    }

    private function getText($chapter): array {
        $textModel = TextManager::where('chapter', $chapter)->first()->toArray();

        $textModel = json_decode($textModel['text'], JSON_UNESCAPED_UNICODE);

        return $textModel;
    }
}
