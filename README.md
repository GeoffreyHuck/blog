    yarn install
    
For dev :

    yarn encore dev --watch

Build for production :

    yarn encore prod

Install asciidoctor :

    sudo apt install asciidoctor

To convert a document to HTML (with <html>, <body>, css and javascript included) :

    asciidoctor -D ../build my-document.adoc

To obtain only the content in HTML :

    asciidoctor -s -D ../build my-document.adoc
