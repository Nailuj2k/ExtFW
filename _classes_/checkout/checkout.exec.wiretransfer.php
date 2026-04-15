<?php

            $amount = str_pad( strval( intval( $result['total'] * 100) ), 1, '0', STR_PAD_LEFT );
            $conditions =   CFG::$vars['shop']['conditions']; 

            $result['title'] = t('PAY_WITH').' '.t('WIRETRANSFER').' <img style="position:absolute;top:10px;right:10px;height:42px;" src="'.SCRIPT_DIR_IMAGES.'/logos/wiretransfer.png">'; 
            if (CFG::$vars['shop']['wiretransfer']['bank']) $result['title'] = '<img style="max-width:170px;position:absolute;top:15px;left:15px;" src="'.SCRIPT_DIR_IMAGES.'/logos/logo_'.CFG::$vars['shop']['wiretransfer']['bank'].'.png">'.$result['title'] ;

            $a = array();
            $a['[SHOP_NAME]']         = CFG::$vars['shop']['name'];
            $a['[USER_FULLNAME]']     = $result['name'];      // $userdata['user_fullname'];
            $a['[USER_EMAIL]']        = $result['email'];     // $userdata['user_email'];
            $a['[USER_CARD_ID]']      = $result['card_id']; 
            $a['[USER_PHONE]']        = $result['phone'];     // $userdata['user_phone'];
            $a['[PAYMENT_METHOD]']    = $result['provider'];  // $shop[$params['method']]['name'];
            $a['[SHIPPING_ADDRESS]']  = 'online'; //getShippingAddressAsString($data); //$result['address'];   // 
            $a['[AMOUNT]']            = $result['total'];     // $this->totalprice();
            $a['[ORDER_NUM]']         = $result['order'];  //strval($id);
          //$a['[ORDER_LINES]']       = order_lines($row);
            $a['[SHIPPING]']          = 'incluidos';  //$this->shipping();
            $a['[COIN]']              = CFG::$vars['shop']['currency'];     
            $a['[EMAIL]']             = CFG::$vars['shop']['email'];    
            $a['[BANK]']              =  ucfirst(strtolower(CFG::$vars['shop']['wiretransfer']['account']['bank']));  //ucfirst(strtolower($result['bank']));
            $a['[IBAN]']              = '<b id="iban">'. CFG::$vars['shop']['wiretransfer']['account']['iban'].'</b><a onclick="copyToClipboard(\'#iban\')"> <i class="fa fa-copy" title="'.t('COPY_TO_CLIPBOARD').'" style="color:#37e191;"> </i> </a>';    
            $a['[SWIFT]']             =  CFG::$vars['shop']['wiretransfer']['account']['swift'];   
          
            $result['html'] = '<p>'
                            . str_replace( array_keys($a), array_values($a), CFG::$vars['shop']['wiretransfer']['message_confirm'])
                            . '<p style="/*margin:10px 20px;*/">'. t('YOU_MUST_ACCEPT').' <a id="button-conditions-wiretransfer" style="cursor:pointer;">'.t('SALE_CONDITIONS').'</a> <input style="display:inline;" id="accept" name="accept" type="checkbox" value="0"> '.t('TO_CONTINUE'). '</p>'
                            . '<p style="text-align:center;"><br />' 
                            . '<span style="display:inline;" id="button-cancel-wiretransfer"  class="btn btn-danger">'.t('CANCEL').'</span> &nbsp; '
                            . '<span style="display:inline;" id="button-confirm-wiretransfer"  class="btn btn-info disabled">'.t('CONFIRM_PAYMENT_WITH').' '.t('WIRETRANSFER').'</span>'
                            . '</p>'
                            . '<span id="check_conditions" style="display:none;position:absolute;top:155px;left:50px;right:50px;background-color:#f45e74;color:white;padding:20px;">'.t('YOU_MUST_ACCEPT_CONDITIONS_BEFORE_CONTINUE').'.</span>'
                            . '<span id="conditions-wiretransfer-text" class="scroll scroll-v" style="display:none;position:absolute;top:0px;left:0px;right:0px;bottom:0px;overflow:auto;background-color:#f45e74;color:white;padding:20px;">'.$conditions.'<p style="text-align:right;"><a class="btn btn-danger">'.t('BACK').'</a> &nbsp; <a id="link_accept_conditions" class="btn btn-success">'.t('I_AGREE').'</a></p></span>'
                            . '<script type="text/javascript">'
                            . '$("#button-conditions-wiretransfer").click(function(){'
                            . '    $("#conditions-wiretransfer-text").fadeIn().click(function(){$(this).fadeOut();});'
                            . '});'
                            . '$("#link_accept_conditions").click(function(){'
                            . '    document.getElementById(\'accept\').checked = true;'
                            . '    $("#button-confirm-wiretransfer").removeClass(\'disabled\');'
                            . '});'
                            . '$("#accept").click(function(){'
                            . '    var accept = document.getElementById(\'accept\').checked;'
                            . '    if (accept) {'
                            . '        $("#button-confirm-wiretransfer").removeClass(\'disabled\');'
                            . '    }else{'
                            . '        $("#button-confirm-wiretransfer").addClass(\'disabled\');'
                            . '    }'
                            . '});'
                            . '$("#button-confirm-wiretransfer").click(function(){'
                            . '    var accept = ($("#accept").is(":checked")) ;'
                            . '    if (!accept) {'
                            . '        $("#check_conditions").fadeIn().click(function(){$(this).fadeOut();});'
                            . '        check_conditions_timer = setTimeout(function(){ $("#check_conditions").fadeOut();},2000);'
                            . '    }else{'
                            . '        $("#button-confirm-wiretransfer").attr(\'disabled\',\'disabled\');'
                            . '        $(this).addClass(\'disabled\');'
                            . '        $(\'.ajax-loader\').show();' 
                            . '        $.ajax( {url: "/'.$_ARGS[0].'/ajax/exec/wiretransfer/ok", data:{"order": '.$result['order'].',"amount": '.$amount .'},type:"POST",dataType: "json"} ) '
                            . '        .done(function(data) {   '
                            . '            jxCart.empty();     '  // En onclick
                            . '            console.log(data);  '
                            . '            $("#form-checkout-proccess-payment,#popup-dialog #popup-content").html(\'<p><br />'.t('THANKS_FOR_YOUR_ORDER').'<br /><br /><br /></p>\'); '
                            . '            setTimeout(function(){ '."\n".'  $("#popup-overlay").slideUp("fast");'."\n".'  $("#popup-dialog").slideUp("slow");  '."\n".'  },5000); '
                            . '        })'
                            . '        .fail(function()     {     console.log(\'FAIL\');       })'
                            . '        .always(function()   {    $(\'.ajax-loader\').hide();     });'
                            . '    }'
                            . '});'
                            . '$("#button-cancel-wiretransfer").click(function(){'
                            . '   $("#popup-overlay").slideUp("fast");'
                            . '   $("#popup-modal").slideUp("slow");'
                            . '});' 
                            . '</script>'
                            ;
