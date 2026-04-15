<?php

    if(Administrador()){

        if($_ARGS[2]=='banners'){

            $tablename = 'GES_BANNERS_LOG';

            $fields = array();
            $fields['ID']          = 'ID';
            $fields['ID_BANNER']   = 'ID_BANNER';
            $fields['TIME']        = 'TIME';
            $fields['IP']          = 'IP';
            $fields['BROWSER']     = 'BROWSER';
            $fields['VERSION']     = 'VERSION';
            $fields['PLATFORM']    = 'PLATFORM';
            $fields['CITY']        = 'CITY';
            $fields['REGION']      = 'REGION';
            $fields['COUNTRY']     = 'COUNTRY';
            $fields['ACTION']      = 'ACTION';
            $fields['USER_AGENT']  = 'USER_AGENT';
            $fields['REQUEST_URI'] = 'REQUEST_URI';
            $fields['TOKEN']       = 'TOKEN';
            $fields['LAT']         = 'LAT';
            $fields['LON']         = 'LON';

            //SELECT COUNT(ID) FROM GES_BANNERS_LOG WHERE ID_BANNER IN (1,2)
            ini_set('memory_limit', '1024M');


         // $sql = 'SELECT '.implode(',',$fields).' FROM '.$tablename.' WHERE  TIME LIKE \'2022%\' AND  ID_BANNER IN (1,2)';
            $sql = 'SELECT '.implode(',',$fields).' FROM '.$tablename.' WHERE  TIME>= \''.$_ARGS[3].' 00:00:00\' AND  TIME<= \''.$_ARGS[4].' 23:59:59\' AND ID_BANNER IN (1,2)';

            $download_file = 'banners_'.date('YmdHi');
            $local_file = false; //'banners_'.date('YmdHi'); // taStr::SanitizeName(TableMysql::getFieldValue( "SELECT FECHA FROM ".TABLE." WHERE IFNULL(VALID,0)=0 LIMIT 1"));


            $headers = array_keys($fields);
            $rows    = Table::sqlQuery($sql);

            // guardar archivo, comprobar que se ha guardao, y ejecutar
            // Table::sqlExec('UPDATE '.TABLE.' SET VALID = 1');

            $footers = false;

        }else if($_ARGS[2]=='users'){

            $tablename = TB_USER;

            $fields['user_id']  = 'user_id';
            $fields['username']  = 'username';
            //$fields['user_date_created']  = 'user_date_created';
            $fields['user_last_login']  = 'user_last_login';
            $fields['user_email']  = 'user_email';
            $fields['user_fullname']  = 'user_fullname';
            //$fields['user_active']  = 'user_active';
            //$fields['user_verify']  = 'user_verify';
            //$fields['id_pais']  = 'id_pais';
            //$fields['id_provincia']  = 'id_provincia';
            //$fields['id_municipio']  = 'id_municipio';
            //$fields['id_localidad']  = 'id_localidad';
            $fields['user_card_id']  = 'user_card_id';
            //$fields['user_lpd_data']  = 'user_lpd_data';
            //$fields['user_lpd_publi']  = 'user_lpd_publi';


            //SELECT COUNT(ID) FROM GES_BANNERS_LOG WHERE ID_BANNER IN (1,2)
            ini_set('memory_limit', '1024M');

            $products = Table::asArrayValues('SELECT AGENCIA_ID, NAME FROM CLI_AGENCIAS','AGENCIA_ID','NAME');

            $sql = 'SELECT '.implode(',',$fields).' FROM '.$tablename.' WHERE  user_id>1';

            $download_file = 'userss_'.date('YmdHi');
            $local_file = false;

            $headers = array_keys($fields);
            $rows    = Table::sqlQuery($sql);

            // Vars::debug_var($headers);
            // Vars::debug_var($rows);
            // die();
            $footers = false;

        }else if($_ARGS[2]=='order-lines'){

            ini_set('memory_limit', '1024M');

            //$products = Table::asArrayValues('SELECT PRODUCT_ID, NAME FROM CLI_PRODUCTS','PRODUCT_ID','NAME');

            $tablename = 'CLI_ORDER_LINES';
            $fields['ORDER_LINE_ID']  = 'ORDER_LINE_ID';
            $fields['ORDER_ID']  = 'ORDER_ID';
            $fields['PRODUCT_ID']  = 'PRODUCT_ID';
            $fields['ORDER_LINE']  = 'ORDER_LINE';
            $fields['UD']  = 'UD';
            $fields['PRODUCT_PRICE']  = 'PRODUCT_PRICE';
            $fields['TOTAL']  = 'PRODUCT_PRICE*UD AS TOTAL';
            $fields['TOKEN']  = 'TOKEN';

            $sql = 'SELECT '.implode(',',array_values($fields)).' FROM '.$tablename; //.' LIMIT 100';

            $download_file = 'orders_lines_'.date('YmdHi');
            $local_file = false;

            $headers = array_keys($fields);
            $_rows    = Table::sqlQuery($sql);
            
            $rows = array();
            foreach ($_rows as $row){
                $row['PRODUCT_NAME'] = $products[$row['PRODUCT_ID']];
                //$row['PRODUCT_PRICE'] = str_replace('.',',',$row['PRODUCT_PRICE']);
                //$row['TOTAL'] = $row['UD']*$row['PRODUCT_PRICE'];
                $rows[]=$row;
            }
            
            //Vars::debug_var($headers);
            //Vars::debug_var($rows);
            //die();
            $footers = false;

        }else if($_ARGS[2]=='orders'){

            function code2str($code){
                $codigos = array();
                $codigos['0']  = 'Desconocido';
                $codigos['SIS101']  = 'Tarjeta caducada';
                $codigos['SIS102']  = 'Tarjeta en excepción transitoria o bajo sospecha de fraude';
                $codigos['SIS106']  = 'Intentos de PIN excedidos';
                $codigos['SIS125']  = 'Tarjeta no efectiva';
                $codigos['SIS129']  = 'Código de seguridad (CVV2/CVC2) incorrecto';
                $codigos['SIS180']  = 'Tarjeta ajena al servicio';
                $codigos['SIS184']  = 'Error en la autenticación del titular';
                $codigos['SIS190']  = 'Denegación sin especificar Motivo';
                $codigos['SIS191']  = 'Fecha de caducidad errónea';
                $codigos['SIS202']  = 'Tarjeta en excepción transitoria o bajo sospecha de fraude con retirada de tarjeta';
                $codigos['SIS904']  = 'Comercio no registrado en FUC';
                $codigos['SIS909']  = 'Error de sistema ';
                $codigos['SIS9912'] = 'Emisor no disponible';
                $codigos['SIS912']  = 'Emisor no disponible';
                $codigos['SIS950']  = 'Operación de devolución no permitida';
                $codigos['SIS9064'] = 'Número de posiciones de la tarjeta incorrecto';
                $codigos['SIS9078'] = 'No existe método de pago válido para esa tarjeta';
                $codigos['SIS9093'] = 'Tarjeta no existente';
                $codigos['SIS9218'] = 'El comercio no permite op. seguras por entrada /operaciones';
                $codigos['SIS9253'] = 'Tarjeta no cumple el check-digit';
                $codigos['SIS9256'] = 'El comercio no puede realizar preautorizaciones';
                $codigos['SIS9257'] = 'Esta tarjeta no permite operativa de preautorizaciones';
                $codigos['SIS9261'] = 'Operación detenida por superar el control de restricciones en la entrada al SIS';
                $codigos['SIS9913'] = 'Error en la confirmación que el comercio envía al TPV Virtual (solo aplicable en la opción de sincronización SOAP)';
                $codigos['SIS9914'] = 'Confirmación “KO” del comercio (solo aplicable en la opción de sincronización SOAP)';
                $codigos['SIS9928'] = 'Anulación de autorización en diferido realizada por el SIS (proceso batch)';
                $codigos['SIS9929'] = 'Anulación de autorización en diferido realizada por el comercio';
                $codigos['SIS9104'] = 'Comercio con “titular seguro” y titular sin clave de compra segura';
                $codigos['SIS9915'] = 'A petición del usuario se ha cancelado el pago';
                $codigos['SIS9094'] = 'Rechazo servidores internacionales';
                $codigos['SIS944']  = 'Sesión Incorrecta';
                $codigos['SIS913']  = 'Pedido repetido';
                $codigos['STEP001'] = 'Pedido creado';
                $codigos['STEP002'] = 'Pedido modificado';
            //9551	SIS0551	ERROR en el proceso de autenticación.
            ///    https://www.kebes.es/2019/09/16/codigos-respuesta-tpv-redsys/

                $codigos['0000'] = 'Transacción autorizada para pagos y preautorizaciones';
                $codigos['0099'] = 'Transacción autorizada para pagos y preautorizaciones';
                $codigos['0900'] = 'Transacción autorizada para devoluciones y confirmaciones';
                $codigos['0101'] = 'Tarjeta caducada';
                $codigos['0102'] = 'Tarjeta en excepción transitoria o bajo sospecha de fraude';
                $codigos['0104'] = 'Operación no permitida para esa tarjeta o terminal';
                $codigos['0116'] = 'Disponible insuficiente';
                $codigos['0118'] = 'Tarjeta no registrada';
                $codigos['0180'] = 'Tarjeta ajena al servicio';
                $codigos['0184'] = 'Error en la autenticación del titular';
                $codigos['0190'] = 'Denegación sin especificar Motivo';
                $codigos['0191'] = 'Fecha de caducidad errónea';
                $codigos['0202'] = 'Tarjeta en excepción transitoria o bajo sospecha de fraude con retirada de tarjeta';
                $codigos['0912'] = 'Emisor no disponible';
                $codigos['9912'] = 'Emisor no disponible';
                
                $codigos['BIZ00000'] = 'Operación realizada correctamente.';
                $codigos['BIZ00001'] = 'Parámetro de entrada obligatorio no completado.';
                $codigos['BIZ00002'] = 'El formato de algún parámetro es incorrecto.';
                $codigos['BIZ00003'] = 'No se encontró el elemento.';
                $codigos['BIZ00005'] = 'Error interno del sistema.';
                $codigos['BIZ00006'] = 'Error de seguridad 3DES o MAC X9.19';
                $codigos['BIZ00007'] = 'Operación no permitida.';
                $codigos['BIZ00008'] = 'Beneficiario no encontrado.';
                $codigos['BIZ00009'] = 'Ordenante no encontrado.';
                $codigos['BIZ00202'] = 'Funcionalidad aún no implementada.';
                $codigos['BIZ00213'] = 'Error de autenticación en la petición recibida. Fallo en secuencia de seguridad.';
                $codigos['BIZ00224'] = 'La respuesta de la entidad a la autenticación por RTP es KO.';
                $codigos['BIZ00225'] = 'La autenticación por request to pay no ha finalizado con éxito.';

                $codigos['WT0001'] = 'Pedido realizado';
                $codigos['PAYPAL001'] = 'Pagado Paypal';
                // Cualquier otro valor Transacción denegada    
                if($code==='0' || !$code) {
                  $code = $code.' Desconocido';
                }else if ($codigos[$code]){
                  $code = $code.' '.$codigos['0000'];
                }else{
                  $code = $code .' Transacción denegada';
                }
                return $code;
            }

            $tablename = 'CLI_ORDERS';
            $fields['ORDER_ID']  = 'ORDER_ID';
            $fields['CUSTOMER_ID']  = 'CUSTOMER_ID';
            $fields['ORDER_DATE']  = 'ORDER_DATE';
            $fields['ORDER_TIME']  = 'ORDER_TIME'; 
            $fields['CHECKOUT']  = 'CHECKOUT';
            $fields['0 AS TOTAL']  = 'TOTAL';
            $fields['SHIPPING_MODE']  = 'SHIPPING_MODE';
            $fields['CODE']  = 'CODE';
            $fields['ORDER_STATE']  = 'ORDER_STATE';
            $fields['SHIPPING']  = 'SHIPPING';
            $fields['TOKEN']  = 'TOKEN';
            $fields['PHONE']  = 'PHONE';
            $fields['EMAIL']  = 'EMAIL';
            $fields['NAME']  = 'Nombre Cliente';
            $fields['CARD_ID']  = 'DNI Envío';
            $fields['ID_COUNTRY']  = 'País Envío';
            $fields['ID_STATE']  = 'Provincia Envío';
            $fields['ID_CITY']  = 'Municipio Envío';
            $fields['ID_COUNTY']  = 'Localidad Envío';
            $fields['ADDRESS']  = 'Dirección Envío';
            $fields['ZIP']  = 'CP Envío';
          //$fields['CREATED_BY']  = '';
          //$fields['CREATION_DATE']  = '';
          //$fields['LAST_UPDATED_BY']  = '';
          //$fields['LAST_UPDATE_DATE']  = '';
            $fields['COUPON']  = 'COUPON';
          //$fields['FILE_INVOICE']  = '';
            $fields['DISCOUNT']  = 'Descuwnto';
            $fields['INVOICE_NAME']  = 'Nombre Cliente Fac.';
            $fields['INVOICE_CARD_ID']  = 'DNi Fac.';
            $fields['INVOICE_EMAIL']  = 'Email Fac.';
            $fields['INVOICE_PHONE']  = 'Teléfoo Fac.';
            $fields['INVOICE_ZIP']  = 'CP Fac.';
            $fields['INVOICE_ADDRESS']  = 'Dirección Fac.';
            $fields['INVOICE_ID_COUNTRY']  = 'Pais Fac.';
            $fields['INVOICE_ID_STATE']  = 'Provincia Fac.';
            $fields['INVOICE_ID_CITY']  = 'Municipio Fac.';
            $fields['INVOICE_ID_COUNTY']  = 'Localidad Fac.';
            $fields['NOTES']  = 'NOTES';
            $fields['MRW_Estado']  = 'MRW_Estado';
            $fields['MRW_Mensaje']  = 'MRW_Mensaje';
            $fields['MRW_NumeroEnvio']  = 'MRW_NumeroEnvio';
            $fields['MRW_NumeroSolicitud']  = 'MRW_NumeroSolicitud';
            $fields['MRW_Url']  = 'MRW_Url';
            $fields['BULTOS']  = 'BULTOS';

            ini_set('memory_limit', '1024M');

            $sql = 'SELECT '.implode(',',array_keys($fields)).' FROM '.$tablename; //.' WHERE  user_id>1';

            $download_file = 'orders_'.date('YmdHi');
            $local_file = false;

            $checkout_modes=array();
            $checkout_modes['0']='Ninguna';
            $checkout_modes['1']='Redsys';
            $checkout_modes['2']='Paypal';
            $checkout_modes['3']='Transferencia';
            $checkout_modes['4']='Contrarreembolso';
            $checkout_modes['5']='Bitcoin';
            $checkout_modes['6']='Bizum';
            $checkout_modes['7']='Ceca';             
            //Vars::debug_var($checkout_modes);

            $shipping_modes = Table::asArrayValues('SELECT AGENCIA_ID, NAME FROM CLI_AGENCIAS','AGENCIA_ID','NAME');
            //Vars::debug_var($shipping_modes);

            $order_states   = array('0'=>'Iniciado','1'=>'Realizado',                 '2'=>'Pagado','3'=>'Anulado','4'=>'Preparado',            '5'=>'Enviado','6'=>'Facturado','7'=>'Entregado','8'=>'Devuelto','9'=>'Error');

            $countries     = Table::asArrayValues('SELECT pais_id, pais_name FROM CFG_PAIS','pais_id','pais_name');
            $states        = Table::asArrayValues('SELECT provincia_id, provincia_name FROM CFG_PROVINCIA','provincia_id','provincia_name');
            $cities        = Table::asArrayValues('SELECT municipio_id, municipio_name FROM CFG_MUNICIPIO','municipio_id','municipio_name');
       
            $headers = array_values($fields);
            $_rows    = Table::sqlQuery($sql);
            $rows = array();
            foreach ($_rows as $row){
                $row['CHECKOUT']      = $checkout_modes[$row['CHECKOUT']];
                $row['SHIPPING_MODE'] = $shipping_modes[$row['SHIPPING_MODE']];
                $row['CODE']          = code2str($row['CODE']);
                $row['ORDER_STATE']   = $order_states[$row['ORDER_STATE']];
                $row['ID_COUNTRY']    = $countries[$row['ID_COUNTRY']];
                $row['ID_STATE']      = $states[$row['ID_STATE']];
                $row['ID_CITY']       = $cities[$row['ID_CITY']];
                $row['INVOICE_ID_COUNTRY']  = $countries[$row['INVOICE_ID_COUNTRY']];
                $row['INVOICE_ID_STATE']    = $states[$row['INVOICE_ID_STATE']];
                $row['INVOICE_ID_CITY']     = $cities[$row['INVOICE_ID_CITY']];
                $row['TOTAL'] = TableMySql::getFieldValue('SELECT SUM(PRODUCT_PRICE*UD) FROM CLI_ORDER_LINES WHERE ORDER_ID = '.$row['ORDER_ID']);
                $rows[]=$row;
            }

            // Vars::debug_var($headers);
            // Vars::debug_var($rows);
            // die();
            $footers = false;
            
        }

    }