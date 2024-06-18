<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Telegram\Handler;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller {
    public function payUSDT($chatId, $period) {
        $description = 'Оплата доступа в телеграм-канал';
        $clientReferenceId = hash('sha256', 'description' . time());
        if ($period == '1') {
            $cost = 15;
        } else if ($period == '6') {
            $cost = 75;
        } else if ($period == '12') {
            $cost = 150;
        }
        $body = array(
            'amount' => number_format(1, 2, '.', ''), // Сумма инвойса
            'client_reference_id' => $clientReferenceId, // Уникальный идентификатор транзакции
            'widget_description' => $description, // Описание инвойса
            'metadata' => $chatId . '_' . $period,
            'cancel_url' => 'https://t.me/hrolus_bot'
        );
        $headers = array(
            'Content-Type: application/json',
            'public-key: ' . env('CRYPTOSCAN_PUBLIC_KEY'),
            'private-key: ' . env('CRYPTOSCAN_PRIVATE_KEY'),
        );

        return $this->sendRequest('https://cryptoscan.one/api/v1/invoice/widget', json_encode($body), $headers)['data']['widget_url'];
    }

    public function payCard($email, $period, $currencyCode) {
        $offerId = array(
            '1' => 'bf624197-53c0-4bb9-aa30-7d79cbbd072a',
            '6' => '6e9744e2-ce6a-49ee-9ff4-ed9145b2422e',
            '12' => '74eb4a34-a74e-4196-a96e-ad0a3366ae95'
        );

        $headers = array(
            'accept: application/json',
            'X-Api-Key:' . env('LAVA_TOKEN'),
            'Content-Type: application/json'
        );
        $body = array(
            'email' => $email,
            'offerId' => $offerId[$period],
            'currency' => $currencyCode,
            'buyerLanguage' => 'RU'

        );

        return $this->sendRequest('https://gate.lava.top/api/v2/invoice', json_encode($body), $headers)['paymentUrl'];
    }

    public function generateInviteLink($chatId) {
        $telegramToken = env('TELEGRAM_TOKEN');
        $data = http_build_query([
            'chat_id' => '-1002215378896',
            'name' => 'Доступ к закрытому каналу СВОИ',
            'member_limit' => 1
        ]);
        $result = $this->sendRequest("https://api.telegram.org/bot$telegramToken/createChatInviteLink", $data)['result']['invite_link'];

        $chat = Chat::where('chat_id', $chatId)->first();
        $chat->invitation_url = $result;
        $chat->save();

        return $result;
    }

    public function responseCrypto(Request $request) {
        Log::info('CRYPTO_KEY:' . print_r($request->all(), true));
        $webhookData = $request->all();
        $chatId = explode('_', $webhookData['data']['metadata'])[0];
        $period = explode('_', $webhookData['data']['metadata'])[1];

        if ($webhookData['event_type'] == 'paid' || $webhookData['event_type'] == 'paid_manually') {
            $date = new DateTime(date("Y-m-d H:i:s"));
            if ($period == 1) {
                $date->modify('+1 month');
            } else if ($period == 6) {
                $date->modify('+6 month');
            } else if ($period == 12) {
                $date->modify('+12 month');
            }

            $chatModel = Chat::where('chat_id', $chatId)->first();
            $chatModel->is_banned = 0;
            $chatModel->valid_until = $date->format('Y-m-d H:i:s');
            $chatModel->save();

            $this->generateInviteLink($chatModel->chat_id);

            $tgHandler = new Handler();
            $tgHandler->rules($chatModel->chat_id);

            return response('Success', 200)->header('Content-Type', 'text/plain');
        }
    }

    public function responseLava(Request $request) {
        Log::info('LAVA_KEY:' . print_r($request->all(), true));
        $webhookData = $request->all();

        if ($webhookData['status'] == 'completed' || $webhookData['status'] == 'subscription-active') {
            $date = new DateTime(date("Y-m-d H:i:s"));

            if (
                $webhookData['currency'] == 'RUB' && $webhookData['amount'] == 1500.00 ||
                $webhookData['currency'] == 'USD' && $webhookData['amount'] == 15.00 ||
                $webhookData['currency'] == 'EUR' && $webhookData['amount'] == 14.00
            ) {
                $date->modify('+1 month');
            } else if (
                $webhookData['currency'] == 'RUB' && $webhookData['amount'] == 7500.00 ||
                $webhookData['currency'] == 'USD' && $webhookData['amount'] == 75.00 ||
                $webhookData['currency'] == 'EUR' && $webhookData['amount'] == 70.00
            ) {
                $date->modify('+6 month');
            } else if (
                $webhookData['currency'] == 'RUB' && $webhookData['amount'] == 15000.00 ||
                $webhookData['currency'] == 'USD' && $webhookData['amount'] == 150.00 ||
                $webhookData['currency'] == 'EUR' && $webhookData['amount'] == 140.00
            ) {
                $date->modify('+12 month');
            }

            $chatModel = Chat::where('email', $webhookData['buyer']['email'])->first();
            $chatModel->is_banned = 0;
            $chatModel->valid_until = $date->format('Y-m-d H:i:s');
            if ($webhookData['status'] == 'subscription-active') {
                $chatModel->contract_id = $webhookData['contractId'];
            } else {
                $chatModel->contract_id = null;
            }

            $chatModel->save();

            $this->generateInviteLink($chatModel->chat_id);

            $tgHandler = new Handler();
            $tgHandler->rules($chatModel->chat_id);

            return response('Success', 200)->header('Content-Type', 'text/plain');
        }
    }

    public function checkHashTransaction($id) {
        return $this->sendRequest("https://apilist.tronscanapi.com/api/transaction-info?hash=$id", null, null, false);
    }

    public function unsubscribe($chatId) {
        $headers = array(
            'accept: */*',
            'X-Api-Key:' . env('LAVA_TOKEN'),
        );

        $chatModel = Chat::where('chat_id', $chatId)->first();

        $ch = curl_init('https://gate.lava.top/api/v1/subscriptions?contractId=' . $chatModel->contract_id . '&email=' . $chatModel->email);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);

        Log::info('LAVA_DELETE:' . print_r($result, true));

        $chatModel->contract_id = null;
        $chatModel->save();
    }

    private function sendRequest($url, $data = null, $headers = null, $isPost = true) {
        if ($isPost == true) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($headers) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
        } else {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}
