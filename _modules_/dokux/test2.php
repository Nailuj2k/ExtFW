<?php


require './vendor/autoload.php';


 function _gettext($filename){

    $file_name = HULAMM_WARE_INBOX_DIR.'/'.$filename.'.pdf';
    $file_name_out = HULAMM_WARE_INBOX_OCR.'/'.$filename.'.txt';
    $parser = new \Smalot\PdfParser\Parser();


    echo '<h4>'.$filename.'</h4>';

    if(file_exists($file_name)){
    

        try{

            $pdf    = $parser->parseFile($file_name);

            $details  = $pdf->getDetails();
            foreach ($details as $property => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                echo $property . ' => ' . $value . "\n";
            }

            $text = $pdf->getText();
            if($text){
                echo '<p style="font-size:0.8em;border:1px solid gray; margin:10px;">'.$text.'</p>';
                if($hfp = fopen($file_name_out,'w'))  fwrite($hfp,stripslashes($text));
                fclose($hfp);
            }

        }catch (Exception $e) {
                echo 'No se puede leer este PDF '; 
                
        }

    }else{
        $ajax_result['text'] = 'ERROR No existe el archivo '.$file_name; 
    }

}

echo '<pre>';


_gettext('ACUSEDERECIBO_signed');
_gettext('presupuesto_22_plataforma_dih');

echo '</pre>';
