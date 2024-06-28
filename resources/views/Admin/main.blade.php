<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sevenme</title>

    <link type="text/css" href="{{asset('styles/manager.css')}}" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <h1>Панель управления</h1>
    </header>

    <section class="note">
        <h1>Памятка по оформлению текста</h1>
        <p>1. Для переноса строки используется сочетание обратного слеша и буквы <tt>\n</tt>. Если просто перенести строку в поле ввода, то строка не перенесется!;<br>
            2. Эмодзи вставляется следующим образом: выбираете эмодзи, например, в тг, копируете его и вставляете в поле ввода;<br>
            3. Фигурные скобочки и кавычки с числами не удалять. <b>Редактируем только текст, который требуется изменить!</b><br>
            4. Для обновления текста в базе данных нажимаем на кнопку "Сохранить изменения";<br>
            5. Для того, чтобы увидеть обновленный текст в боте надо прописать команду <tt>/start</tt>, чтобы бот подтянул обновленный текст;<br>
            6. Для выделения слова или предложения заворачивайте их в спец. символы: <br>
            &nbsp&nbsp&nbsp&nbsp * — выделить текст <b>жирным</b>. Пример: выделяем текст *<b>жирным</b>*;<br>
            &nbsp&nbsp&nbsp&nbsp _ — выделить текст <i>курсивом</i>. Пример: выделяем текст _<i>курсивом</i>_;<br>
            &nbsp&nbsp&nbsp&nbsp ` — выделить текст <tt>моноширным</tt>. Пример: выделяем текст `<tt>моноширным</tt>`;<br>
        </p>
    </section>

    <form method="post">
        <table class="table">
            <thead>
                <tr>
                    <th>Наименование в коде</th>
                    <th>Текст</th>
                </tr>
            </thead>

            <tbody>
                @foreach($texts as $i => $text)
                <tr>
                    <td>{{$text->chapter}}</td>
                    <td>
                        @csrf
                        <textarea name="{{$text->chapter}}" class="area">{{$text->text}}</textarea>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button formaction="/public/manager/saveUpdate">Сохранить изменения</button>
    </form>
</body>

<script src="{{asset('scripts/autosize.js')}}"></script>

</html>