<?php

namespace App\Http\Controllers;

use App\Models\TextManager;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class AdminController extends Controller {
    public function index() {
        $textModel = TextManager::all();

        return view('Admin.main', ['texts' => $textModel]);
    }

    public function saveUpdatedText(Request $request) {
        $inputData = $request->all();
        unset($inputData['_token']);

        foreach ($inputData as $chapter => $text) {
            $tManager = TextManager::where('chapter', $chapter)->first();
            $tManager->text = $text;
            $tManager->save();
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
