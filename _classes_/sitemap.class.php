<?php

    include(SCRIPT_DIR_LIB.'/sitemap-generator/SitemapGenerator.php');




    // Set the output file name.
    define ("OUTPUT_FILE", "sitemap.xml");
    
    // Set the start URL. Example: define ("SITE", "https://www.example.com");
    define ("SITE", $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/');
  /*
    $skip_url = array (
                       SITE . "/control_panel",
                       SITE . "/backup",
                       SITE . "/db",
                       SITE . "/debug",
                       SITE . "/footer",
                       SITE . "/home",
                       SITE . "/install",
                       SITE . "/login",
                       SITE . "/menu",
                       SITE . "/news",
                       SITE . "/newsletter",
                       SITE . "/page",
                       SITE . "/silent",
                       SITE . "/404",
                       SITE . "/theme",
         //            SITE . "/lang",
                       SITE . "/ajax",
                       SITE . "/pdf",
                      );
    */
    // General information for search engines how often they should crawl the page.
    //define ("FREQUENCY", "weekly");

    // General information for search engines. You have to modify the code to set
    // various priority values for different pages. Currently, the default behavior
    // is that all pages have the same priority.
    //define ("PRIORITY", "0.5");


class Sitemap{
        
        // create object
        $sitemap = new SitemapGenerator(SITE);

        // add urls
        $sitemap->addUrl(SITE                  );
        $sitemap->addUrl(SITE."noticias"      );
        $sitemap->addUrl(SITE."cortometraje"  );
        $sitemap->addUrl(SITE."inscripcion"   );
        $sitemap->addUrl(SITE."bases_2020"    );

        // create sitemap
        $sitemap->createSitemap();

        // write sitemap as file
        $sitemap->writeSitemap();

        // update robots.txt file
        $sitemap->updateRobots();

        // submit sitemaps to search engines
        $result = $sitemap->submitSitemap();
 
        echo "<pre>";
        print_r($result);
        echo "</pre>";

        echo "Memory peak usage: ".number_format(memory_get_peak_usage()/(1024*1024),2)."MB";
        $time2 = explode(" ",microtime());
        $time2 = $time2[1];
        echo "<br>Execution time: ".number_format($time2-$time)."s";
}