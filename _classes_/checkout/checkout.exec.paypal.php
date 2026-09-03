<?php


   $result['html'] = '<p>'
                            //. str_replace( array_keys($a), array_values($a), CFG::$vars['shop']['paypal']['message_confirm'])
                            . '<p style="margin:10px 20px;">'. t('YOU_MUST_ACCEPT').' <a id="button-conditions-paypal" style="cursor:pointer;">'.t('SALE_CONDITIONS').'</a> <input style="display:inline;" id="accept" name="accept" type="checkbox" value="0"> '.t('TO_CONTINUE'). '</p>'
                            . '<p style="text-align:center;"><br />' 
                            . '<span style="display:inline;" id="button-cancel-paypal"  class="btn btn-danger">'.t('CANCEL').'</span> &nbsp; '
                            . '<span style="display:inline;" id="button-confirm-paypal" class="btn btn-info">'.t('CONFIRM_PAYMENT_WITH').'Paypal</span>'
                            . '</p>'
                            . '<span id="check_conditions" style="display:none;position:absolute;top:155px;left:50px;right:50px;background-color:#f45e74;color:white;padding:20px;">'.t('YOU_MUST_ACCEPT_CONDITIONS_BEFORE_CONTINUE').'.</span>'
                            . '<span id="conditions-paypal-text" class="scroll scroll-v" style="display:none;position:absolute;top:0px;left:0px;right:0px;bottom:0px;overflow:auto;background-color:#f45e74;color:white;padding:20px;">'.$conditions.'<p style="text-align:right;"><a class="btn btn-danger">'.t('BACK').'</a> &nbsp; <a id="link_accept_conditions" class="btn btn-success">'.t('I_AGREE').'</a></p></span>'
                            . '<script type="text/javascript">'
                            . '$("#button-conditions-paypal").click(function(){'
                            . '    $("#conditions-paypal-text").fadeIn().click(function(){$(this).fadeOut();});'
                            . '});'
                            . '$("#link_accept_conditions").click(function(){'
                            . '    $("#accept").attr(\'checked\',\'checked\') ;'
                            . '});'
                            . '$("#button-confirm-paypal").click(function(){'

                            . '});'
                            . '$("#button-cancel-paypal").click(function(){'
                            . '   $("#popup-overlay").slideUp("fast");'
                            . '   $("#popup-modal").slideUp("slow");'
                            . '});' 
                            . '</script>'
                            ;