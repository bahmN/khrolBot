<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebAppController extends Controller {
    public function index(Request $request) {
        $incomeData = $request->all();

        return view('webApp', ['rate' => $incomeData['rate']]);
    }
}
