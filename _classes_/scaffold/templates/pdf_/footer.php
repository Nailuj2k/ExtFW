<?php

$html_pdf_header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style>
    @import url(\'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap\');
    @font-face {font-family: "Montserrat Thin";      src:local("Montserrat Thin"),      local("Montserrat-Thin"),      url(_fonts_/Montserrat-Thin.ttf)       format("truetype");}
    @font-face {font-family: "Montserrat ExtraLight";src:local("Montserrat ExtraLight"),local("Montserrat-ExtraLight"),url(_fonts_/Montserrat-ExtraLight.ttf) format("truetype");}
    @font-face {font-family: "Montserrat Light";     src:local("Montserrat Light"),     local("Montserrat-Light"),     url(_fonts_/Montserrat-Light.ttf)      format("truetype");}
    @font-face {font-family: "Montserrat Regular";   src:local("Montserrat Regular"),   local("Montserrat-Regular"),   url(_fonts_/Montserrat-Regular.ttf)    format("truetype");}
    @font-face {font-family: "Montserrat Medium";    src:local("Montserrat Medium"),    local("Montserrat-Medium"),    url(_fonts_/Montserrat-Medium.ttf)     format("truetype");}
    @font-face {font-family: "Montserrat SemiBold";  src:local("Montserrat SemiBold"),  local("Montserrat-SemiBold"),  url(_fonts_/Montserrat-SemiBold.ttf)   format("truetype");}
    @font-face {font-family: "Montserrat Bold";      src:local("Montserrat Bold"),      local("Montserrat-Bold"),      url(_fonts_/Montserrat-Bold.ttf)       format("truetype");}
    @font-face {font-family: "Montserrat ExtraBold"; src:local("Montserrat ExtraBold"), local("Montserrat-ExtraBold"), url(_fonts_/Montserrat-ExtraBold.ttf)  format("truetype");}
    @font-face {font-family: "Montserrat Black";     src:local("Montserrat Black"),     local("Montserrat-Black"),     url(_fonts_/Montserrat-Black.ttf)      format("truetype");}
    html {margin:0px;padding:0px;}
    body { background-color:#fffffe;  width:100%; padding:0px;margin:0;font-family: \'Open Sans\', sans-serif;font-size:12px;}
    * {padding: 0;margin:0;font-family: \'Open Sans\', sans-serif;}
    h1,h2,h3,h4{font-family: \'Montserrat Bold\';color:#000000;margin:20px;font-weight:700;}
    div,li,p,span{font-family: \'Montserrat Light\'; color:#000000;margin:20px;}
    #watermark {position: fixed;bottom:0px;left:0px;top:0px;right:0px;z-index:-1000;}
    img{max-width:100%;}
    ul{list-style-type:bullet;margin:5px 0 0 40px;}
    #top{position:relative;height: 140px;width:789px; border:0px solid red;background-color:transparent;padding:0;}
    #top #logo{height: 50px;position:absolute;left:20px;bottom:20px;}
    #top #logo-r{height: 36px;position:absolute;left:585px;bottom:20px;}
    table{border-collapse:collapse;margin:0;}
    table tr th{border-bottom:2px solid black;font-family: \'Montserrat Medium\';font-weight:normal;}
    table tr td{border-bottom:1px solid #444;font-family: \'Montserrat Light\'; }
    table tr th,
    table tr td{margin:0;padding:3px 5px;color:#000000;}
    /*@page { size: a4 landscape; }*/
   .page {page-break-after: always;position:relative; width:100%;}
   .footer { position: fixed; bottom: 5px; left:5px; right:5px;height:26px;}
   .pagenum:before { content: counter(page)  }
</style>
</head>';



ob_start();
include(SCRIPT_DIR_MODULE.'/index.php');
$html_pdf_content = ob_get_clean();

$html_pdf_content = CFG::$vars['templates']['pdf']['header']
                  . $html_pdf_content
                  . CFG::$vars['templates']['pdf']['footer']; 

// $pdf_orientation = $owner->pdf_orientation ? $owner->pdf_orientation : false;

// TEST
// $html_pdf_content=$html_pdf_content.'<div class="page"></div>Pag 2<div class="page"></div>pag 3<div class="page"></div>pag 4';

$html_pdf_page_num = $html_pdf_page_num?$html_pdf_page_num:false;

if ($html_pdf_page_num) 
   $html_pdf_content  = '<body><div class="footer">Página: <span class="pagenum"></span></div>'.$html_pdf_content.'</body>';
else
   $html_pdf_content  = '<body>'.$html_pdf_content.'</body>';

$html_pdf_footer  = '</html>';

$html_pdf_detail = '<p>line</p>';

/********************** PHP7
require_once SCRIPT_DIR_LIB.'/dompdf/lib/html5lib/Parser.php';
require_once SCRIPT_DIR_LIB.'/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once SCRIPT_DIR_LIB.'/dompdf/lib/php-svg-lib/src/autoload.php';
require_once SCRIPT_DIR_LIB.'/dompdf/src/Autoloader.php';
Dompdf\Autoloader::register();
******************/


/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

// HMLT5 Parser
require_once SCRIPT_DIR_LIB . '/dompdf/lib/html5lib/Parser.php';

// Sabberworm
spl_autoload_register(function($class)
{
    if (strpos($class, 'Sabberworm') !== false) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file = realpath(SCRIPT_DIR_LIB . '/dompdf/lib/php-css-parser/lib/' . (empty($file) ? '' : DIRECTORY_SEPARATOR) . $file . '.php');
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// php-font-lib
require_once SCRIPT_DIR_LIB . '/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';

//php-svg-lib
require_once SCRIPT_DIR_LIB . '/dompdf/lib/php-svg-lib/src/autoload.php';


/*
 * New PHP 5.3.0 namespaced autoloader
 */
require_once SCRIPT_DIR_LIB . '/dompdf/src/Autoloader.php';

Dompdf\Autoloader::register();





use Dompdf\Dompdf;
use Dompdf\Options;
$options = new Options();
//$options->set('defaultFont', 'Courier');
$options->set('defaultFont', 'Helvetica');
//$options->setIsHtml5ParserEnabled(true);
//$options->setIsRemoteEnabled(true);
$options->set('isRemoteEnabled', true);
$options->set('debugKeepTemp', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('chroot', '/');
//$options->set('orientation', 'landscape');
//$options->set('paper', 'a3');

$dompdf = new Dompdf($options);
$dompdf->load_html( $html_pdf_header.$html_pdf_content.$html_pdf_footer );
 $dompdf->setPaper('a4', 'landscape');
//$dompdf->set_paper( $pdf_paper_format ? $pdf_paper_format : 'a4' ,
//                    $pdf_orientation  ? $pdf_orientation  : 'portrait' );

// PDFTEST
$f;
$l;
if(headers_sent($f,$l)){
    die("<pre style='border:2px solid #ff6600;background-color:#ffff99;margin:20px;'>Encontrada línea en las cabeceras:<br /><br /><b>Archivo:</b><br />$f<br/><b>Línea:</b><br />$l<br/></pre>");
}
// PDFTEST

$pdf_filename = $_ARGS[3]?$_ARGS[3].'.pdf':time().'.pdf';
$dompdf->render();
$dompdf->stream($pdf_filename); //, array("Attachment" => false));
  
if($pdf_savedir && $pdf_savefilename){
  $output = $dompdf->output();
  file_put_contents($pdf_savedir.$pdf_savefilename, $output);
}

exit(0);



