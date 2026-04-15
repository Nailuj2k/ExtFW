<?php

require './vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


class PdfGenerator {

    private $dompdf;
    private $headerHeight;
    private $footerHeight;
    private $headerTemplate;
    private $footerTemplate;
    public $pageSize;
    public $orientation;
    public $margins;
    public $watermark_code='';
    public $pdf_filename = false;
    public $pdf_savedir = false;
    public $pdf_savefilename = false;

    public function __construct(array $config = []) {
        // Configuración por defecto
        $this->headerHeight = $config['headerHeight'] ?? 100;
        $this->footerHeight = $config['footerHeight'] ?? 100;
        $this->headerTemplate = $config['headerTemplate'] ?? '';
        $this->footerTemplate = $config['footerTemplate'] ?? '';
        //$this->pageSize = $config['pageSize'] ?? 'A4';
        //$this->orientation = $config['orientation'] ?? 'portrait';
        $this->margins = $config['margins'] ?? [
            'top' => 20,
            'right' => 20,
            'bottom' => 20,
            'left' => 20
        ];

        // Inicializar Dompdf
        /*
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        */

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);
        $options->set('debugKeepTemp', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', '.');

        $this->dompdf = new Dompdf($options);
    }

    public function generatePdf(string $htmlContent): string {

        $this->dompdf->setPaper($this->pageSize, $this->orientation);

        // Preparar el contenido HTML con los estilos necesarios
        $finalHtml = $this->prepareDocument($htmlContent);
        
        // Cargar el HTML en Dompdf
        $this->dompdf->loadHtml($finalHtml);
        
        // Renderizar el PDF
        //$this->dompdf->render();
        
        // Devolver el PDF como string
        //OKIS return $this->dompdf->output();


        $this->dompdf->render();
        $this->dompdf->stream($this->$pdf_filename, array("Attachment" => false));
          
        if($this->pdf_savedir && $this->pdf_savefilename){
          $output = $this->dompdf->output();
          file_put_contents(Str::end_with($this->pdf_savedir,'/').$this->pdf_savefilename, $output);
        } 
    
    }

