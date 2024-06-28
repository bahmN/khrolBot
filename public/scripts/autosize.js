textarea = document.getElementById('area21');

Array.from(document.getElementsByClassName('area'))
    .forEach((element) => {
        element.style.height = (element.scrollHeight + 3) + 'px';
    }
    );

