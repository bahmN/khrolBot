<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sevenme</title>

    <link type="text/css" href="{{asset('styles/auth.css')}}?<?php echo time(); ?>" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
</head>

<body>
    <form method="get">
        <h1>Авторизация</h1>
        <div class="inputs">
            <div>
                <h2>Логин</h2>
                <input type="text" id="name" name="name">
            </div>
            <div>
                <h2>Пароль</h2>
                <input type="password" id="password" name="password">
            </div>

            @if($errors->all())
            неверные логин или пароль
            @endif
        </div>
        <button formaction="/public/auth/login">Авторизоваться</button>
    </form>
</body>

</html>