    public function prepareDocument(string $content): string {
        // Calcular el espacio disponible para el contenido
        //$contentStyle = sprintf( 'margin-top: %spx; margin-bottom: %spx;', $this->headerHeight + $this->margins['top'], $this->footerHeight + $this->margins['bottom'] );
 
        return <<<HTML
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
            <meta charset="UTF-8">
            <style>

                @import url("https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap");
                @font-face {font-family: "Montserrat Thin";      src:local("Montserrat Thin"),      local("Montserrat-Thin"),      url(./_fonts_/Montserrat-Thin.ttf)       format("truetype");}
                @font-face {font-family: "Montserrat ExtraLight";src:local("Montserrat ExtraLight"),local("Montserrat-ExtraLight"),url(./_fonts_/Montserrat-ExtraLight.ttf) format("truetype");}
                @font-face {font-family: "Montserrat Light";     src:local("Montserrat Light"),     local("Montserrat-Light"),     url(./_fonts_/Montserrat-Light.ttf)      format("truetype");}
                @font-face {font-family: "Montserrat Regular";   src:local("Montserrat Regular"),   local("Montserrat-Regular"),   url(./_fonts_/Montserrat-Regular.ttf)    format("truetype");}
                @font-face {font-family: "Montserrat Medium";    src:local("Montserrat Medium"),    local("Montserrat-Medium"),    url(./_fonts_/Montserrat-Medium.ttf)     format("truetype");}
                @font-face {font-family: "Montserrat SemiBold";  src:local("Montserrat SemiBold"),  local("Montserrat-SemiBold"),  url(./_fonts_/Montserrat-SemiBold.ttf)   format("truetype");}
                @font-face {font-family: "Montserrat Bold";      src:local("Montserrat Bold"),      local("Montserrat-Bold"),      url(./_fonts_/Montserrat-Bold.ttf)       format("truetype");}
                @font-face {font-family: "Montserrat ExtraBold"; src:local("Montserrat ExtraBold"), local("Montserrat-ExtraBold"), url(./_fonts_/Montserrat-ExtraBold.ttf)  format("truetype");}
                @font-face {font-family: "Montserrat Black";     src:local("Montserrat Black"),     local("Montserrat-Black"),     url(./_fonts_/Montserrat-Black.ttf)      format("truetype");}
                body {font-family: Arial, sans-serif;/*margin:0;*/}
                *{font-family: "Montserrat Light","Helvetica Neue",Helvetica,Arial,"verdana", "sans-serif";color:#000000;/*line-height:1em;*/font-size:10px;}

                @page {margin-top: {$this->headerHeight}px;margin-bottom: {$this->footerHeight}px;margin-left: {$this->margins['left']}px;margin-right: {$this->margins['right']}px;}
                 #watermark{position: fixed;bottom: -{$this->footerHeight}px;left:-25px;top: -{$this->headerHeight}px;right:-25px;z-index:-1000;background-size:contain;}

                {$this->watermark_code}

                NOhtml {margin:0px;padding:0px;}
                NObody{padding:0;margin:0;}
                NObody>*{padding-left:20px;padding-right:20px;}
                .pdf-header { position: fixed;top: -{$this->headerHeight}px;left: 0;right: 0;height: {$this->headerHeight}px;                }
                .pdf-footer { position: fixed;bottom: -{$this->footerHeight}px;left: 0;right: 0;height: {$this->footerHeight}px;                }
                .pdf-content{/*position:fixed;*//*top:100px;bottom:180px;left:20px;right:20px;*//*outline:2px solid red;*/}
                .page { width:90%; margin:3px auto 3px auto;page-break-after: always;position:relative; }
                .page:first-child{/*page-break-after: avoid;*/}
                 p,li{page-break-inside: avoid;}
                /*
                .pdf-content table{border-collapse:collapse;width:100%;}
                .pdf-content table tr th{border:1px solid #cccccc;background-color:#cccccc;}
                .pdf-content table tr td{border:1px solid #cccccc; }
                .pdf-content table tr th,
                .pdf-content table tr td{margin:0;padding:3px 5px;}
                 */
                .pdf-content Xtable{border-collapse:collapse;width:100%;}
                .pdf-content Xtable tr th{border-bottom:2px solid black;font-family: "Montserrat Medium";font-weight:normal;}
                .pdf-content Xtable tr td{border-bottom:1px solid #444;font-family: "Montserrat Light"; }
                .pdf-content Xtable.item tr:first-child td{border-top:1px solid #444; }
                .pdf-content Xtable tr th,
                .pdf-content Xtable tr td{margin:0;padding:3px 5px;color:#000000;font-size:10px !important;}
                .pdf-content Xtd.key{font-family: "Montserrat Bold","Open Sans", sans-serif;color:#bfbfbf;/*font-weight:600;*/}
                .pdf-content Xtable.header{width:100%;}
                .pdf-content Xtable.header tr td{border-bottom:none;}

                .page-number:before { content: counter(page);  }
                .total-pages:before { content: counter(pages); }
            </style>
        </head>
        <body>
            <div id="watermark"></div>
            <div class="pdf-header">{$this->headerTemplate}</div>
            <div class="pdf-footer">{$this->footerTemplate}</div>
            <div class="pdf-content">$content</div>
        </body>
        </html>
        HTML;
    }

    // Setters para configuración dinámica
    public function setHeaderTemplate(string $template): self {
        $this->headerTemplate = $template;
        return $this;
    }

    public function setFooterTemplate(string $template): self {
        $this->footerTemplate = $template;
        return $this;
    }

    public function setHeaderHeight(int $height): self {
        $this->headerHeight = $height;
        return $this;
    }

    public function setFooterHeight(int $height): self {
        $this->footerHeight = $height;
        return $this;
    }

    public function setMargins(array $margins): self {
        $this->margins = array_merge($this->margins, $margins);
        return $this;
    }

}

class PDF{   //new

    public static $html_pdf_header = false;
    public static $html_pdf_page_num = false;
    public static $html_pdf_num_pages = 0;
    public static $html_pdf_footer = '</html>';
    public static $html_pdf_detail = '<p>line</p>';
    public static $pdf_paper_format = 'a4';
    public static $pdf_orientation  = 'portrait';
    public static $pdf_filename = false;
    public static $pdf_savedir = false;
    public static $pdf_savefilename = false;
    public static $pdf_watermark = false; //'https://tienda.extralab.net/media/works/images/2/.big_a4.jpg';
    public static $pdf_watermark_opacity = '0.2';
    public static $pdf_watermark_style = 'z-index:-1000;background-size:cover;';

   // public static function make_html_header(){}

    public static $config = [
        'headerHeight' => 110,
        'footerHeight' => 50,
        'margins' => [
            'top' => 130,
            'right' => 25,
            'bottom' => 50,
            'left' => 25
        ],
    ];

    public static function html2pdf($html){


        $_url = 'https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR;

        $replace = array();
        $replace['[SITE_URL]']     = $_url;   
        $replace['[SITE_NAME]']    = CFG::$vars['site']['title'];   
        $replace['[SITE_ADDRESS]'] = CFG::$vars['site']['address'];   
        $replace['[SITE_EMAIL]']   = CFG::$vars['site']['email'];   
        $replace['[SITE_PHONE]']   = CFG::$vars['site']['phone'];           
  
        //$html = str_replace(array_keys($replace),array_values($replace),$html);

        $pdf = new PdfGenerator(self::$config);

        $pdf->watermark_code   = self::$pdf_watermark ?'#watermark {'.self::$pdf_watermark_style.';background: url('.self::$pdf_watermark.');opacity:'.self::$pdf_watermark_opacity.'}':'#watermark {display:none;}';
        $pdf->orientation      = self::$pdf_orientation ?? 'portrait';
        $pdf->pageSize         = self::$pdf_paper_format ?? 'a4';
        $pdf->pdf_filename     = self::$pdf_filename ?? false;
        $pdf->pdf_savedir      = self::$pdf_savedir ?? false;
        $pdf->pdf_savefilename = self::$pdf_savefilename ?? false;


        /*
        $_pdf_header = CFG::$vars['templates']['pdf']['header'] 
                     ? CFG::$vars['templates']['pdf']['header'] 
                     : '<table class="header"><tr>
        <td align="left" style="width:35%;vertical-align:middle;"><img src="./_modules_/plan_contingencia/images/logo_hulamm.png" style="height:55px;width:auto;" /></td>
        <td align="center" style="width:40%;" class="address"><b>Hospital Universitario Los Arcos del Mar Menor</b><br />Paraje Torre Octavio, s/n   30739 <br />Pozo Aledo –  San Javier (Murcia)</td>
        <td align="right" style="width:25%;vertical-align:middle;"><img src="./_modules_/plan_contingencia/images/logo_sms.png" style="height:70px;width:auto;" /></td>
        </tr></table>';
        */
        $_pdf_header = CFG::$vars['templates']['pdf']['header'] 
                     ? CFG::$vars['templates']['pdf']['header'] 
                     : '<table class="header"><tr>
        <td align="center" style="width:15%;vertical-align:middle;"><img src="./media/images/logo.png" style="height:55px;width:auto;" /></td>
        <td align="center" style="width:70%;" class="address"><b>[SITE_NAME]</b><br />[SITE_ADDRESS]</td>
        <td align="center" style="width:15%;vertical-align:middle;"><img src="./media/images/logo.png" style="height:55px;width:auto;" /></td>
        </tr></table>';

        $replace = array();
        $replace['[SITE_URL]']     = $_url;   
        $replace['[SITE_NAME]']    = CFG::$vars['site']['title'];   
        $replace['[SITE_ADDRESS]'] = CFG::$vars['site']['address'];   
        $replace['[SITE_EMAIL]']   = CFG::$vars['site']['email'];   
        $replace['[SITE_PHONE]']   = CFG::$vars['site']['phone'];   

        $_pdf_header = CFG::$vars['templates']['pdf']['header'] 
                     ? CFG::$vars['templates']['pdf']['header'] 
                     : '<table class="header" style="margin-top:20px;width:100%">
        <tr>
        <td align="left" style="width:30%;vertical-align:middle;">
          <img src="./media/images/logo.png" style="height:55px;width:auto;" />
        </td>
        <td align="center" style="width:40%;" class="address"><b>'. CFG::$vars['site']['title'] .'</b>'. CFG::$vars['site']['address'] .'</td>
        <td align="right" style="width:30%;vertical-align:middle;">
          <img src="./media/images/logo.png" style="height:55px;width:auto;" />
        </td>
        </tr>
        </table>';

        $_pdf_header  =  str_replace(array_keys($replace),array_values($replace),$_pdf_header);
        
        /*
        $pdf->setHeaderTemplate('<table class="header"><tr>
        <td align="left" style="width:35%;vertical-align:middle;"><img src="./_modules_/plan_contingencia/images/logo_hulamm.png" style="height:55px;width:auto;" /></td>
        <td align="center" style="width:40%;" class="address"><b>Hospital Universitario Los Arcos del Mar Menor</b><br />Paraje Torre Octavio, s/n   30739 <br />Pozo Aledo –  San Javier (Murcia)</td>
        <td align="right" style="width:25%;vertical-align:middle;"><img src="./_modules_/plan_contingencia/images/logo_sms.png" style="height:70px;width:auto;" /></td>
        </tr></table>')
        */
        $pdf->setHeaderTemplate($_pdf_header)
            ->setFooterTemplate('<div style="text-align: center;/*background-color:orange;*/">Página <span class="page-number"></span><!-- de <span class="total-pages"></span>--></div>');

        // Generar el PDF
        $pdf->generatePdf($html);
      
    }

}