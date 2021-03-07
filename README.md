# Blog engine for scientific content

This is the code of my personal blog. The live version is accessible here : [geoffreyhuck.com](https://geoffreyhuck.com)

It is based upon the [Symfony Framework](https://github.com/symfony/symfony) and allows writing articles in the [AsciiDoc](https://asciidoc.org) format, which is very convenient to add mathematical formulas, graphic plots and other schemas such as neural network representations.

## Installation

1. Create the database :
    

    bin/console doctrine:schema:update --force

2. Create an admin user :


    bin/console app:create:admin

3. Install the javascript dependencies :


    yarn install
    

4. Install asciidoctor, asciidoctor-diagram and asciidoctor-mathematical :


    sudo apt install -y asciidoctor
    sudo gem install asciidoctor-diagram
    sudo apt install -y gnuplot

    sudo apt-get -qq -y install bison flex libffi-dev libxml2-dev libgdk-pixbuf2.0-dev libcairo2-dev libpango1.0-dev fonts-lyx cmake fonts-lyx
    sudo gem install asciidoctor-mathematical

5. Install image optimization tools


    sudo apt-get install -y jpegoptim optipng pngquant gifsicle webp
    sudo npm install -g svgo

6. Setup crontab

Add the following into crontab to enable the spam verification.

    * * * * * /path/to/app console app:verify:comments

7. Configuration

Copy the *.env* file into *.env.local* and fill up the configuration. For production, don't forget to put :

    APP_ENV=prod
    APP_SECRET=you_have_to_modify_this

You can get a free [Akismet key](https://akismet.com/signup/) to fight against spam.

## Writing

To write an article, create a new directory inside the articles/ directory.

To build a document, which means creating the html and preparing the files, type the following command :

    symfony console app:build-article


## Updating

In development environnement :
    
    yarn encore dev --watch
    
Build for production :

    yarn encore prod

## Licence

Apache License 2.0
