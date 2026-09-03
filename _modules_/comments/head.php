<?php

  // CSS files

     HTML::css(SCRIPT_DIR_MODULE.'/style.css?ver=1.0.0');

  // Examples
  // HTML::css('https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/1.9.6/tailwind.min.css');
  // HTML::css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');


  // Javascript files

  // Examples
  // HTML::js(SCRIPT_DIR_LIB.'/moment/moment-with-locales.min.js');  

  // If the defer attribute is set, it specifies that the script is downloaded in parallel 
  // to parsing the page, and executed after the page has finished parsing

     HTML::js(SCRIPT_DIR_MODULE.'/script.js?ver=1.0.0','defer');  


