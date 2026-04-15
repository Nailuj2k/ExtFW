<?php

/*
 * Forma de request para enviar etiqueta
 *
                        $request['DatosEntrega']['Direccion']['CodigoDireccion'] = '';
                        $request['DatosEntrega']['Direccion']['CodigoTipoVia'] = '';
                        $request['DatosEntrega']['Direccion']['Via'] = '';
                        $request['DatosEntrega']['Direccion']['Numero'] = '';
                        $request['DatosEntrega']['Direccion']['Resto'] = '';
                        $request['DatosEntrega']['Direccion']['CodigoPostal'] = '';
                        $request['DatosEntrega']['Direccion']['Poblacion'] = '';
                        $request['DatosEntrega']['Direccion']['Provincia'] = '';
                        $request['DatosEntrega']['Direccion']['CodigoPais'] = '';
                        $request['DatosEntrega']['Nif'] = '';
                        $request['DatosEntrega']['Nombre'] = '';
                        $request['DatosEntrega']['Telefono'] = '';
                        $request['DatosEntrega']['Contacto'] = '';
                        $request['DatosEntrega']['ALaAtencionDe'] = '';
                        $request['DatosEntrega']['Observaciones'] = '';
                        $request['DatosServicio']['Peso'] = '';
                        $request['DatosServicio']['Mail'] = '';
                        $request['DatosServicio']['SMS'] = '';
 *
 */

class WebServiceMRW{

    private $dbh;
    /*
    Código de Franquicia: 02803
    Código de Abonado: 005108
    Código de Departamento: Vacío (en blanco)
    Usuario MRW: 02803SHGILFAMILY
    Contraseña MRW: RseSxHHTymS5!
    Contraseña de Seguimiento: FK579F
    */

    public function __construct()    {

        // http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL (PRE)
        // http://sagec.mrw.es/MRWEnvio.asmx?WSDL (PRO)
        $this->wsdl_url = CFG::$vars['shop']['mrw']['url'];  //"http://sagec-test.mrw.es/MRWEnvio.asmx?WSDL";

        CFG::$vars['shop']['mrw']['codigo_departamento'] = '';

        $this->cabeceras = array(
            'CodigoFranquicia'   => CFG::$vars['shop']['mrw']['codigo_franquicia'],   // Obligatorio
            'CodigoAbonado'      => CFG::$vars['shop']['mrw']['codigo_abonado'],      // Obligatorio
            'CodigoDepartamento' => CFG::$vars['shop']['mrw']['codigo_departamento'], // Opcional - Se puede omitir si no se han creado departamentos en sistemas de MRW.
            'UserName'           => CFG::$vars['shop']['mrw']['username'],            // Obligatorio
            'Password'           => CFG::$vars['shop']['mrw']['password']             // Obligatorio
        );

        $this->init();
    }

    private function init()    {
        try {
            $this->clientMRW = new SoapClient(
                $this->wsdl_url,
                array(
                    'trace' => TRUE
                )
            );
        } catch (SoapFault $e) {
          //Messages::error("Error creando cliente SOAP: %s<br />\n".$e->__toString());
            printf("Error creando cliente SOAP: %s<br />\n", $e->__toString());
            return false;
        }
    }

