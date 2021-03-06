Create the database :
    
    bin/console doctrine:schema:update --force
Create an admin :

    bin/console app:create:admin

Install the javascript dependencies :

    yarn install
    
For dev :

    yarn encore dev --watch

Build for production :

    yarn encore prod

Install asciidoctor and asciidoctor-diagram :

    sudo apt install asciidoctor
    sudo gem install asciidoctor-diagram
    sudo apt install gnuplot

Install image optimizer tools

    sudo apt-get install -y jpegoptim optipng pngquant gifsicle webp
    sudo npm install -g svgo

To build a document :

    symfony console app:build-article

Crontab :
    
    * * * * * /path/to/symfony console app:verify:comments
