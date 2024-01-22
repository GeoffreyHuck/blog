window.addEventListener('load', () => {
    const generateButton = document.getElementById('generate_preview');
    if (generateButton) {
        generateButton.addEventListener('click', async () => {
            const response = await fetch( generateButton.getAttribute('data-href') + '?' + new URLSearchParams({
                'rawContent': (document.getElementById('article_rawContent') as HTMLTextAreaElement).value
            }), {
                method: 'GET'
            });
            const html = await response.text();

            // Set the html in the preview iframe.
            const previewIframe = document.getElementById('iframe_preview') as HTMLIFrameElement;
            previewIframe.contentWindow.document.open();
            previewIframe.contentWindow.document.write(html);
            previewIframe.contentWindow.document.close();
        });
    }
});