    public function createOrder($request)    {
        $result = array();
        $responseCode = '';

        // ## PARAMETROS DEL ENVIO ## 
        // Datos de fecha actual
        date_default_timezone_set('CET');
        $hoy = date("d/m/Y");

        $params = array(
            'request' => array(
                'DatosEntrega' => array(
                    // ## DATOS DESTINATARIO ##
                    'Direccion' => array(
                        'CodigoDireccion' => $request['DatosEntrega']['Direccion']['CodigoDireccion'], //Opcional - Se puede omitir. Si se indica sustituira al resto de parametros
                        'CodigoTipoVia' => $request['DatosEntrega']['Direccion']['CodigoTipoVia'], //Opcional - Se puede omitir aunque es recomendable usarlo
                        'Via' => $request['DatosEntrega']['Direccion']['Via'], //Obligatorio
                        'Numero' => $request['DatosEntrega']['Direccion']['Numero'], //Obligatorio - Recomendable que sea el dato real. Si no se puede extraer el dato real se pondra 0 (cero)
                        'Resto' => $request['DatosEntrega']['Direccion']['Resto'], //Opcional - Se puede omitir.
                        'CodigoPostal' => $request['DatosEntrega']['Direccion']['CodigoPostal'], //Obligatorio
                        'Poblacion' => $request['DatosEntrega']['Direccion']['Poblacion'], //Obligatorio
                        'Estado' => $request['DatosEntrega']['Direccion']['Provincia'], //Obligatorio
                        //'Estado' => '', //Opcional - Se debe omitir para envios nacionales.
                        'CodigoPais' => $request['DatosEntrega']['Direccion']['CodigoPais'] //Opcional - Se puede omitir para envios nacionales.
                    ),
                    'Nif' => $request['DatosEntrega']['Nif'], //Opcional - Se puede omitir.
                    'Nombre' => $request['DatosEntrega']['Nombre'], //Obligatorio
                    'Telefono' => $request['DatosEntrega']['Telefono'], //Obligatorio
                    'Contacto' => $request['DatosEntrega']['Contacto'], //Opcional - Muy recomendable
                    'ALaAtencionDe' => $request['DatosEntrega']['ALaAtencionDe'], //Opcional - Se puede omitir.
                    'Observaciones' =>  $request['DatosEntrega']['Observaciones'] //Opcional - Se puede omitir.
                ),

                //"qwer tyui opas\ndfgh jklñ zxcv\nbnm, qwer tyui\nopas dfgh jklñ\nzxcv bnm, qwer"; //
                // ## DATOS DEL SERVICIO ##
                'DatosServicio' => array(
                    'Fecha' => $hoy,  // Obligatorio. Fecha >= Hoy()
                    'Referencia' => $request['DatosServicio']['Referencia'],  //Obligatorio. ¿numero de pedido/albaran/factura? !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! ORDER_ID
                    'CodigoServicio' => '0205', // Obligatorio. Cada servicio deberá ser activado por la franquicia
                    //0800 = Ecommerce
                    /*****
                    CodigoServicio: código de servicio MRW. Valores posibles:
                    - 0000: Urgente 10
                    - 0005: Urgente Hoy
                    - 0010: Promociones
                    - 0015: Urgente 10 Expedición
                    - 0100: Urgente 12
                    - 0105: Urgente 12 Expedición
                    - 0110: Urgente 14
                    - 0115: Urgente 14 Expedición
                    - 0200: Urgente 19
                    - 0205: Urgente 19 Expedicion
                    - 0220: Urgente 19 Portugal
                    - 0230: Bag 19
                    - 0235: Bag 14
                    - 0300: Economico
                    - 0350: Economico Interinsular
                    - 0370: Marítimo Baleares
                    - 0385: Marítimo Canarias
                    - 0390: Marítimo Interinsular
                    - 0400: Express Documentos
                    - 0450: Express 2 Kilos
                    - 0480: Caja Express 3 Kilos
                    - 0490: Documentos 14
                    - 0800: Ecommerce
                    - 0800 Ecommerce Ultrarrápido (se debe indicar el campo frecuencia)
                    - 810: Ecommerce Canje
                    ***/ 


                    // ## Desglose de Bulto ##
                    'NumeroBultos' =>  $request['DatosServicio']['NumeroBultos'], //Obligatorio. Solo puede haber un bulto por envio en eCommerce
                    'Peso' => $request['DatosServicio']['Peso'], //Obligatorio. Debe ser igual al valor Peso en BultoRequest si se ha usado
                    'Reembolso' => $request['DatosServicio']['Reembolso'], //Opcional - Se puede omitir. (coste adicional)
                    'ImporteReembolso' => $request['DatosServicio']['ImporteReembolso'], //Obligatorio si hay reembolso. Los decimales se indican con , (coma)
                    // ## Notificaciones  ## // Opcional - Se puede omitir todo el nodo y subnodos
                    'Notificaciones' => array(
                        'NotificacionRequest' => array(
                            0 => array(
                                'CanalNotificacion' => '2',    //Canal por el que se enviará la notificación
                                'TipoNotificacion' => '2',    //Tipo de la notificación
                                'MailSMS' => $request['DatosServicio']['Mail']    //Teléfono móvil o dirección email, según CanalNotificacion
                            ),
                            1 => array(
                                'CanalNotificacion' => '1',    //Canal por el que se enviará la notificación
                                'TipoNotificacion' => '4',    //Tipo de la notificación
                                'MailSMS' => $request['DatosServicio']['SMS']    //Teléfono móvil o dirección email, según CanalNotificacion
                            )
                        )
                    ),
                )
            )
        );

        if($request['ModificaDatosEnvio']){
            $params['request']['ModificaDatosEnvio']['NumeroEnvioOriginal']=$request['ModificaDatosEnvio']['NumeroEnvioOriginal'];
        }

        // Cargamos los headers sobre el objeto cliente SOAP
        $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $this->cabeceras);
        $this->clientMRW->__setSoapHeaders($header);
        // Invocamos el metodo TransmEnvio pasandole los parametros del envio
        try {
            $responseCode = $this->clientMRW->TransmEnvio($params);
            if ($responseCode->TransmEnvioResult->Estado == 1) {

                /*
                 *
                 * $responseCode => Nos devuelve un Object.
                 *
                 * $responseCode->TransmEnvioResult->Estado, 
                   $responseCode->TransmEnvioResult->Mensaje, 
                   $responseCode->TransmEnvioResult->NumeroSolicitud, 
                   $responseCode->TransmEnvioResult->NumeroEnvio, 
                   $responseCode->TransmEnvioResult->Url
                 *
                 * Con estos datos podemos introducirlos en BD y luego rescatarlos para obtener el número de envío.
                 *
                 */

            } else {

                // Error, introducir en un log.

            }
        } catch (SoapFault $exception) {
            // Error, mostramos la excepción del SOAP.
        }
           
        $result['params']=print_r($params['request'],true);
        $result['response']=$responseCode;
        return $result; //$responseCode;

    }


    public function getTicket($numero_envio) {
        $result = array();

        $responseCode = '';

        $params = array(
            'request' => array(
                'NumeroEnvio' => $numero_envio,
                'SeparadorNumerosEnvio' => '',
                'FechaInicioEnvio' => '',
                'FechaFinEnvio' => '',
                'TipoEtiquetaEnvio' => '0',
                'ReportTopMargin' => '',
                'ReportLeftMargin' => '',
            )
        );

        $header = new SoapHeader('http://www.mrw.es/', 'AuthInfo', $this->cabeceras);
        $this->clientMRW->__setSoapHeaders($header);

        try {
            $responseCode = $this->clientMRW->EtiquetaEnvio($params);

            /*
             *
             *  $responseCode => Nos devuelve un Object.
             *
             *  $responseCode->GetEtiquetaEnvioResult->EtiquetaFile, Con esto obtenemos la etiqueta en base64.
             *
             */
            $result['base64pdf']=$responseCode->GetEtiquetaEnvioResult->EtiquetaFile;
            $result['msg']='ok';
            $result['error']=0;

        } catch (SoapFault $exception) {

            $result['msg']='No se ha podido obtener la etiqueta';
            $result['error']=1;
        }

        return $result; 

    }


}