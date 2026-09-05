<?php

        /***
        $cfg['oracle']['host'] = "(DESCRIPTION =
            (ADDRESS = (PROTOCOL = TCP)(HOST = hlaracpro01.ad.sms.carm.es)(PORT = 1521))
            (CONNECT_DATA =
              (SERVER = DEDICATED)
              (SERVICE_NAME = cws11)
            )
        )";
        $cfg['oracle']['config'] = "NLS_TERRITORY='SPAIN' NLS_LANGUAGE='SPANISH' NLS_DATE_FORMAT='DD-MM-YYYY'";
        $cfg['oracle']['user'] = 'auxhla';
        $cfg['oracle']['password'] = 'auxhla';
        $cfg['oracle']['charset'] = 'AL32UTF8';
        $cfg['oracle']['decimal_separator'] = '.';

      // $sql = "SELECT NAME, SURNAME, SURNAME2, SEX, MEDICALRECORD, to_char(trunc((SYSDATE - DATEOFBIRTH) / 365)) AS EDAD FROM PACIENTE WHERE NHC = '230835'";  //{$params['id']}'";
      $sql   = 'SELECT * FROM ( ';      
      $sql  .= ' SELECT  NAME, SURNAME, SURNAME2, SEX, MEDICALRECORD, to_char(trunc((SYSDATE - DATEOFBIRTH) / 365)) AS EDAD, row_number() OVER(ORDER BY NAME) rnk ';
      $sql  .= ' FROM PACIENTE ';
      //  $sql  .= ' WHERE '.$this->field_key.'='.$this->field_value;
      //  $sql  .= ' GROUP BY '.$this->groupby;
       $sql  .= ') WHERE rnk BETWEEN 0 AND 3';

        ***/


        /*******/
        $cfg['oracle']['host'] = "(DESCRIPTION =
            (ADDRESS = (PROTOCOL = TCP)(HOST = hlaracpro01.ad.sms.carm.es)(PORT = 1521))
            (CONNECT_DATA =
              (SERVER = DEDICATED)
              (SERVICE_NAME = hulamm11)
            )
          )";

/**
        $cfg['oracle']['host'] = "(DESCRIPTION =
            (ADDRESS = (PROTOCOL = TCP)(HOST = scanhla01.sms.carm.es)(PORT = 1521))
            (CONNECT_DATA =
              (SERVER = DEDICATED)
              (SERVICE_NAME = hulamm11)
            )
          )";
**/
/**
        $cfg['oracle']['host'] = "(DESCRIPTION =
            (ADDRESS = (PROTOCOL = TCP)(HOST = orascdr02.sms.carm.es)(PORT = 1521))
            (CONNECT_DATA =
              (SERVER = DEDICATED)
              (SERVICE_NAME = hulamm11)
            )
          )";
**/

        $cfg['oracle']['config'] = "NLS_TERRITORY='SPAIN' NLS_LANGUAGE='SPANISH' NLS_DATE_FORMAT='DD-MM-YYYY HH24:MI:SS'"; //-MM-DD'";;
        $cfg['oracle']['user'] = 'dwh_web';
        $cfg['oracle']['password'] = 'dwh_webpw7';  // pruebas: dwh_webpw3
        $cfg['oracle']['charset'] = 'AL32UTF8';
        $cfg['oracle']['decimal_separator'] = '.';
       
        $sql = "SELECT EXTENSION_ID,EXTENSION FROM COM_TEL_EXTENSIONES";  // WHERE NHC='338185'";

       $sql   = 'SELECT * FROM ( ';      
       $sql  .= ' SELECT  EXTENSION_ID,EXTENSION , row_number() OVER(ORDER BY EXTENSION) rnk ';
       $sql  .= ' FROM COM_TEL_EXTENSIONES ';
       //  $sql  .= ' WHERE '.$this->field_key.'='.$this->field_value;
       //  $sql  .= ' GROUP BY '.$this->groupby;
       $sql  .= ') WHERE rnk BETWEEN 0 AND 3';
       /****/









        include_once(SCRIPT_DIR_CLASSES.'/exceptions/IException.php');
        include_once(SCRIPT_DIR_CLASSES.'/exceptions/CustomException.php');
        include_once(SCRIPT_DIR_CLASSES.'/exceptions/OracleConnectionException.php');
        include_once(SCRIPT_DIR_CLASSES.'/db/db.oracle.php');   

        $db_instance = OracleConnection::singleton();
        $db_instance->connect();
        $resource = $db_instance->getResource();




        $handle = oci_parse($resource, $sql);
        $r = oci_execute($handle);
        if ($r){
            echo $sql.'<br /><br />';
            if ($handle){
                echo '<pre style="max-height:350px;overflow:auto;">';
                while ($row =  oci_fetch_array($handle, OCI_ASSOC+OCI_RETURN_NULLS)) {  
                    print_r($row);
                }
                echo '</pre>';
            } 
        }else {
            $e = oci_error($handle);  // Para errores de oci_execute, pase el gestor de sentencia
            $error = 'ERROR: '.$e['code'].': '.htmlentities($e['message'])
                   . "\n<pre>\n"
                   . htmlentities($e['sqltext'])
                   . sprintf("\n%".($e['offset']+1)."s", "^")
                   . "\n</pre>\n";
            echo $error.'<br />';
        }





/*********



        $result = array();
        $result['error'] = 0;
        $result['msg'] = 'ok';
        $to = new TableOracle();
        //$sql = "SELECT to_char(trunc((SYSDATE - DATEOFBIRTH) / 365)) AS EDAD FROM PACIENTE WHERE NHC = '{$params['id']}'";
        //$sql = "SELECT DATEOFBIRTH EDAD FROM PACIENTE WHERE NHC = '{$params['id']}'";
        $result['sql'] = $sql;
        $paciente = $to->asArray($sql);
        $result['paciente'] = $paciente[0];
        echo json_encode($result);     
**/