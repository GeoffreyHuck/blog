window.addEventListener('load', () => {
    Array.from(document.getElementsByClassName('flash')).forEach((item) => {
        setTimeout(() => {
            item.remove();
        }, 3000);
    });
});
