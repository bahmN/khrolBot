<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Play:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('styles/styles.css')}}">

    <title>sevenme</title>
</head>


<body>
    <div class="title">
        <h4>Оплата через</h4>
        <img src="{{asset('img/lava_logo.svg')}}" alt="lava">
    </div>
    <div class="productname">
        <h3>Наименование товара:</h3>
        <h2>Доступ в канал СВОИ ЛЮДИ (1 мес.):</h2>
    </div>
    <form method="get">
        <div class="email">
            <label for="email">Введите вашу почту:</label>
            <input type=" text" id="email" name="email">
        </div>

        <div class="currency">
            <h1>Выберите валюту:</h1>
            <div class="radios">
                <label for="rub">
                    <input type="radio" name="mode" id="rub" value="RUB" checked />
                    <span>RUB</span>
                </label>
                <label for="usd">
                    <input type="radio" name="mode" id="usd" value="USD" />
                    <span>USD</span>
                </label>
                <label for="eur">
                    <input type="radio" name="mode" id="eur" value="EUR" />
                    <span>EUR</span>
                </label>
                <input type="hidden" name="rate" value="{{$rate}}">
            </div>
        </div>

        <button formaction="/webApp/pay">Перейти к оплате</button>
    </form>
</body>

</html>