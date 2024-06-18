// get elements
const button = document.querySelector('.button');
const loader = document.querySelector('.loader');


let isLoading = false;

// event listener
button.addEventListener('click', () => {
    console.log('clicked')

    if (!isLoading) {
        startLoadingAnimation();
    } else if (isLoading) {
        stopLoadingAnimation();
    }
    changeButtonText();
}

);

// start animation
function startLoadingAnimation() {
    isLoading = true;
    loader.style.display = 'flex';
    loader.classList.add('rotate');

}