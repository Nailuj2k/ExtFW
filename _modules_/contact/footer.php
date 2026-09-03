<?php 
if(CFG::$vars['modules']['contact']['bum']) include(SCRIPT_DIR_MODULE.'/bum.php'); else echo '<script type="text/javascript">var bum_effect = false;</script>';
?>

<!--<script type="text/javascript" src="<?=SCRIPT_DIR_MODULE?>/script.js?ver=1.2.5"></script>-->
<script type="text/javascript">



        //$(document).ready(function() { 
            $('#<?=($_SESSION['userid'])?'notas':'nombre'?>').focus();
            
            <?php
            if(CFG::$vars['captcha']['enabled'] && CFG::$vars['captcha']['google_v3']['enabled']) { 
            } else if(CFG::$vars['captcha']['enabled']){ 
            ?>

            $('#captcha').closest('div')
                         .css('position','relative')
                         .append('<a id="captcha-reload" style="position:absolute;right:5px;"> <?=t('RELOAD_CAPTCHA','Cambiar captcha')?> <i class="fa fa-refresh"></i> </a>');
            $('#captcha-reload').click(function(){
                                $('#captcha-reload i').addClass('fa-spin');
                              //$('#captcha-label span').html('<?=t('RELOADING')?>').css('color','#e05687');
                                $('#error').hide('fast');
                                $('#captcha').css('outline','none');
                                var this_form = $(this);
                                $.ajax({
                                   method: "POST",
                                   url: "<?=MODULE?>/ajax/captcha", //?op=download",
                                   data: { op: 'reload','value':$('#captcha').val()},
                                   dataType: "json",
                                   beforeSend: function( xhr, settings ) { }
                                }).done(function( data ) {
                                   if(data.error==0){
                                       $('#captcha').val('');//css('outline','1px solid red').highlight();
                                     //$('#captcha-label span').html(data.captcha.label).highlight();
                                       $('#captcha').attr('placeholder',data.captcha.label).highlight();
                                   }else{
                                   }
                                }).fail(function() {
                                   $('#error').html(responseText.msg).show('fast');
                                }).always(function(data) {
                                   $('#captcha-reload i').removeClass('fa-spin');
                                });
                           });
            <?php } ?>
       // });
</script>










            <script type="text/javascript">
                $(document).ready(function(){
                    $('#data-protect-info #btn-show-lpd').click(function(){  $('#data-protect-info .hidden').toggle('fast'); });
                });
            </script>