<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class WebAppController extends Controller {
    public function index(Request $request) {
        $incomeData = $request->all();

        $nameProduct = array(
            '1' => 'Доступ в канал СВОИ ЛЮДИ (1 мес.)',
            '6' => 'Доступ в канал СВОИ ЛЮДИ (6 мес.)',
            '12' => 'Доступ в канал СВОИ ЛЮДИ (12 мес.)'
        );

        return view(
            'webApp',
            [
                'nameProduct' => $nameProduct[$incomeData['rate']] ?? $nameProduct['1'],
                'rate' => $incomeData['rate'],
                'chat_id' => $incomeData['chat_id'],
            ]
        );
    }

    public function pay(Request $request) {
        $request->validate(
            [
                'email' => 'required|email',
            ]
        );
        $inputData = $request->all();
        $chatModel = Chat::where('chat_id', $inputData['chat_id'])->first();
        $chatModel->email = $inputData['email'];
        $chatModel->save();
        $payment = new PaymentController();
        $link = $payment->payCard($inputData['email'], $inputData['rate'], $inputData['currency']);
        return redirect()->to($link)->send();
    }
}
