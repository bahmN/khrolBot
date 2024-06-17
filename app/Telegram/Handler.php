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
        $chatModel = Chat::where('chat_id', $this->chat->chat_id)->first();

        $rate = $this->data->get('rate') ?? $chatModel->rate;

        if ($rate == '1') {
            $chatModel->rate = 1;
            $chatModel->save();
        } else if ($rate == '6') {
            $chatModel->rate = 6;
            $chatModel->save();
        } else if ($rate == '12') {
            $chatModel->rate = 12;
            $chatModel->save();
        }
        $this->chat->message(__('type'))
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make(trans_choice('typeButtons', 0))
                            ->webApp('https://sevenme.es/public/webApp?rate=' . $rate)
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

        $this->chat->message('Оплата в USDT TRC-20. Если вы осуществили перевод и доступ не был предоставлен, то нажмите на "Проверить транзакцию".')
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make('Открыть форму оплаты')
                            ->webApp($paymentUSDT)
                    ])
                    ->row([
                        Button::make('Проверить транзакцию')
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
        $this->chat->message('Отправьте, пожалуйста, Transaction ID (хэш)')->send();
    }

    protected function handleChatMessage(Stringable $text): void {
        $chatModel = Chat::where('chat_id', $this->chat->chat_id)->first();
        $payment = new PaymentController();
        $result = $payment->checkHashTransaction($text);
        Log::info(env('USDT_TRC20_WALLET'));
        if (
            isset($result['trc20TransferInfo'][0]['to_address'], $result['contractRet']) &&
            $result['trc20TransferInfo'][0]['to_address'] == env('USDT_TRC20_WALLET') &&
            $result['contractRet'] == 'SUCCESS'
        ) {
            $this->accessMessage($this->chat->chat_id, $chatModel->invitation_url);
        } else if (
            isset($result['trc20TransferInfo'][0]['to_address']) &&
            $result['trc20TransferInfo'][0]['to_address'] != env('USDT_TRC20_WALLET')
        ) {
            $this->chat->message('Транзакция не подтверждена. Неверный получатель платежа.')->send();
        } else {
            $this->chat->message('Транзакция не подтверждена. Попробуйте еще раз.')->send();
        }
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
                    ->row([
                        Button::make(__('rulesButton'))
                            ->action('rules')
                    ])
            )->send();
    }

    public function rules() {
        $this->chat->message(__('rules'))->send();
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

    // public function notification($chatId) {
    //     $chat = TelegraphChat::find($chatId);
    //     $chat->message('Скоро закончится')->send();
    // }
}
