<?php

    //$csv = array();

    $rows = array();

    include(SCRIPT_DIR_MODULE.'/index.php');

 //   Vars::debug_var($headers,'headers');
 //   Vars::debug_var($rows,'rows');

    $csv_separator = CFG::$vars['modules']['csv']['separator'] ? CFG::$vars['modules']['csv']['separator'] : ';';


/*****/
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.($download_file?$download_file:'data').'.csv');

    $output = fopen('php://output', 'w');
    
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    if($headers) {
        fputcsv($output, $headers , $csv_separator );
        if($local_file) $contenido = implode($csv_separator,$headers)."\n";
    }
    
    foreach ($rows as $row){ 
      fputcsv($output, $row , $csv_separator);
      if($local_file) $contenido .= implode($csv_separator,$row)."\n";
    }
    
    if($footers) { 
      fputcsv($output, $footers, $csv_separator );
      if($local_file) $contenido .= implode($csv_separator,$footers)."\n";
    }

    if($local_file){
        $filename=SCRIPT_DIR_MODULE.'/'.$local_file.'.csv';
        if($hfp = fopen($filename,'w+')){
          //$contenido = '<'."?\n".stripslashes($contenido)."\n";
          fwrite($hfp,$contenido);
          fclose($hfp);
        }
    }






/****/

/**** example of MODULE/csv.php




      $table = 'FAMILIAS_HIST';
      $key   = 'ID_FAMILIA';
      $where = '';
      $fields = array();
	    $totals = array();

	    $fields['Curso'] = 'CURSO';
	    $fields['Familia'] = 'ID_FAMILIA';
	    $fields['Jefe'] = "JEFE";
	    $fields['Coordinador'] = 'COORDINADOR';
	   
      $sql = 'SELECT '.implode($fields,',').' FROM '.$table;
            
      $headers = array_keys($fields);
      $rows = Informe::sqlQuery($sql);
      $footers = false;




*****/