<?php


   $result['html'] = '<p>'
                            //. str_replace( array_keys($a), array_values($a), CFG::$vars['shop']['bizum']['message_confirm'])
                            . '<p style="margin:10px 20px;">'. t('YOU_MUST_ACCEPT').' <a id="button-conditions-bizum" style="cursor:pointer;">'.t('SALE_CONDITIONS').'</a> <input style="display:inline;" id="accept" name="accept" type="checkbox" value="0"> '.t('TO_CONTINUE'). '</p>'
                            . '<p style="text-align:center;"><br />' 
                            . '<span style="display:inline;" id="button-cancel-bizum"  class="btn btn-danger">'.t('CANCEL').'</span> &nbsp; '
                            . '<span style="display:inline;" id="button-confirm-bizum" class="btn btn-info">'.t('CONFIRM_PAYMENT_WITH').'bizum</span>'
                            . '</p>'
                            . '<span id="check_conditions" style="display:none;position:absolute;top:155px;left:50px;right:50px;background-color:#f45e74;color:white;padding:20px;">'.t('YOU_MUST_ACCEPT_CONDITIONS_BEFORE_CONTINUE').'.</span>'
                            . '<span id="conditions-bizum-text" class="scroll scroll-v" style="display:none;position:absolute;top:0px;left:0px;right:0px;bottom:0px;overflow:auto;background-color:#f45e74;color:white;padding:20px;">'.$conditions.'<p style="text-align:right;"><a class="btn btn-danger">'.t('BACK').'</a> &nbsp; <a id="link_accept_conditions" class="btn btn-success">'.t('I_AGREE').'</a></p></span>'
                            . '<script type="text/javascript">'
                            . '$("#button-conditions-bizum").click(function(){'
                            . '    $("#conditions-bizum-text").fadeIn().click(function(){$(this).fadeOut();});'
                            . '});'
                            . '$("#link_accept_conditions").click(function(){'
                            . '    $("#accept").attr(\'checked\',\'checked\') ;'
                            . '});'
                            . '$("#button-confirm-bizum").click(function(){'

                            . '});'
                            . '$("#button-cancel-bizum").click(function(){'
                            . '   $("#popup-overlay").slideUp("fast");'
                            . '   $("#popup-modal").slideUp("slow");'
                            . '});' 
                            . '</script>'
                            ;