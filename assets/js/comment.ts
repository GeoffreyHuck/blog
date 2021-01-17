window.onload = () => {
    const commentReplyTo = document.getElementById('comment_replyTo');
    const replyToContainer = document.getElementById('reply-to-container');

    Array.from(document.getElementsByClassName('reply-link')).forEach((item) => {
        item.addEventListener('click', () => {
            commentReplyTo.setAttribute('value', item.getAttribute('data-replyTo'));

            replyToContainer.style.display = 'block';
        });
    });
};
