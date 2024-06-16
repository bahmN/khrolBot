<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Telegram\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller {
    public function paymentLink($sum, $chatId) {
        $publicKey = env('CRYPTOSCAN_PUBLIC_KEY');
        $privateKey = env('CRYPTOSCAN_PRIVATE_KEY');
        $description = 'Оплата доступа в телеграм-канал';
        $clientReferenceId = hash('sha256', 'description' . time());

        $data = array(
            'amount' => number_format($sum, 2, '.', ''), // Сумма инвойса
            'client_reference_id' => $clientReferenceId, // Уникальный идентификатор транзакции
            'widget_description' => $description, // Описание инвойса
            'metadata' => "$chatId",
            'back_url' =>  $this->generateInviteLink($chatId),
            'cancel_url' => 'https://t.me/hrolus_bot'
        );

        // Преобразование данных в формат JSON
        $jsonData = json_encode($data);

        // Формирование заголовков HTTP запроса
        $headers = array(
            'Content-Type: application/json',
            'public-key: ' . $publicKey,
            'private-key: ' . $privateKey,
        );

        return $this->sendRequest('https://cryptoscan.one/api/v1/invoice/widget', $jsonData, $headers, true)['data']['widget_url'];
    }

    public function generateInviteLink($chatId) {
        $data = http_build_query([
            'chat_id' => '-1002215378896',
            'name' => 'Доступ к закрытому каналу СВОИ',
            'member_limit' => 1
        ]);

        $telegramToken = env('TELEGRAM_TOKEN');
        $result = $this->sendRequest("https://api.telegram.org/bot$telegramToken/createChatInviteLink", $data)['result']['invite_link'];

        $chat = Chat::where('chat_id', $chatId)->first();
        $chat->invitation_url = $result;
        $chat->save();

        return $result;
    }

    public function callback(Request $request) {
        Log::info(json_encode($request->all()));
        // $data = $request->all();

        // $chatId = 255499895;
        // $chat = Chat::where('chat_id', $chatId)->first();

        // $tgHandler = new Handler();
        // $tgHandler->accessMessage($chatId, $chat->invitation_url);

        // return response('', 200)->header('Content-Type', 'text/plain');
    }

    public function callbackLava(Request $request) {
        Log::info('LAVA:' . print_r($request->all(), true));
    }

    private function sendRequest($url, $data, $headers = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}
