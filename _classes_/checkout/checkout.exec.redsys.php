<?php


    /*****
    https://pagosonline.redsys.es/conexion-insite.html

    ****/
    include SCRIPT_DIR_LIB.'/redsys/apiRedsys.php';
    $type  = 'hidden';  // text para pruebas, hidden para produccción
    $miObj = new RedsysAPI;
    $id    =  $result['order'];   //$result['token'];  // time();  
    $amount = str_pad( strval( intval( $result['total'] * 100) ), 1, '0', STR_PAD_LEFT );
    $miObj->setParameter("DS_MERCHANT_AMOUNT"      , $amount);
    $miObj->setParameter("DS_MERCHANT_ORDER"       , strval($id));

    $redsys_code = CFG::$vars['shop']['redsys']['code'];  
    $miObj->setParameter("DS_MERCHANT_MERCHANTCODE", $redsys_code); // "999008881"               //SANTANDER

    if($result['provider'] == 'bizum'){  //147.84.199.32
       $miObj->setParameter("DS_MERCHANT_PAYMETHODS","z");
       //Ds_Merchant_Bizum_MobileNumber +34700000000
    }

    $miObj->setParameter("DS_MERCHANT_CURRENCY"    , CFG::$vars['shop']['redsys']['currency'] );    // "978"
    $miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE","0");
    $miObj->setParameter("DS_MERCHANT_TERMINAL"    , "001");                                // "871";
    $miObj->setParameter("DS_MERCHANT_MERCHANTURL" , CFG::$vars['shop']['url'].'/'.$_ARGS[0].'/checkout/redsys/callback/raw/'.$_SESSION['lang']);
    $miObj->setParameter("DS_MERCHANT_URLOK"       , CFG::$vars['shop']['url'].'/'.$_ARGS[0].'/checkout/redsys/ok/');		
    $miObj->setParameter("DS_MERCHANT_URLKO"       , CFG::$vars['shop']['url'].'/'.$_ARGS[0].'/checkout/redsys/ko/');
    $result['version']   ="HMAC_SHA256_V1";
    $result['params']    = $miObj->createMerchantParameters();

    $redsys_clave = CFG::$vars['shop']['redsys']['clave'];  
    $result['signature'] = $miObj->createMerchantSignature($redsys_clave);  // 'sq7HjrUOBfKmC576ILgskD5srU870gJ7'        //SANTANDER

    $a = array();
    $a['[SHOP_NAME]']         = CFG::$vars['shop']['name'];
    $a['[USER_FULLNAME]']     = $result['name'];      // $userdata['user_fullname'];
    $a['[USER_EMAIL]']        = $result['email'];     // $userdata['user_email'];
    $a['[USER_CARD_ID]']      = $result['card_id']; 
    $a['[USER_PHONE]']        = $result['phone'];     // $userdata['user_phone'];
    $a['[PAYMENT_METHOD]']    = $result['provider'];  // $shop[$params['method']]['name'];
    $a['[SHIPPING_ADDRESS]']  = 'online'; //getShippingAddressAsString($data); //$result['address'];   // 
    $a['[AMOUNT]']            = $result['total'];     // $this->totalprice();
    $a['[ORDER_NUM]']         = strval($id);
  //$a['[ORDER_LINES]']       = order_lines($row);
    $a['[SHIPPING]']          = $result['shipping'];  //'incluidos';  //$this->shipping();
    $a['[COIN]']              = CFG::$vars['shop']['currency'];     
    $a['[EMAIL]']             = CFG::$vars['shop']['email'];    
  //$a['[BANK]']             =  ucfirst(strtolower($result['bank']));  //t(strtoupper($result['bank']));
  //$a['[ORDER_LINES]']       = $this->get_order_lines($params);

    $conditions =  CFG::$vars['shop']['conditions']; //Table::getFieldsValues("SELECT item_text FROM CLI_PAGES WHERE item_name = 'condiciones'");  

    $result['title'] = t('PAY_WITH').' Redsys<img style="position:absolute;top:15px;right:20px;max-width: 110px;" src="'.SCRIPT_DIR_IMAGES.'/logos/redsys.png">';    // //SANTANDER
    if (CFG::$vars['shop']['redsys']['bank']) $result['title'] = '<img style="max-width:180px;position:absolute;top:15px;left:15px;" src="'.SCRIPT_DIR_IMAGES.'/logos/logo_'.CFG::$vars['shop']['redsys']['bank'].'.png">'.$result['title'] ;

    $redsys_url_tpvv =  CFG::$vars['shop']['redsys']['url_tpvv'];  //$result['bank'] ? CFG::$vars['shop']['redsys']['url_tpvv_'.$result['bank']] : CFG::$vars['shop']['redsys']['url_tpvv'];  //SANTANDER
    $result['html'] = str_replace( array_keys($a), array_values($a), CFG::$vars['shop']['redsys']['message_confirm'])
                  //. 'Ha elegido pagar con Redsys. Pulse confirmar una vez haya aceptado las condiciones de venta y será redirigido a la pasarela de pagos Redsys, en donde podrá efectuar el pago en modo seguro con su tarjeta bancaria. '
                  //. 'El importe total es de <strong>'.$result['total'].' '.CFG::$vars['shop']['currency'].'</strong> con impuestos y gastos de envío incluidos.'
                  //. '</p>'
                    . '<p style="margin:10px 20px;">'. t('YOU_MUST_ACCEPT').' <a id="button-conditions-redsys" style="cursor:pointer;">'.t('SALE_CONDITIONS').'</a> <input style="display:inline;" id="accept" name="accept" type="checkbox" value="0"> '.t('TO_CONTINUE'). '</p>'
                  //. '<p>pedido :'.strval($id).'</p>'
                    . '<form action="'.$redsys_url_tpvv.'" method="POST" id="redsys_form" name="redsys_form" style="margin-top:0px !important;text-align:left;">'  //SANTANDER
                    . '<!-- Ds_Merchant_SignatureVersion --><input   type="'.$type.'" style="display:block;" name="Ds_SignatureVersion"   value="'.$result['version'].'"/>'
                    . '<!-- Ds_Merchant_MerchantParameters --><input type="'.$type.'" style="display:block;" name="Ds_MerchantParameters" value="'.$result['params'] .'"/>'
                    . '<!-- Ds_Merchant_Signature --><input type="'.$type.'" style="display:block;" name="Ds_Signature" value="'.$result['signature'].'"/>'
                    . '</form>'
                    . '<p style="text-align:center;"><br />' 
                    . '<span style="display:inline;" id="button-cancel-redsys"  class="btn btn-danger">'.t('CANCEL').'</span> &nbsp; '
    ////////////    . '<span style="display:inline;" id="button-confirm-redsys" class="btn btn-info">'.t('CONFIRM_PAYMENT_WITH').' Redsys</span>'
                    . '</p>'
                    . '<span id="check_conditions" style="display:none;position:absolute;top:155px;left:50px;right:50px;background-color:#f45e74;color:white;padding:20px;">Debe aceptar las condiciones de la venta para proseguir con el pedido.</span>'
                    . '<span id="conditions-redsys-text" class="scroll scroll-v" style="display:none;position:absolute;top:0px;left:0px;right:0px;bottom:0px;overflow:auto;background-color:#f45e74;color:white;padding:20px;">'.$conditions.'<p style="text-align:right;"><a class="btn btn-danger">Volver</a> &nbsp; <a id="link_accept_conditions" class="btn btn-success">Acepto</a></p></span>'
                    . '<script type="text/javascript">'
                    . '$("#button-conditions-redsys").click(function(){'
                    . '    $("#conditions-redsys-text").fadeIn().click(function(){$(this).fadeOut();});'
                    . '});'
                    //. '$("#link_accept_conditions").click(function(){'
                    //. '    $("#accept").attr(\'checked\',\'checked\') ;'
                    //. '    $("#button-confirm-redsys").removeClass(\'disabled\');'
                    //. '});'
                    . '$("#link_accept_conditions").click(function(){'
                    . '    $("#accept").attr(\'checked\',true) ;'
                    . '    $("#button-confirm-redsys").removeClass(\'disabled\');'
                    . '});'
                    . '$("#accept").click(function(){'
                    . '    var accept = ($("#accept").is(":checked")) ;'
                    . '    if (accept) {'
                    . '        $("#button-confirm-redsys").removeClass(\'disabled\');'
                    . '    }else{'
                    . '        $("#button-confirm-redsys").addClass(\'disabled\');'
                    . '    }'
                    . '});'
                    . '$("#button-confirm-redsys").click(function(){'
                    . '    $(this).addClass(\'disabled\');'
                    . '    var accept = ($("#accept").is(":checked")) ;'
                    . '    if (!accept) {'
                    . '        $("#check_conditions").fadeIn().click(function(){$(this).fadeOut();});'
                    . '        check_conditions_timer = setTimeout(function(){ $("#check_conditions").fadeOut();},2000);'
                    . '    }else{'
                    . '        $("#button-confirm-redsys").attr(\'disabled\',\'disabled\');'
                    . '        $("#redsys_form").submit();'  //  FIX compare total in redsys form with total in summary & cancel submit
                    . '    }'
                    . '});'                 
                    . '$("#button-cancel-redsys").click(function(){'
                    . '    $("#popup-overlay").slideUp("fast");'
                    . '    $("#popup-modal").slideUp("slow");'
                    . '});' 
                    . '</script>'
                    ;
