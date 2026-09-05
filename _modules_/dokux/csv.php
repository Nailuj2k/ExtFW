<?php



      $table = 'DOKU_FILES';
      $key   = 'ID';
      $where = '';
      $fields = array();
	    $totals = array();

	    $fields['DNI'] = 'DNI';
	    $fields['Nombre'] = 'NAME';
	   
      $sql = 'SELECT '.implode(',',$fields).' FROM '.$table;
            
      $headers = array_keys($fields);
      $rows = Table::sqlQuery($sql);
      $footers = false;