window.onload = () => {
    Array.from(document.getElementsByClassName('flash')).forEach((item) => {
        console.log('i');
        setTimeout(() => {
            item.remove();
        }, 3000);
    });
};
