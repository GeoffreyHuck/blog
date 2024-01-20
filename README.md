# Blog engine for scientific content

This is the code of my personal blog. The live version is accessible here : [geoffreyhuck.com](https://geoffreyhuck.com)

It is based upon the [Symfony Framework](https://github.com/symfony/symfony) and allows writing articles in the [AsciiDoc](https://asciidoc.org) format, which is very convenient to add mathematical formulas, graphic plots, code, and other schemas such as neural network representations.

## Installation

### Install dependencies :

    composer install

### Create the database :

    bin/console doctrine:schema:update --force

### Create an admin user :

    bin/console app:create:admin

### Install the javascript dependencies :

    yarn install

### Install asciidoctor, asciidoctor-diagram and asciidoctor-mathematical :


    sudo apt install -y asciidoctor
    sudo gem install asciidoctor-diagram
    sudo apt install -y gnuplot
    sudo apt install -y graphviz

    sudo apt-get -qq -y install bison flex libffi-dev libxml2-dev libgdk-pixbuf2.0-dev libcairo2-dev libpango1.0-dev fonts-lyx cmake fonts-lyx
    sudo gem install asciidoctor-mathematical

### Install image optimization tools

    sudo apt install php-imagick imagick
    sudo apt-get install -y jpegoptim optipng pngquant gifsicle webp
    sudo npm install -g svgo

### Setup crontab

Add the following into crontab to enable the spam verification.

    * * * * * /path/to/app console app:verify:comments

### Configuration

Copy the *.env* file into *.env.local* and fill up the configuration. For production, don't forget to put :

    APP_ENV=prod
    APP_SECRET=you_have_to_modify_this

You can get a free [Akismet key](https://akismet.com/signup/) to fight against spam.

## Writing

To write an article, create a new directory inside the articles/ directory.

- `index.adoc` is the content is ASCII-Doc format.
- `cover.JPG` is the cover image.
- `otherimage.JPG` any other image you use in your article. The extension must be in uppercase.

To build a document, which means creating the html and preparing the files, type the following command :

    symfony console app:build-article

Then synchronize the articles by login in `/login` and clicking on the `Synchronize articles` button.

## Tests

To run the tests, type the following command :

    vendor/bin/phpunit

## Updating

In development environnement :
    
    yarn encore dev --watch
    
Build for production :

    yarn encore prod

## Licence

Apache License 2.0
