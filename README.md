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

Install asciidoctor, asciidoctor-diagram and asciidoctor-mathematical :

    sudo apt install asciidoctor
    sudo gem install asciidoctor-diagram
    sudo apt install gnuplot

    sudo apt-get -qq -y install bison flex libffi-dev libxml2-dev libgdk-pixbuf2.0-dev libcairo2-dev libpango1.0-dev fonts-lyx cmake fonts-lyx
    sudo gem install asciidoctor-mathematical

Install image optimizer tools

    sudo apt-get install -y jpegoptim optipng pngquant gifsicle webp
    sudo npm install -g svgo

To build a document :

    symfony console app:build-article

Crontab :
    
    * * * * * /path/to/symfony console app:verify:comments
