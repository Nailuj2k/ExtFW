<h1><?=MODULE?></h1>
<p>No por mucho tempranar amanece mas madrugo ...</p>





<?php

/**********



https://unix.stackexchange.com/questions/530900/etc-fstab-does-not-mount-automatically-on-debian-10





        // Instalación Tesseract OCR
        // https://notesalexp.org/tesseract-ocr/
            sudo apt-get install apt-transport-https
            /etc/apt/sources.list
            deb https://notesalexp.org/tesseract-ocr-dev/buster/ buster main
            sudo apt-get update -oAcquire::AllowInsecureRepositories=true
            sudo apt-get install notesalexp-keyring -oAcquire::AllowInsecureRepositories=true
            sudo apt-get update
            or
            wget -O - https://notesalexp.org/debian/alexp_key.asc | sudo apt-key add -
            sudo apt-get update

            sudo apt-get install tesseract-ocr

        // Instalación Tesseract OCR for PHP
        // https://github.com/thiagoalessio/tesseract-ocr-for-php
        // https://www.sitepoint.com/ocr-in-php-read-text-from-images-with-tesseract/
            composer require thiagoalessio/tesseract_ocr

        // Alt: ttp://sourceforge.net/projects/phpocr/



       // use thiagoalessio\TesseractOCR\TesseractOCR;
       // $ajax_result['text'] (new TesseractOCR($scanned_image))->lang('spa')->run();

vi /etc/php/8.0/cli/php.ini

509  vi /etc/php/8.0/fpm/php.ini
  510   systemctl reload php8.0-fpm

post_max_size = 20M
upload_max_filesize = 20M
max_file_uploads = 20


****************/