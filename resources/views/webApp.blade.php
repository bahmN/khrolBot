<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link type="text/css" href="{{asset('styles/styles.css')}}?<?php echo time(); ?>" rel="stylesheet" />
    <title>sevenme</title>
</head>


<body>
    <div class="loader">
        <div class="loader__content"></div>
        <h3>Ожидайте, сейчас вас переадресует на страницу оплаты...</h3>
    </div>
    <div class="title">
        <h4>Оплата через</h4>
        <img src="{{asset('img/lava_logo.svg')}}" alt="lava">
    </div>
    <div class="productname">
        <h3>Наименование товара:</h3>
        <h2>{{$nameProduct}}</h2>
    </div>
    <form method="get">
        @if ($errors->has('email'))
        <div class="email">
            <label for="email">Введите вашу почту:</label>
            <input type="text" id="email" name="email" placeholder="Данное поле обязательное для заполнения">
        </div>
        @else
        <div class="email">
            <label for="email">Введите вашу почту:</label>
            <input type=" text" id="email" name="email">
        </div>
        @endif
        <div class="currency">
            <h1>Выберите валюту:</h1>
            <div class="radios">
                <label for="rub">
                    <input type="radio" name="currency" id="rub" value="RUB" checked />
                    <span>RUB</span>
                </label>
                <label for="usd">
                    <input type="radio" name="currency" id="usd" value="USD" />
                    <span>USD</span>
                </label>
                <label for="eur">
                    <input type="radio" name="currency" id="eur" value="EUR" />
                    <span>EUR</span>
                </label>
                <input type="hidden" name="rate" value="{{$rate}}">
                <input type="hidden" name="chat_id" value="{{$chat_id}}">
            </div>
        </div>

        @if($rate == 1)
        <p>*Будет оформлено ежемесячное списание. Подписку можно будет отменить.</p>
        @endif
        <button formaction="/public/wepApp/pay" class="button">Перейти к оплате</button>
    </form>
    <script src="{{asset('scripts/loader.js')}}"></script>
</body>

</html>