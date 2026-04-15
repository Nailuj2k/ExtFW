<?php 

    if (($_ARGS[1]=='register'||$_ARGS['op']=='register'||$reg=='ko') && (!$_SESSION['auth_provider']) &&  CFG::$vars['auth']!='ldap' ) {  

        ?>
        <script type="text/javascript">

            $(document).ready(function(){
        
                var url_check_email   = 'control_panel/ajax/op=method/table=<?=TB_USER?>/method=validate/field=user_email';
                var url_check_card_id = 'control_panel/ajax/op=method/table=<?=TB_USER?>/method=validate/field=user_card_id';
                var url_check_register_code = '<?=CFG::$vars['login']['register_code']['url']?>';
                var input_fullname = $("#user_fullname");
                var input_fullname = $("#user_fullname");
                var input_email   = $("#user_email");
                var input_p1 = $("#password");
                var input_p2 = $("#password2");
                var user_email_ok, user_card_id_ok, password_ok, register_code_ok, invitation_code_ok = false;

                <?php  if(CFG::$vars['login']['card_id']['required']) {?>
                var input_card_id = $("#user_card_id");
                <?php }else{?>
                var input_card_id=false;
                <?php }?>

                <?php  if(CFG::$vars['login']['register_code']['required']) {?>
                var input_register_code = $("#register_code");
                var input_register_code_key = $("#register_code_key");
                var register_code_key = 0;
                <?php }else{?>
                var input_register_code=false;
                <?php }?>



                $('#btn-register-passwordless').on('click', async function(e){
                    console.log('REGISTER PASSWORDLESS CLICKED');
                    e.preventDefault();
                    const email = document.querySelector('#passwordless-email')?.value;
                    if (email && typeof PasswordlessRegistration !== 'undefined') {
                        PasswordlessRegistration.register(email);
                    }
                });

                // Handler para registro con Nostr — muestra opciones
                $('#btn-nostr-register').on('click', function(e){
                    e.preventDefault();
                    var invInput = document.getElementById('shared-invitation-code');
                    window._nostr_invitation_code = invInput ? invInput.value.trim() : '';
                    nostrRegisterFlow($(this));
                });

                function valid_data() {
                    <?php  if(CFG::$vars['login']['username']['required']!=true) {?> 
                        user_card_id_ok=true;
                    <?php }?>
                    <?php  if(CFG::$vars['login']['register_code']['required']!=true) {?>
                        register_code_ok=true;
                    <?php }?>
                    <?php  if(!Invitation::isRequired()) {?>
                        invitation_code_ok=true;
                    <?php }?>
                    console.log(register_code_ok,' && ',user_card_id_ok ,'&&--', user_email_ok ,'--&&', password_ok  ,'&& (',input_p1.val(),'==',input_p2.val()  ,') &&',$('#conditions').prop('checked') ,'&&', valid_fullname() )
                    if (invitation_code_ok && register_code_ok && user_card_id_ok && user_email_ok  && password_ok && (input_p1.val()==input_p2.val()) && $('#conditions').prop('checked') && valid_fullname() ){
                        $('#btnsubmit').removeClass('disabled').removeAttr('disabled');
                    }else{
                        $('#btnsubmit').addClass('disabled').attr('disabled', '');
                    }
                }
                input_email.change(function() { 
                    var user_email = $(this).val();
                    user_email_ok = false;
                    input_email.removeClass('pop-outin valid_input')
                    //showmessage(input_email,'info','...');
                    if(user_email){
                        console.log(url_check_email,{"value":user_email});
                        $.post(url_check_email,{"value":user_email},function(data, textStatus, jqXHR){  
                        console.log(data);
                        if(data.valid<1){
                            showmessage(input_email,'error',data.msg);
                        }else if(data.field>0){
                            showmessage(input_email,'error','Nombre de usuario o email ocupado');
                        }else{
                            user_email_ok = true;
                            input_email.addClass('pop-outin valid_input')
                            showmessage(input_email,'success','Nombre de usuario o email válido');
                        }
                        valid_data();
                        },'json');
                    }else{
                        //showmessage(input_email,'info','Email obligatorio');
                    }
                });
        
                <?php  if(CFG::$vars['login']['card_id']['required']) {?>
                input_card_id.change(function() { 
                    var user_card_id = $(this).val();
                    user_card_id_ok = false;
                    // showmessage(input_card_id,'info','...');
                    if(user_card_id){
                        console.log(url_check_card_id+'/value='+user_card_id);
                        $.post(url_check_card_id,{"value":user_card_id},function(data, textStatus, jqXHR){  
                            console.log(data);
                        if(data.valid<1){
                            showmessage(input_card_id,'error',data.msg);
                        }else if(data.field>0){
                            showmessage(input_card_id,'error','Ya hay un usuario con este DNI');
                        }else{
                            user_card_id_ok = true;
                            showmessage(input_card_id,'success','DNI correcto');
                        }
                        valid_data();
                        },'json');
                    }else{
                        //showmessage(input_card_id,'info','Identificador obligatorio');
                    }
                });
                <?php }?>
                
                <?php  if(CFG::$vars['login']['register_code']['required']) {?>
                input_register_code.change(function() { 
                    var _code = $(this).val();
                    register_code_ok = false;
                    if(_code){
                        console.log(url_check_register_code+'/value='+_code);
                        $.post(url_check_register_code,{"value":_code},function(data, textStatus, jqXHR){  
                        console.log(data);
                        if(data.error!=0){
                            showmessage(input_register_code,'error',data.msg);
                            input_register_code_key.val('0');
                        }else if(data.error==0&&data.key){
                            register_code_ok = true;
                            register_code_key=data.key;
                            input_register_code_key.val(data.key);
                            showmessage(input_register_code,'success',data.msg);
                        }
                        valid_data();
                        },'json');
                    }else{
                        //showmessage(input_register_code,'info','Identificador obligatorio');
                    }
                });
                <?php }?>

                <?php if(Invitation::isRequired()) {?>
                $('#shared-invitation-code').on('change blur', function() {
                    var _code = $(this).val().trim();
                    $('#invitation_code_hidden').val(_code);
                    invitation_code_ok = false;
                    if(_code){
                        fetch('login/ajax/op=validate_invitation',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'code='+encodeURIComponent(_code)}).then(function(r){return r.json()}).then(function(data){
                            if(data.error){
                                showmessage($('#shared-invitation-code'),'error',data.msg || 'Código no válido');
                            }else{
                                invitation_code_ok = true;
                                showmessage($('#shared-invitation-code'),'success',data.msg || 'Código válido');
                            }
                            valid_data();
                        });
                    }else{
                        showmessage($('#shared-invitation-code'),'error','Código de invitación requerido');
                        valid_data();
                    }
                });
                <?php }?>

                input_fullname.change(function() {
                    //input_fullname.removeClass('pop-outin valid_input')
                    console.log('input_fullname.change');
                    //var user_input_fullname = $(this).val();
                    if(valid_fullname()){
                        console.log('input_fullname.change OK');
                        //input_fullname.addClass('pop-outin valid_input');
                        showmessage(input_fullname,'success','Nombre correcto');
                    }else{
                        console.log('input_fullname.change KO');
                        showmessage(input_fullname,'error','Nombre no válido');
                    }
                });
                
                input_p1.change(function() { 
                    console.log('input_p1.change');
                    //input_p1.removeClass('pop-outin valid_input')
                    password_ok = $(this).attr('strength')>=<?=CFG::$vars['login']['password']['strength']?>;
                    console.log('STRENGTH',$(this).attr('strength'),'>=',<?=CFG::$vars['login']['password']['strength']?>);
                    if(password_ok){
                        //input_p1.addClass('pop-outin valid_input')
                        showmessage(input_p1,'success','Contraseña válida');
                    }else{
                        showmessage(input_p1,'error','No se admiten contraseñas débiles.');
                    }
                });
                /** 
                input_p2.keyup(function() { 
                    password2_ok = input_p1.val() == input_p2.val();
                    if(password2_ok){
                        input_p2.addClass('pop-outin valid_input')
                        //showmessage(input_p2,'success','Contraseña válida');
                    }else{
                        input_p2.removeClass('pop-outin valid_input')
                        //showmessage(input_p2,'error','No se admiten contraseñas débiles.');
                    }
                });
                **/
                
                input_email.change();
                <?php  if(CFG::$vars['login']['card_id']['required']) {?>
                input_card_id.change();
                <?php }?>
        
                $('.text-after').click(function(){
                    $(this).hide('slow');
                });

                function showmessage(e,type,msg){
                    var t = e.parent().find('.text-after');
                    t.show('fast').attr('class', 'text-after').addClass(type);
                    if     (type=='success'){
                        e.css('background-color','#dbeeff');
                        t.html(' '+msg+' <i class="fa fa-check" style="color:#00e409;"></i>');
                    }else if(type='error'){
                        t.html(' '+msg+' <i class="fa fa-warning" style="color:#ff0000;"></i>');
                        //e.focus();
                        e.css('background-color','#f99999 !important');
                    }else if(type='info'){
                        e.css('background-color','#f2f0f0');
                        t.html(' '+msg+' <i class="fa fa-warning" style="color:#a9aba7;"></i>');
                    }
                    setTimeout(function(){t.hide('slow')},5000);
                }
        
        
                //$('#pwdMeter').css('width',$("#password").outerWidth());
                $("#password").pwdMeter({'displayText':false,'passwordBox':'#password','meterBox':'#pwdMeter'});
        
                $("#password").on("keyup",function(){
                    if($(this).val())  $(".fa-eye").show(); //.css('left', $("#password").outerWidth()-24 );
                                  else $(".fa-eye").hide();
                    console.log('STRENGTH:', $(this).attr('strength') );
                    password_ok = $(this).attr('strength')>=<?=CFG::$vars['login']['password']['strength']?>;
                    valid_data();
                });

                $("#conditions").on("change",function(){
                    valid_data();
                });
                $("##password2").on("keyup",function(){
                    valid_data();
                });
                $("#user_fullname").on("keyup",function(){
                    valid_data();
                });
                
                function valid_fullname(){
                 // var reg = /^[ÁÉÍÓÚÑA-Z][a-zñáéíóú]+(\s+[ÁÉÍÓÚÑA-Z]?[a-zñáéíóú]+)*$/;
                    var reg = /^[ÁÉÍÓÚÑA-Za-zñáéíóú]+(\s+[ÁÉÍÓÚÑA-Za-zñáéíóú]+)*$/;
                    return reg.test($('#user_fullname').val().trim());
                }
                
                $(".fa-eye").mousedown(function(){
                    $("#password,#password2").attr('type','text');
                }).mouseup(function(){
                    $("#password,#password2").attr('type','password');
                }).mouseout(function(){
                    $("#password,#password2").attr('type','password');
                });
                
                function getRandomPassword() { 
                    console.log('getRandomPassword');
                    $.ajax({
                        method: "POST",
                        url: "<?=Vars::mkUrl(MODULE,'ajax')?>",
                        data: { op: 'randompassword', strength: <?=CFG::$vars['login']['password']['strength']?>, length: 10 },
                        dataType: "json",
                        beforeSend: function( xhr, settings ) {
                            $('#password').html('calculando..'); 
                            $('#ajaxmonitor').html('<i class="fa fa-refresh  fa-spin "></i>'); //'<img src="<?=$imagespath?>gears.gif"><span></span>');
                        }
                    }).done(function( data ) {
                        $('#password').val(data.randompassword);
                        $('#password2').val(data.randompassword);
                        $(".fa-eye").show().css('left', $("#password").outerWidth()-24 );
                        $("#password,#password2").attr('type','text');
                        //evaluateMeter();
                        $('#password').keyup();
                    }).fail(function() {
                        showMessageError( "error" );
                    }).always(function() {
                        //alert( "complete" );
                    $('#ajaxmonitor').html('<i class="fa fa-refresh "></i>'); //'<img src="<?=$imagespath?>gears.png">');
                    });
                }

             // $('body').on('dblclick','#password,#password2',function(){ getRandomPassword(); });

                $('body').on('click touch','#get-random-password,#ajaxmonitor .fa',function(){ getRandomPassword(); });
                
            
                input_fullname.focus();
             // getRandomPassword();
        
                //======fix for autocomplete
                /*
                $('input, :input').attr('readonly',true);//readonly all inputs on page load, prevent autofilling on pageload       
                $('input, :input').on( 'click focus', function(){ //on input click
                $('input, :input').attr('readonly',true);//make other fields readonly
                $( this ).attr('readonly',false);//but make this field Not readonly
                });
                */
                //======./fix for autocomplete
        
            });
        
        </script>
        
   
        <?php
    } else if ($_SESSION['valid_user'] && ($_ARGS[1]=='changepassword'||$_ARGS['op']=='changepassword'))     {  
        ?>
        <script type="text/javascript">
            /////////////////changepassword
            $(document).ready(function(){
                
                $("#newpassword").pwdMeter({'displayText':false});
                
                $("#newpassword").on("keyup",function(){
                    if($(this).val())  $(".fa-eye").show().css('left', $("#newpassword").outerWidth()-44 );
                                else $(".fa-eye").hide();
                });
                $(".fa-eye").mousedown(function(){
                    $("#newpassword,#confirmpassword").attr('type','text');
                }).mouseup(function(){
                    $("#newpassword,#confirmpassword").attr('type','password');
                }).mouseout(function(){
                    $("#newpassword,#confirmpassword").attr('type','password');
                });

                function getRandomPassword() { 
                    console.log('getRandomPassword');
                    $.ajax({
                        method: "POST",
                        url: "<?=Vars::mkUrl(MODULE,'ajax')?>",
                        data: { op: 'randompassword', strength: 8, length: 12 },
                        dataType: "json",
                        beforeSend: function( xhr, settings ) {
                            $('#newpassword').html('calculando..'); 
                            $('#ajaxmonitor').html('<i class="fa fa-refresh  fa-spin "></i>'); //'<img src="<?=$imagespath?>gears.gif"><span></span>');
                        }
                    }).done(function( data ) {
                    $('#newpassword').val(data.randompassword);
                    $('#confirmpassword').val(data.randompassword);
                    $(".fa-eye").show().css('left', $("#newpassword").outerWidth()-24 );
                    //$("#newpassword,#confirmpassword").attr('type','text');
                    //evaluateMeter();
                        $('#newpassword').keyup();
                    }).fail(function() {
                        showMessageError( "error" );
                    }).always(function() {
                        //alert( "complete" );
                    $('#ajaxmonitor').html('<i class="fa fa-refresh"></i>'); //'<img src="<?=$imagespath?>gears.png">');
                    });
            }
            $('body').on('dblclick','#newpassword,#confirmpassword',function(){ getRandomPassword(); });
            $('body').on('click touch','#ajaxmonitor .fa',function(){ getRandomPassword(); });

            });
            /////////////////changepassword
            </script>
        <?php

    } else if ($_SESSION['valid_user'] && $_ARGS[1]=='profile'){  
        ?>
        <script>
            $(document).ready(function() { 

                const __USER_ID__ = <?=$_SESSION['userid']?>;
      
                $('#send_message_to_site').click(function(){
                    var message=$('#message_body').val();
                    var title=$('#message_subject').val();
                    var id_user=__USER_ID__;
                    //var el=$(this).closest('div');
                    if(!message) return false;
                    console.log(id_user,title,message);
                    //el.html('<h3><br />Enviando mensaje ...</h3>');
                    $('.ajax-loader').show();
                    $.post('control_panel/ajax/op=method/method=sendmessage/table=CLI_USER',{"from":id_user,"title":title,"message":message},function(data, textStatus, jqXHR){  
                        //console.log(data);
                        //el.html('<h4><br />'+data.msg+'</h4>');
                        show_info('#ftab-tab_customer_messages',data.msg,10000,function(e){e.animate({'top':'+=100'});});

                        $('.ajax-loader').hide();
                        //setTimeout( function(){ $('#LOG_EVENTS .reload').click(); }, 200);
                        setTimeout(function(){get_last_messages(id_user,true);},600);
                    },'json');
                });

                function get_last_messages(id,highlight){
                    $.post('control_panel/ajax/op=method/method=list_messages/table=CLI_USER',{"id":id,'target':'profile'},function(data, textStatus, jqXHR){  
                        $('#list-messages').html(data.msg);
                        setTimeout(function(){if(highlight) $('#list-messages').find('tbody tr:first-child').css('background-color','#ffff99').highlight();},300);
                    },'json');
                }

                $('body').on('click','.message-reply',function(){
                    $('.ajax-loader').show();
                    let tr = $(this).closest('tr');
                    let id = tr.data('user');
                    $.post('control_panel/ajax/op=getfield/table=CLI_USER/field=user_fullname/key=user_id/value='+id,function(data, textStatus, jqXHR){  
                        $('#message_subject').val( 'Re: '+tr.find('.message-subject').html() );
                        $('#message_body').val( '  > '+data.field+' escribió: \n  > '+tr.find('.message-body').html()+'\n  > \n' ).focus(); 
                        $('.ajax-loader').hide();                  
                    },'json');
                })

                $('body').on('click','.message-view',function(){
                    $('.ajax-loader').show();
                    let tr = $(this).closest('tr');
                    // console.log(tr.data('user'));
                    var url_get_msg   = 'control_panel/ajax/op=getfield/table=LOG_EVENTS/field=MESSAGE/key=ID/value='+tr.data('id');
                    //console.log(url_get_msg);
                    let subject = tr.find('.message-subject').html()
                    $.post(url_get_msg,function(data, textStatus, jqXHR){  
                        console.log(data.field);
                        show_info('#list-messages','<b>'+subject+'</b><br>'+data.field,10000,function(e){e.animate({'top':'+=10'});});
                        $('.ajax-loader').hide();
                    },'json');
                // $('#message_subject').val( 'Re: '+tr.find('.message-subject').html() );
                // $('#message_body').val( '  > < ?=$fila['user_fullname']? > escribió: \n  > '+tr.find('.message-body').html()+'\n  > \n' ).focus();                   
                })

                function update_btn(){
                    if ($('#message_body').val()=='') $('#send_message_to_site').addClass('disabled'); else $('#send_message_to_site').removeClass('disabled'); 
                }

                $('#message_body').change(function(){ update_btn(); }).keyup(function(){ update_btn(); });

                $('#btn_last_messages').click(function(){
                    get_last_messages(__USER_ID__,true);
                });

                get_last_messages(__USER_ID__,true)

                update_btn();

            });

            function getCookies() {
                let output = [];
                document.cookie.split(/\s*;\s*/).forEach((pair) => {
                    var name = decodeURIComponent(pair.substring(0, pair.indexOf('=')));
                    var value = decodeURIComponent(pair.substring(pair.indexOf('=') + 1));
                    output.push({ key: name, val: value });
                });
                return output;
            }

            function deleteCookies() {
                var object = getCookies();
                for (var property in object) { deleteCookie(object[property].key); }
                $('#list_cookies').click();
            }

            $('#list_cookies').click(function(){
                var object = getCookies();
                $('#cookies-list').empty();
                for (var property in object) { $('#cookies-list').append( property.padStart(3) + ': ' + (object[property].key).padStart(25)+' : '+object[property].val+'<br>' ); }
                $('#cookies-list').append(                                     '3'.padStart(3) + ': ' +'token'.padStart(25)                +' : '+_TOKEN_+'<br>' ); 
            });                  

            $('#delete_cookies').click(function(){ deleteCookies(); });   

            $(document).ready(function(){ $('#list_cookies').click(); });
            
            var module_name='login';

            function OnUploadSuccessCallback(src, imageId){
                console.log('OnUploadSuccessCallback',src,imageId);
                document.body.querySelectorAll(`img[src='./${src}']`).forEach(img => img.src = src)
            }

            function loadUserData(){
                $('#customer-profile').load('/control_panel/ajax/module=control_panel/op=view/table=<?=TB_USER?>/id=<?=$_SESSION['userid']?>', function() {  
                    $('.buttons').html('<p style="text-align:right;"><a id="btn-edit-user"><i class="fa fa-edit"></i> Editar</a></p>');
                    $('#btn-edit-user').click(function(){
                        var url = '/control_panel/ajax/op=edit/table=<?=TB_USER?>/id=<?=$_SESSION['userid']?>/target=profile';
                        $.modalform({ 'title' : 'Editar', 'url': url  });
                    });
                    ImageEditor.editable_images('.editable-image','/control_panel/ajax/op=function/function=imagereceive/table=<?=TB_USER?>/id=<?=$_SESSION['userid']?>',OnUploadSuccessCallback);         
   
                });
            }
            
            function onChange(){}

            $( document ).ready(function() {    

                loadUserData();
                $('#ftab-tab_customer_account').find('.tab-content').css('overflow','auto');

                setTimeout(function() {
                    const galleryUserFiles = document.querySelectorAll('[rel="g2"]');
                },3000);

                <?php if($_ARGS[2]=='addresses'){?>
                    $('a[href=\'#ftab-tab_customer_addresses\']').click();
                <?php }?>

                // ========== INICIALIZAR UI DE NOSTR SEGÚN ESTADO DE INDEXEDDB ==========

                <?php if (CFG::$vars['login']['nostr']['enabled']) { ?>

                    (async function initNostrUI() {
                        const container = document.getElementById('nostr-link-container');
                        if (!container) return; // Solo ejecutar si estamos en la sección de no vinculado

                        try {
                            // Comprobar si hay claves en IndexedDB
                            const localKeys = await getNostrKeys();

                            if (localKeys && localKeys.npub) {
                                // ESTADO: Tiene claves en IndexedDB
                                container.innerHTML = `
                                    <div style="background:#e8f5e9;border:1px solid #81c784;border-radius:8px;padding:12px;margin-bottom:10px;">
                                        <p style="margin:0 0 8px 0;color:#2e7d32;font-weight:bold;">
                                            <i class="fa fa-check-circle"></i> Ya tienes una identidad Nostr en este navegador
                                        </p>
                                        <p style="margin:0 0 5px 0;font-size:0.85em;color:#666;">Vincula esta identidad a tu cuenta:</p>
                                    </div>

                                    <div style="margin-bottom:10px;">
                                        <input type="text" id="manual-npub-input" readonly
                                            value="${localKeys.npub}"
                                            style="width:100%;padding:8px;font-family:monospace;font-size:0.75em;border:2px solid #81c784;border-radius:4px;margin-bottom:5px;background:#f1f8f4;">
                                        <button id="btn-link-manual-npub" class="btn btn-primary" style="background:#4caf50;border-color:#4caf50;width:100%;">
                                            <i class="fa fa-link"></i> Vincular esta identidad a mi cuenta
                                        </button>
                                        <p style="margin:5px 0 0 0;font-size:0.75em;color:#666;text-align:center;">
                                            <i class="fa fa-shield"></i> Se verificará con firma criptográfica
                                        </p>
                                    </div>

                                    <details style="margin-top:15px;">
                                        <summary style="cursor:pointer;color:#666;font-size:0.9em;margin-bottom:10px;">
                                            <i class="fa fa-exclamation-triangle"></i> ¿Quieres usar una identidad diferente?
                                        </summary>
                                        <div style="padding:10px;background:#fff3e0;border:1px solid #ffb74d;border-radius:8px;margin-bottom:10px;">
                                            <p style="margin:0 0 10px 0;font-size:0.85em;color:#e65100;">
                                                <strong>⚠️ Advertencia:</strong> Esto sobrescribirá tu identidad actual guardada en este navegador.
                                            </p>
                                            <div style="">
                                                <button id="btn-create-nostr-identity-profile" class="btn btn-sm btn-success" style="">
                                                    <i class="fa fa-magic"></i> Crear nueva identidad
                                                </button>
                                                <button id="btn-import-nostr-identity" class="btn btn-sm btn-info" style="">
                                                    <i class="fa fa-download"></i> Importar identidad existente
                                                </button>
                                                <button id="btn-link-nostr" class="btn btn-sm" style="background:#7b4dff;border-color:#7b4dff;color:#fff;">
                                                    <svg width="14" height="14" viewBox="0 0 256 256" style="vertical-align:middle;margin-right:3px;">
                                                        <circle cx="128" cy="128" r="120" fill="white"/>
                                                        <path d="M80 100 Q128 60 176 100 Q200 140 176 180 Q128 220 80 180 Q56 140 80 100" fill="#7b4dff"/>
                                                    </svg>
                                                    Vincular con extensión
                                                </button>
                                            </div>
                                        </div>
                                    </details>

                                    <p style="margin:10px 0 0 0;font-size:0.85em;color:#888;">
                                        Extensiones: <a href="https://getalby.com" target="_blank">Alby</a>, <a href="https://chromewebstore.google.com/detail/nos2x/kpgefcfmnafjgpblomihpgmejjdanjjp" target="_blank">nos2x</a>
                                    </p>
                                `;
                            } else {
                                // ESTADO: NO tiene claves en IndexedDB
                                container.innerHTML = `
                                    <p style="margin:0 0 10px 0;color:#666;font-size:0.9em;">
                                        <strong>¿Cómo quieres continuar?</strong> Puedes crear una nueva identidad, importar una existente o usar una extensión.
                                    </p>

                                    <div style="margin-bottom:10px;">
                                        <button id="btn-create-nostr-identity-profile" class="btn btn-success">
                                            <i class="fa fa-magic"></i> Crear nueva identidad Nostr
                                        </button>

                                        <button id="btn-import-nostr-identity" class="btn btn-info">
                                            <i class="fa fa-download"></i> Importar identidad existente (nsec)
                                        </button>

                                        <button id="btn-link-nostr" class="btn btn-primary" style="background:#7b4dff;border-color:#7b4dff;">
                                            <svg width="16" height="16" viewBox="0 0 256 256" style="vertical-align:middle;margin-right:5px;margin-top:-4px;">
                                                <circle cx="128" cy="128" r="120" fill="white"/>
                                                <path d="M80 100 Q128 60 176 100 Q200 140 176 180 Q128 220 80 180 Q56 140 80 100" fill="#7b4dff"/>
                                            </svg>
                                            Vincular con extensión Nostr
                                        </button>
                                    </div>
                                    <!--
                                    <p style="margin:10px 0 0 0;font-size:0.75em;color:#888;">
                                        Extensiones compatibles: <a href="https://getalby.com" target="_blank">Alby</a>, <a href="https://chromewebstore.google.com/detail/nos2x/kpgefcfmnafjgpblomihpgmejjdanjjp" target="_blank">nos2x</a>
                                    </p>
                                    -->
                                    <p style="margin-top:10px;padding:4px 10px 0 10px;background:#f5f5f5;border-left:3px solid #4caf50;font-size:0.8em;color:#666;">
                                        <i class="fa fa-info-circle"></i> <strong>Recomendación:</strong> Crea una nueva identidad si es tu primera vez con Nostr. ¡Es gratis y tarda 2 segundos!
                                    </p>
                                `;
                            }
                        } catch (error) {
                            console.error('Error checking IndexedDB:', error);
                            // En caso de error, mostrar UI por defecto (sin claves)
                            container.innerHTML = `
                                <p style="margin:0 0 15px 0;color:#666;">No tienes cuenta Nostr vinculada.</p>

                                <div style="display:grid;gap:10px;margin-bottom:10px;">
                                    <button id="btn-create-nostr-identity-profile" class="btn btn-success" style="width:100%;">
                                        <i class="fa fa-magic"></i> Crear nueva identidad Nostr
                                    </button>

                                    <button id="btn-import-nostr-identity" class="btn btn-info" style="width:100%;">
                                        <i class="fa fa-download"></i> Importar identidad existente
                                    </button>

                                    <button id="btn-link-nostr" class="btn btn-primary" style="background:#7b4dff;border-color:#7b4dff;width:100%;">
                                        <svg width="16" height="16" viewBox="0 0 256 256" style="vertical-align:middle;margin-right:5px;">
                                            <circle cx="128" cy="128" r="120" fill="white"/>
                                            <path d="M80 100 Q128 60 176 100 Q200 140 176 180 Q128 220 80 180 Q56 140 80 100" fill="#7b4dff"/>
                                        </svg>
                                        Vincular con extensión Nostr
                                    </button>
                                </div>

                                <p style="margin:10px 0 0 0;font-size:0.75em;color:#888;text-align:center;">
                                    Extensiones compatibles: <a href="https://getalby.com" target="_blank">Alby</a>, <a href="https://chromewebstore.google.com/detail/nos2x/kpgefcfmnafjgpblomihpgmejjdanjjp" target="_blank">nos2x</a>
                                </p>

                                <p style="margin:15px 0 0 0;padding:10px;background:#f5f5f5;border-left:3px solid #4caf50;font-size:0.8em;color:#666;">
                                    <i class="fa fa-info-circle"></i> <strong>Recomendación:</strong> Crea una nueva identidad si es tu primera vez con Nostr. ¡Es gratis y tarda 2 segundos!
                                </p>
                            `;
                        }
                    })();

                    // ========== HANDLERS CON DELEGACIÓN DE EVENTOS (para elementos dinámicos) ==========

                    // Handler para vincular cuenta Nostr desde perfil (con extensión)
                    $(document).on('click', '#btn-link-nostr', async function(e) {
                        e.preventDefault();

                        // Verificar si hay extensión Nostr
                        if(typeof window.nostr === 'undefined') {
                            alert('No se detecta extensión Nostr.\n\nInstala una extensión como:\n• Alby (getalby.com)\n• nos2x\n• Flamingo');
                            return;
                        }

                        const btn = $(this);
                        const originalText = btn.html();
                        btn.html('<i class="fa fa-spinner fa-spin"></i> Vinculando...');
                        btn.prop('disabled', true);

                        try {
                            // 1. Solicitar challenge
                            const challengeResp = await fetch('login/ajax/op=nostr_link_challenge', {
                                method: 'POST'
                            });
                            const challengeData = await challengeResp.json();

                            if(!challengeData.success) {
                                throw new Error(challengeData.msg || 'Error obteniendo challenge');
                            }

                            // 2. Crear evento para firmar
                            const event = {
                                kind: 22242,
                                created_at: Math.floor(Date.now() / 1000),
                                tags: [
                                    ['challenge', challengeData.challenge],
                                    ['domain', challengeData.domain]
                                ],
                                content: 'link_account'
                            };

                            // 3. Firmar con la extensión
                            const signedEvent = await window.nostr.signEvent(event);

                            // 4. Enviar al servidor
                            // 4. Enviar al servidor
                            const linkResp = await fetch('login/ajax/op=nostr_link', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({ event: JSON.stringify(signedEvent) })
                            });
                            const result = await linkResp.json();

                            if(result.success) {
                                alert('✓ ' + result.msg);
                                location.reload(); // Recargar para mostrar el npub
                            } else {
                                throw new Error(result.msg || result.error || 'Error al vincular');
                            }

                        } catch(err) {
                            console.error('Nostr link error:', err);
                            alert('Error: ' + err.message);
                            btn.html(originalText);
                            btn.prop('disabled', false);
                        }
                    });

                    // Handler para vincular identidad (con firma obligatoria)
                    $(document).on('click', '#btn-link-manual-npub', async function(e) {
                        e.preventDefault();

                        const btn = $(this);
                        const originalText = btn.html();
                        btn.html('<i class="fa fa-spinner fa-spin"></i> Firmando...');
                        btn.prop('disabled', true);

                        try {
                            // 1. Verificar si tiene claves locales en IndexedDB
                            const hasLocalKeys = await checkLocalNostrKeys();
                            let signedEvent;

                            if (hasLocalKeys) {
                                // Firmar con claves locales
                                const localKeys = await getNostrKeys();

                                // Obtener challenge
                                const challengeResp = await fetch('login/ajax/op=nostr_link_challenge', {
                                    method: 'POST'
                                });
                                const challengeData = await challengeResp.json();

                                if(!challengeData.success) {
                                    throw new Error(challengeData.msg || 'Error obteniendo challenge');
                                }

                                // Crear evento para firmar
                                const event = {
                                    kind: 22242,
                                    created_at: Math.floor(Date.now() / 1000),
                                    tags: [
                                        ['challenge', challengeData.challenge],
                                        ['domain', challengeData.domain]
                                    ],
                                    content: 'link_account',
                                    pubkey: localKeys.pubkeyHex
                                };

                                // Firmar con claves locales
                                signedEvent = await signNostrEventLocal(event, localKeys.privkeyHex);

                            } else if (typeof window.nostr !== 'undefined') {
                                // Firmar con extensión Nostr
                                const challengeResp = await fetch('login/ajax/op=nostr_link_challenge', {
                                    method: 'POST'
                                });
                                const challengeData = await challengeResp.json();

                                if(!challengeData.success) {
                                    throw new Error(challengeData.msg || 'Error obteniendo challenge');
                                }

                                const event = {
                                    kind: 22242,
                                    created_at: Math.floor(Date.now() / 1000),
                                    tags: [
                                        ['challenge', challengeData.challenge],
                                        ['domain', challengeData.domain]
                                    ],
                                    content: 'link_account'
                                };

                                signedEvent = await window.nostr.signEvent(event);

                            } else {
                                throw new Error('No se puede firmar. Necesitas una extensión Nostr o generar claves primero.');
                            }

                            // Enviar evento firmado al servidor
                            const linkResp = await fetch('login/ajax/op=nostr_link', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({ event: JSON.stringify(signedEvent) })
                            });
                            const result = await linkResp.json();

                            if(result.success) {
                                alert('✓ ' + result.msg);
                                location.reload();
                            } else {
                                throw new Error(result.msg || result.error || 'Error al vincular');
                            }
                        } catch(err) {
                            console.error('Nostr link error:', err);
                            alert('Error: ' + err.message);
                            btn.html(originalText);
                            btn.prop('disabled', false);
                        }
                    });

                    // Handler para crear nueva identidad Nostr desde perfil
                    // IMPORTANTE: Usa la MISMA función que en login (createNostrIdentity)
                    // Esta función guarda las claves en IndexedDB automáticamente
                    $(document).on('click', '#btn-create-nostr-identity-profile', async function(e) {
                        e.preventDefault();
                        await createNostrIdentity();
                    });

                    // Handler para importar identidad Nostr existente (pegar nsec)
                    $(document).on('click', '#btn-import-nostr-identity', async function(e) {
                        e.preventDefault();
                        await importNostrIdentity();
                    });

                    // Handler para desvincular cuenta Nostr del servidor
                    $(document).on('click', '#btn-unlink-nostr', async function(e) {
                        e.preventDefault();

                        const confirmed = confirm(
                            '⚠️ ¿Desvincular cuenta Nostr?\n\n' +
                            'Esto eliminará la vinculación entre tu cuenta y tu identidad Nostr en el servidor.\n\n' +
                            '✓ Tus claves locales NO se borrarán\n' +
                            '✓ Podrás re-vincular cuando quieras\n\n' +
                            '¿Continuar?'
                        );

                        if (!confirmed) return;

                        const btn = $(this);
                        const originalText = btn.html();
                        btn.html('<i class="fa fa-spinner fa-spin"></i> Desvinculando...');
                        btn.prop('disabled', true);

                        try {
                            const resp = await fetch('login/ajax/op=nostr_unlink', {
                                method: 'POST'
                            });
                            const result = await resp.json();

                            if (result.success) {
                                alert('✓ ' + result.msg);
                                location.reload();
                            } else {
                                throw new Error(result.msg || result.error || 'Error al desvincular');
                            }
                        } catch(err) {
                            console.error('Nostr unlink error:', err);
                            alert('Error: ' + err.message);
                            btn.html(originalText);
                            btn.prop('disabled', false);
                        }
                    });

                    // Handler para eliminar claves locales de IndexedDB
                    $(document).on('click', '#btn-delete-local-keys', async function(e) {
                        e.preventDefault();

                        const confirmed = confirm(
                            '🗑️ ¿Eliminar claves de este navegador?\n\n' +
                            '⚠️ ATENCIÓN: Esto eliminará tus claves privadas de Nostr de este navegador.\n\n' +
                            '✗ NO podrás recuperarlas si no las tienes guardadas\n' +
                            '✗ Perderás acceso a tu identidad Nostr desde este dispositivo\n' +
                            '✓ La vinculación en el servidor NO se verá afectada\n\n' +
                            '¿Estás seguro de que quieres continuar?'
                        );

                        if (!confirmed) return;

                        // Segunda confirmación para evitar accidentes
                        const doubleConfirm = confirm(
                            '⚠️ ÚLTIMA ADVERTENCIA\n\n' +
                            '¿Tienes tus claves guardadas en un lugar seguro?\n\n' +
                            'Si no las tienes guardadas, las perderás para siempre.\n\n' +
                            'Clic en OK para ELIMINAR definitivamente.'
                        );

                        if (!doubleConfirm) return;

                        try {
                            // Eliminar claves de IndexedDB usando la función centralizada
                            await clearCurrentUserNostrKeys();
                            alert('✓ Claves eliminadas de este navegador correctamente');
                            location.reload();
                        } catch(err) {
                            console.error('Delete local keys error:', err);
                            alert('Error: ' + err.message);
                        }
                    });

                <?php } ?>

                <?php if(Invitation::isRequired()) { ?>
                // ---- Invitations tab ----
                (function(){
                    function loadInvitations(){
                        fetch('login/ajax/op=get_my_invitations').then(r=>r.json()).then(function(data){
                            if(data.error) return;
                            $('#inv-karma-score').text(data.score);
                            $('#inv-count-available').text(data.available);
                            $('#inv-count-used').text(data.used);
                            var tbody = $('#inv-table-body');
                            tbody.empty();
                            if(!data.items || data.items.length === 0){
                                tbody.html('<tr><td colspan="4" style="text-align:center;color:#999;"><?=t('NO_INVITATIONS','No tienes invitaciones aún')?></td></tr>');
                                return;
                            }
                            data.items.forEach(function(inv){
                                var st = inv.used == 1
                                    ? '<span style="color:#999;">\u2713 <?=t('USED','Usada')?></span>'
                                    : '<span style="color:#28a745;font-weight:bold;">\u25CF <?=t('AVAILABLE','Disponible')?></span>';
                                var cd = inv.used == 1
                                    ? '<span style="color:#999;">' + inv.code + '</span>'
                                    : '<code style="cursor:pointer;font-size:1.1em;" title="Click para copiar" onclick="navigator.clipboard.writeText(\'' + inv.code + '\');this.style.color=\'#28a745\';">' + inv.code + '</code>';
                                var created = inv.created_at ? new Date(inv.created_at * 1000).toLocaleDateString() : '-';
                                var used_at = inv.used_at ? new Date(inv.used_at * 1000).toLocaleDateString() : '-';
                                tbody.append('<tr><td>' + cd + '</td><td>' + st + '</td><td>' + created + '</td><td>' + used_at + '</td></tr>');
                            });
                        });
                    }

                    $('#btn-generate-invitation').on('click', function(){
                        var btn = $(this);
                        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ...');
                        fetch('login/ajax/op=generate_invitation',{method:'POST'}).then(r=>r.json()).then(function(data){
                            var msg = $('#inv-message');
                            if(data.error){
                                msg.css({background:'#fee',color:'#c00'}).text(data.msg).show();
                            }else{
                                msg.css({background:'#efe',color:'#060'}).html('<?=t('INVITATION_CREATED','Invitación creada')?>: <code style="font-size:1.2em;cursor:pointer;" onclick="navigator.clipboard.writeText(\'' + data.code + '\');this.style.color=\'#28a745\';">' + data.code + '</code> (click para copiar)').show();
                                loadInvitations();
                            }
                            btn.prop('disabled', false).html('<i class="fa fa-plus"></i> <?=t('GENERATE_INVITATION','Generar invitación')?>');
                        });
                    });

                    $('a[href="#ftab-tab_invitations"]').on('click', function(){ loadInvitations(); });
                    if($('#ftab-tab_invitations').is(':visible')) loadInvitations();
                })();
                <?php } ?>

            });
        </script>
        <?php

    } else if ($_SESSION['valid_user'])  {

        if($logged) {

            if ($_SESSION['save']=='1'){
                ?>
                <script>
                    ready(function (){
                        saveCookie('<?=CFG::$vars['prefix'].'_username'?>','<?=$_SESSION[CFG::$vars['prefix'].'_username']?>');
                        saveCookie('<?=CFG::$vars['prefix'].'_userpass'?>','<?=$_SESSION[CFG::$vars['prefix'].'_userpass']?>');            
                    });
                </script>
                <?php
            }
            //die();            
        }

    } else if ($_ARGS[1] =='logout')  { 

        ?>
        <script>
            ready(function (){
                deleteCookie('<?=CFG::$vars['prefix'].'_username'?>');
                deleteCookie('<?=CFG::$vars['prefix'].'_userpass'?>');

                // Disconnect Nostr (noxtr) on web logout
                if (typeof Noxtr !== 'undefined' && Noxtr.logout) {
                    try { Noxtr.logout(); } catch(e) { console.warn('Noxtr logout error:', e); }
                } else {
                    // Noxtr not loaded on this page — clean localStorage only
                    // Note: IndexedDB keys are preserved (keyed by userId, restored on next login)
                    try { localStorage.removeItem('noxtr_npub'); } catch(e) {}
                    try { localStorage.removeItem('noxtr_nip46'); } catch(e) {}
                }

                //if (window.location.href.indexOf('/logout') > -1) {
                //    logoutFromGoogle();
                //}                
                /** FIX, this block show error in console, but not important, except google sessión is not deleted
                // This loads a hidden iframe that logs the user out from Google
                var iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = 'https://accounts.google.com/logout';
                document.body.appendChild(iframe);
                
                // You can remove the iframe after a delay
                setTimeout(function() {
                    document.body.removeChild(iframe);
                }, 5000);
                **/
            });
        </script>
        <?php

    } else {
        // Handler para login passwordless
            include(SCRIPT_DIR_MODULE.'/passwordless_js.php');
        ?> 
        <script>
        $(document).ready(function(){
 
            <?php if ( CFG::$vars['login']['nostr']['enabled']){ ?>

                // Handler para registro con Nostr — muestra opciones
                $('#btn-nostr-register').on('click', function(e){
                    e.preventDefault();
                    var invInput = document.getElementById('shared-invitation-code');
                    window._nostr_invitation_code = invInput ? invInput.value.trim() : '';
                    nostrRegisterFlow($(this));
                });

                // Handler para login con Nostr (NIP-07)
                $('#btn-nostr').on('click', async function(e){
                    e.preventDefault();

                    const btn = $(this);
                    const originalText = btn.html();

                    // 0. Si hay email/username escrito, buscar ese usuario concreto
                    var usernameInput = document.getElementById('username');
                    var usernameVal = usernameInput ? usernameInput.value.trim() : '';
                    if (usernameVal) {
                        await loginNostrByUsername(btn, originalText, usernameVal);
                        return;
                    }

                    // 1. Verificar si hay extensión Nostr
                    if(typeof window.nostr !== 'undefined') {
                        // Usar extensión
                        await loginWithNostrExtension(btn, originalText);
                        return;
                    }

                    // 2. Verificar si hay ALGUNA clave local en IndexedDB (cualquier usuario)
                    const localKeys = await findAnyNostrKeys();
                    if(localKeys) {
                        console.log('[btn-nostr] Encontradas claves locales, haciendo login automático con:', localKeys.npub);
                        btn.html('<i class="fa fa-spinner fa-spin"></i> Firmando...');
                        btn.css('pointer-events', 'none');
                        try {
                            // Usar directamente las claves encontradas
                            await loginWithSpecificNostrKey(localKeys);
                        } catch(err) {
                            console.error('Local Nostr login error:', err);
                            alert('Error: ' + err.message);
                        }
                        btn.html(originalText);
                        btn.css('pointer-events', '');
                        return;
                    }

                    // 3. Si no hay extensión ni claves locales, mostrar diálogo con opciones
                    showNostrOptionsDialog();
                });

                // ============================================================
                // NIP-46 LOGIN (nostrconnect://)
                // ============================================================

                $('#btn-nostr-connect').on('click', async function(e) {
                    e.preventDefault();
                    await startNip46Login();
                });

                function loadNobleCiphers46() {
                    if (window.nobleCiphers) return Promise.resolve();
                    return new Promise((resolve, reject) => {
                        const s = document.createElement('script');
                        s.src = '/_lib_/bitcoin/noble-ciphers.min.js?ver=1.2.1b';
                        s.onload = resolve;
                        s.onerror = () => reject(new Error('Error cargando noble-ciphers'));
                        document.head.appendChild(s);
                    });
                }

                function _n46HexToBytes(hex) {
                    const b = new Uint8Array(Math.ceil(hex.length / 2));
                    for (let i = 0; i < b.length; i++) b[i] = parseInt(hex.substr(i * 2, 2), 16);
                    return b;
                }

                async function _n44Hmac(key, data) {
                    const k = await crypto.subtle.importKey('raw', key, { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']);
                    return new Uint8Array(await crypto.subtle.sign('HMAC', k, data));
                }

                async function _n44ConvKey(privkey, pubkeyHex) {
                    const shared = nobleSecp256k1.getSharedSecret(privkey, '02' + pubkeyHex);
                    const sharedX = (typeof shared === 'string' ? _n46HexToBytes(shared) : shared).slice(1, 33);
                    const saltKey = await crypto.subtle.importKey('raw', new TextEncoder().encode('nip44-v2'), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']);
                    return new Uint8Array(await crypto.subtle.sign('HMAC', saltKey, sharedX));
                }

                async function _n44Hkdf(prk, info, len) {
                    const prkKey = await crypto.subtle.importKey('raw', prk, { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']);
                    const okm = new Uint8Array(len);
                    let T = new Uint8Array(0), offset = 0, ctr = 1;
                    while (offset < len) {
                        const blk = new Uint8Array(T.length + info.length + 1);
                        blk.set(T); blk.set(info, T.length); blk[T.length + info.length] = ctr++;
                        T = new Uint8Array(await crypto.subtle.sign('HMAC', prkKey, blk));
                        const copy = Math.min(T.length, len - offset);
                        okm.set(T.subarray(0, copy), offset); offset += copy;
                    }
                    return okm;
                }

                async function _n44Decrypt(payload, convKey) {
                    const raw = Uint8Array.from(atob(payload), c => c.charCodeAt(0));
                    if (raw[0] !== 2) throw new Error('bad NIP-44 version');
                    const nonce = raw.slice(1, 33), mac = raw.slice(raw.length - 32), ct = raw.slice(33, raw.length - 32);
                    const mk = await _n44Hkdf(convKey, nonce, 76);
                    const hmacData = new Uint8Array([...nonce, ...ct]);
                    const expectedMac = await _n44Hmac(mk.slice(44, 76), hmacData);
                    let match = true; for (let i = 0; i < 32; i++) if (mac[i] !== expectedMac[i]) match = false;
                    if (!match) throw new Error('invalid MAC');
                    const padded = nobleCiphers.chacha20(mk.slice(0, 32), mk.slice(32, 44), ct);
                    const len = (padded[0] << 8) | padded[1];
                    return new TextDecoder().decode(padded.slice(2, 2 + len));
                }

                async function _n44Encrypt(plaintext, convKey) {
                    const utf8 = new TextEncoder().encode(plaintext);
                    const l = utf8.length;
                    const plen = l <= 32 ? 32 : (() => { const p = 1 << (32 - Math.clz32(l - 1)); const c = p <= 256 ? 32 : p / 8; return c * (Math.floor((l - 1) / c) + 1); })();
                    const padded = new Uint8Array(2 + plen);
                    padded[0] = (l >> 8) & 0xff; padded[1] = l & 0xff; padded.set(utf8, 2);
                    const nonce = crypto.getRandomValues(new Uint8Array(32));
                    const mk = await _n44Hkdf(convKey, nonce, 76);
                    const ct = nobleCiphers.chacha20(mk.slice(0, 32), mk.slice(32, 44), padded);
                    const hmacData = new Uint8Array([...nonce, ...ct]);
                    const mac = await _n44Hmac(mk.slice(44, 76), hmacData);
                    const result = new Uint8Array([2, ...nonce, ...ct, ...mac]);
                    return btoa(String.fromCharCode.apply(null, result));
                }

                async function startNip46Login() {
                    const modal = document.getElementById('nip46-login-modal');
                    const statusEl = document.getElementById('nip46-status');
                    const uriEl = document.getElementById('nip46-uri');
                    if (!modal) return;

                    function setStatus(msg, isError) {
                        statusEl.textContent = msg;
                        statusEl.style.color = isError ? '#c00' : '#555';
                    }

                    try {
                        await loadNobleSecp256k1();
                        await loadNobleCiphers46();

                        // Generar keypair efímero del cliente
                        const clientPriv = bytesToHex(crypto.getRandomValues(new Uint8Array(32)));
                        const pkRaw = nobleSecp256k1.getPublicKey(clientPriv, true);
                        const clientPub = (typeof pkRaw === 'string' ? pkRaw : bytesToHex(pkRaw)).slice(2);
                        const secret = bytesToHex(crypto.getRandomValues(new Uint8Array(16)));

                        // Construir URI nostrconnect://
                        const relays = ['wss://relay.damus.io', 'wss://nos.lol', 'wss://relay.nostr.band'];
                        const uri = 'nostrconnect://' + clientPub
                            + '?' + relays.map(r => 'relay=' + encodeURIComponent(r)).join('&')
                            + '&secret=' + secret
                            + '&name=' + encodeURIComponent(window.location.hostname)
                            + '&perms=sign_event:27235';

                        uriEl.value = uri;
                        modal.style.display = 'block';
                        setStatus('Esperando signer...');

                        // QR code
                        var qrEl = document.getElementById('nip46-qr');
                        if (qrEl) {
                            qrEl.innerHTML = '';
                            if (typeof QRCode !== 'undefined') {
                                new QRCode(qrEl, { text: uri, width: 180, height: 180,
                                    colorDark: '#000000', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
                            }
                        }

                        // Estado NIP-46
                        let signerPubkey = null, convKey = null, connected = false;
                        const pending = {}, seen = {}, sockets = {};

                        function closeAll() {
                            Object.values(sockets).forEach(ws => { try { ws.close(); } catch(e) {} });
                        }

                        async function sendNip46Request(method, params) {
                            if (!signerPubkey || !convKey) throw new Error('Not connected');
                            const id = bytesToHex(crypto.getRandomValues(new Uint8Array(16)));
                            const encrypted = await _n44Encrypt(JSON.stringify({ id, method, params: params || [] }), convKey);
                            const ev = { pubkey: clientPub, created_at: Math.floor(Date.now() / 1000), kind: 24133, tags: [['p', signerPubkey]], content: encrypted };
                            const hashBuf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(JSON.stringify([0, ev.pubkey, ev.created_at, ev.kind, ev.tags, ev.content])));
                            ev.id = bytesToHex(new Uint8Array(hashBuf));
                            const sig = await nobleSecp256k1.schnorr.sign(ev.id, clientPriv);
                            ev.sig = typeof sig === 'string' ? sig : bytesToHex(sig);
                            const msg = JSON.stringify(['EVENT', ev]);
                            Object.values(sockets).forEach(ws => { try { if (ws.readyState === 1) ws.send(msg); } catch(e) {} });
                            return new Promise((resolve, reject) => {
                                const t = setTimeout(() => { delete pending[id]; reject(new Error('Timeout: ' + method)); }, 30000);
                                pending[id] = { resolve, reject, timeout: t };
                            });
                        }

                        async function handleNip46Event(ev) {
                            if (ev.kind !== 24133 || seen[ev.id]) return;
                            seen[ev.id] = true;
                            if (!signerPubkey) {
                                signerPubkey = ev.pubkey;
                                convKey = await _n44ConvKey(clientPriv, signerPubkey);
                            }
                            if (ev.pubkey !== signerPubkey) return;
                            let msg;
                            try { msg = JSON.parse(await _n44Decrypt(ev.content, convKey)); } catch(e) { return; }

                            // Respuesta de connect
                            if (!connected && (msg.result === secret || msg.result === 'ack')) {
                                connected = true;
                                setStatus('Conectado. Obteniendo clave pública...');
                                try {
                                    const userPubkey = await sendNip46Request('get_public_key', []);
                                    setStatus('Firmando evento de autenticación...');

                                    const chalResp = await fetch('login/ajax/op=nostr_challenge', { method: 'POST' });
                                    const chalData = await chalResp.json();
                                    if (!chalData.success) throw new Error('Challenge failed: ' + (chalData.msg || ''));

                                    const loginEv = {
                                        kind: 27235, pubkey: userPubkey,
                                        created_at: Math.floor(Date.now() / 1000),
                                        tags: [['challenge', chalData.challenge], ['u', 'https://' + chalData.domain]],
                                        content: ''
                                    };
                                    const signedStr = await sendNip46Request('sign_event', [loginEv]);
                                    const signed = typeof signedStr === 'string' ? JSON.parse(signedStr) : signedStr;

                                    setStatus('Verificando en servidor...');
                                    const vResp = await fetch('login/ajax/op=nostr_verify', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                        body: 'event=' + encodeURIComponent(JSON.stringify(signed))
                                    });
                                    const vData = await vResp.json();
                                    if (vData.success) {
                                        setStatus('¡Login exitoso! Redirigiendo...');
                                        closeAll();
                                        window.location.href = vData.redirect || '/';
                                    } else {
                                        throw new Error(vData.msg || vData.error || 'Verificación fallida');
                                    }
                                } catch(e) {
                                    setStatus('Error: ' + e.message, true);
                                    closeAll();
                                }
                                return;
                            }

                            // Respuesta a un request pendiente
                            if (msg.id && pending[msg.id]) {
                                const p = pending[msg.id];
                                clearTimeout(p.timeout);
                                delete pending[msg.id];
                                msg.error ? p.reject(new Error(msg.error)) : p.resolve(msg.result);
                            }
                        }

                        document.getElementById('nip46-cancel').onclick = function() {
                            closeAll();
                            modal.style.display = 'none';
                        };

                        // Conectar a los relays y suscribirse
                        relays.forEach(url => {
                            try {
                                const ws = new WebSocket(url);
                                sockets[url] = ws;
                                ws.onopen = () => ws.send(JSON.stringify(['REQ', 'nip46-login', { kinds: [24133], '#p': [clientPub], limit: 5 }]));
                                ws.onmessage = evt => {
                                    try { const d = JSON.parse(evt.data); if (d[0] === 'EVENT') handleNip46Event(d[2]); } catch(e) {}
                                };
                            } catch(e) {}
                        });

                    } catch(e) {
                        setStatus('Error: ' + e.message, true);
                    }
                }

                // Login con extensión Nostr (NIP-07)
                async function loginWithNostrExtension(btn, originalText) {
                    btn.html('<i class="fa fa-spinner fa-spin"></i> Firmando...');
                    btn.css('pointer-events', 'none');
                    
                    try {
                        // 1. Solicitar challenge al servidor
                        const challengeResp = await fetch('login/ajax/op=nostr_challenge', {
                            method: 'POST'
                        });
                        const challengeData = await challengeResp.json();
                        
                        if(!challengeData.success) {
                            throw new Error(challengeData.msg || 'Error obteniendo challenge');
                        }
                        
                        // 2. Crear evento para firmar (kind 22242 - NIP-42 Auth)
                        const event = {
                            kind: 22242,
                            created_at: Math.floor(Date.now() / 1000),
                            tags: [
                                ['challenge', challengeData.challenge],
                                ['domain', challengeData.domain]
                            ],
                            content: ''
                        };
                        
                        // 3. Firmar con la extensión Nostr
                        const signedEvent = await window.nostr.signEvent(event);
                        console.log('Signed event:', signedEvent);
                        
                        // 4. Enviar al servidor para verificar
                        var _vparams = { event: JSON.stringify(signedEvent) };
                        if(window._nostr_invitation_code) _vparams.invitation_code = window._nostr_invitation_code;
                        const verifyResp = await fetch('login/ajax/op=nostr_verify', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams(_vparams)
                        });
                        const result = await verifyResp.json();
                        
                        if(result.success) {
                            btn.html('<i class="fa fa-check"></i> ¡Bienvenido!');
                            
                            // Mensaje diferente para usuarios nuevos
                            if(result.is_new) {
                                notify('✓ ' + result.msg + ' - Te recomendamos completar tu perfil.', 'success', 3000);
                            } else {
                                console.log('Login OK:', result.msg);
                            }
                            
                            window.location.href = result.redirect || '/';
                        } else {
                            throw new Error(result.msg || result.error || 'Verificación fallida');
                        }
                        
                    } catch(err) {
                        console.error('Nostr login error:', err);
                        error('Error: ' + err.message);
                        btn.html(originalText);
                        btn.css('pointer-events', '');
                    }
                }
                
                // Función para mostrar diálogo de opciones Nostr (sin extensión)
                function showNostrOptionsDialog() {
                    $("body").dialog({
                        title: '<i class="fa fa-key"></i> Login con Nostr',
                        type: 'html',
                        width: '520px',
                        openAnimation: 'zoom',
                        closeAnimation: 'fade',
                        content: `
                            <div style="padding:20px;text-align:center;">
                                <p style="margin-bottom:15px;color:#666;">
                                    No se detectó extensión Nostr en tu navegador.
                                </p>
                                
                                <div style="background:#f8f4ff;border:1px solid #e0d4f7;border-radius:8px;padding:15px;margin-bottom:15px;">
                                    <p style="font-size:0.95em;color:#6B4FCF;margin-bottom:8px;">
                                        <i class="fa fa-magic"></i> <strong>¿Nuevo en Nostr?</strong>
                                    </p>
                                    <p style="font-size:0.85em;color:#666;margin:0;">
                                        Te creamos una identidad Nostr en segundos.<br>
                                        Tus claves se guardan en tu navegador y son tuyas para siempre.<br>
                                        <span style="color:#888;">Podrás usarlas en cualquier app Nostr del mundo.</span>
                                    </p>
                                </div>
                                
                                <p style="font-size:0.8em;color:#888;margin-bottom:10px;">
                                    <strong>¿Ya tienes Nostr?</strong> Instala una extensión para firmar automáticamente.<br>
                                    <span style="font-size:0.9em;color:#aaa;">(Si eliges Alby, que Dios o el MEV se apiaden de ti 🙏)</span>
                                </p>
                            </div>
                        `,
                        buttons: [
                            { 
                                text: 'Cancelar', 
                                class: 'btn btn-reset', 
                                action: function(event, overlay) {
                                    document.body.removeChild(overlay);
                                }
                            },
                            { 
                                text: '<i class="fa fa-puzzle-piece"></i> Instalar nos2x', 
                                class: 'btn btn-info', 
                                action: function(event, overlay) {
                                    window.open('https://chromewebstore.google.com/detail/nos2x/kpgefcfmnafjgpblomihpgmejjdanjjp', '_blank');
                                }
                            },
                            { 
                                text: '<i class="fa fa-magic"></i> Crear identidad', 
                                class: 'btn btn-success', 
                                action: function(event, overlay) {
                                    document.body.removeChild(overlay);
                                    setTimeout(() => createNostrIdentity(), 300);
                                }
                            }
                        ]
                    });
                }
                
                // Función para mostrar diálogo de login manual con npub
                function showNostrManualLoginDialog() {
                    $("body").dialog({
                        title: '<i class="fa fa-key"></i> Login con Nostr (manual)',
                        type: 'html',
                        width: '500px',
                        openAnimation: 'zoom',
                        closeAnimation: 'fade',
                        content: `
                            <div style="padding:15px;">
                                <div id="nostr-manual-step1">
                                    <p style="margin-bottom:10px;">Introduce tu clave pública Nostr:</p>
                                    <input type="text" id="nostr-npub-input" class="form-control" 
                                        placeholder="npub1... o clave hex (64 caracteres)" 
                                        style="width:100%;padding:10px;font-family:monospace;margin-bottom:15px;">
                                    <button id="nostr-get-challenge" class="btn btn-block" style="background:#8B5CF6;color:#fff;">
                                        Obtener challenge para firmar
                                    </button>
                                </div>
                                <div id="nostr-manual-step2" style="display:none;">
                                    <p><strong>Tu clave pública:</strong></p>
                                    <code id="nostr-pubkey-display" style="display:block;background:#f5f5f5;padding:8px;border-radius:4px;word-break:break-all;font-size:0.8em;margin-bottom:15px;"></code>
                                    
                                    <p><strong>Evento a firmar (JSON):</strong></p>
                                    <textarea id="nostr-event-to-sign" readonly 
                                        style="width:100%;height:120px;font-family:monospace;font-size:0.7em;background:#1a1a2e;color:#0f0;padding:8px;border-radius:4px;margin-bottom:5px;"></textarea>
                                    <button id="nostr-copy-event" class="btn btn-sm"><i class="fa fa-copy"></i> Copiar</button>
                                    
                                    <p style="margin-top:15px;"><strong>Pega el evento firmado:</strong></p>
                                    <textarea id="nostr-signed-event" placeholder='{"id":"...","pubkey":"...","sig":"...","kind":22242,...}' 
                                        style="width:100%;height:80px;font-family:monospace;font-size:0.7em;padding:8px;margin-bottom:10px;"></textarea>
                                    <button id="nostr-verify-manual" class="btn btn-block" style="background:#8B5CF6;color:#fff;">
                                        Verificar y entrar
                                    </button>
                                    
                                    <p style="margin-top:10px;font-size:0.8em;color:#666;">
                                        <i class="fa fa-info-circle"></i> Firma con: Amethyst, Damus, Primal, o cualquier cliente Nostr.
                                    </p>
                                </div>
                                <div id="nostr-manual-status" style="margin-top:10px;"></div>
                            </div>
                        `,
                        onLoad: function(overlay) {
                            let currentChallenge = null;
                            let currentPubkey = null;
                            
                            // Paso 1: Obtener challenge
                            $(overlay).find('#nostr-get-challenge').on('click', async function(){
                                const npubInput = $(overlay).find('#nostr-npub-input').val().trim();
                                const statusEl = $(overlay).find('#nostr-manual-status');
                                
                                if(!npubInput) {
                                    statusEl.html('<span style="color:red;">Introduce tu npub o clave pública hex</span>');
                                    return;
                                }
                                
                                statusEl.html('<i class="fa fa-spinner fa-spin"></i> Obteniendo challenge...');
                                
                                try {
                                    const resp = await fetch('login/ajax/op=nostr_challenge_for_pubkey', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                        body: new URLSearchParams({ pubkey: npubInput })
                                    });
                                    const data = await resp.json();
                                    
                                    if(!data.success) {
                                        throw new Error(data.msg || 'Error obteniendo challenge');
                                    }
                                    
                                    currentChallenge = data.challenge;
                                    currentPubkey = data.pubkey_hex;
                                    
                                    // Mostrar pubkey
                                    $(overlay).find('#nostr-pubkey-display').text(data.npub || currentPubkey);
                                    
                                    // Crear evento para firmar
                                    const eventToSign = {
                                        kind: 22242,
                                        created_at: Math.floor(Date.now() / 1000),
                                        tags: [
                                            ['challenge', currentChallenge],
                                            ['domain', data.domain]
                                        ],
                                        content: '',
                                        pubkey: currentPubkey
                                    };
                                    
                                    $(overlay).find('#nostr-event-to-sign').val(JSON.stringify(eventToSign, null, 2));
                                    
                                    // Mostrar paso 2
                                    $(overlay).find('#nostr-manual-step1').hide();
                                    $(overlay).find('#nostr-manual-step2').show();
                                    statusEl.html('<span style="color:green;">✓ Challenge generado. Firma el evento con tu cliente Nostr.</span>');
                                    
                                } catch(err) {
                                    statusEl.html('<span style="color:red;">Error: ' + err.message + '</span>');
                                }
                            });
                            
                            // Copiar evento
                            $(overlay).find('#nostr-copy-event').on('click', function(){
                                const eventText = $(overlay).find('#nostr-event-to-sign').val();
                                navigator.clipboard.writeText(eventText).then(() => {
                                    $(this).html('<i class="fa fa-check"></i> Copiado!');
                                    setTimeout(() => $(this).html('<i class="fa fa-copy"></i> Copiar'), 2000);
                                });
                            });
                            
                            // Paso 2: Verificar firma
                            $(overlay).find('#nostr-verify-manual').on('click', async function(){
                                const signedEventJson = $(overlay).find('#nostr-signed-event').val().trim();
                                const statusEl = $(overlay).find('#nostr-manual-status');
                                
                                if(!signedEventJson) {
                                    statusEl.html('<span style="color:red;">Pega el evento firmado</span>');
                                    return;
                                }
                                
                                statusEl.html('<i class="fa fa-spinner fa-spin"></i> Verificando firma...');
                                
                                try {
                                    // Validar JSON
                                    let signedEvent;
                                    try {
                                        signedEvent = JSON.parse(signedEventJson);
                                    } catch(e) {
                                        throw new Error('JSON inválido. Asegúrate de copiar el evento completo.');
                                    }
                                    
                                    // Verificar que tiene los campos necesarios
                                    if(!signedEvent.sig || !signedEvent.pubkey || !signedEvent.id) {
                                        throw new Error('El evento debe tener id, pubkey y sig');
                                    }
                                    
                                    var _vparams3 = { event: JSON.stringify(signedEvent) };
                                    if(window._nostr_invitation_code) _vparams3.invitation_code = window._nostr_invitation_code;
                                    const resp = await fetch('login/ajax/op=nostr_verify', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                        body: new URLSearchParams(_vparams3)
                                    });
                                    const result = await resp.json();
                                    
                                    if(result.success) {
                                        statusEl.html('<span style="color:green;font-size:1.2em;">✓ ' + result.msg + '</span>');
                                        setTimeout(() => {
                                            window.location.href = result.redirect || '/';
                                        }, 1500);
                                    } else {
                                        throw new Error(result.msg || result.error || 'Verificación fallida');
                                    }
                                    
                                } catch(err) {
                                    statusEl.html('<span style="color:red;">Error: ' + err.message + '</span>');
                                }
                            });
                        }
                    });
                }

            <?php } ?>
        });
        </script>
        <?php
    }

    // ============================================================================
    // FUNCIONES GLOBALES NOSTR - Disponibles en todas las vistas
    // ============================================================================
    ?>
    <script>
    // ========== NOSTR IDENTITY GENERATION - FUNCIONES GLOBALES ==========
    // Estas funciones están disponibles tanto en login como en perfil

    
    // comentado por repetido    

    const NOSTR_DB_NAME = 'JuxNostrKeys';
    const NOSTR_DB_VERSION = 1;
    const NOSTR_STORE_NAME = 'keys';

    // Abrir/crear base de datos IndexedDB
    function openNostrDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(NOSTR_DB_NAME, NOSTR_DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains(NOSTR_STORE_NAME)) {
                    db.createObjectStore(NOSTR_STORE_NAME, { keyPath: 'id' });
                }
            };
        });
    }

    // Obtener userId del usuario actualmente logueado
    async function getCurrentUserId() {
        try {
            const response = await fetch('login/ajax/op=get_current_user_id', {
                method: 'POST'
            });
            const data = await response.json();
            return data.userId || null;
        } catch (error) {
            console.error('[getCurrentUserId] Error:', error);
            return null;
        }
    }

    // Guardar claves en IndexedDB (asociadas al usuario actual)
    async function saveNostrKeys(npub, nsec, pubkeyHex, privkeyHex) {
        const db = await openNostrDB();

        // Obtener userId actual (null si no está logueado)
        const userId = await getCurrentUserId();
        const keyId = userId ? `user_${userId}` : 'guest';

        return new Promise((resolve, reject) => {
            const tx = db.transaction(NOSTR_STORE_NAME, 'readwrite');
            const store = tx.objectStore(NOSTR_STORE_NAME);

            store.put({
                id: keyId,
                userId: userId,
                npub: npub,
                nsec: nsec,
                pubkeyHex: pubkeyHex,
                privkeyHex: privkeyHex,
                createdAt: new Date().toISOString(),
                createdOn: window.location.hostname
            });

            tx.oncomplete = () => {
                console.log(`[saveNostrKeys] Claves guardadas para: ${keyId}`);
                resolve(true);
            };
            tx.onerror = () => reject(tx.error);
        });
    }

    // Obtener claves de IndexedDB del usuario actual
    async function getNostrKeys() {
        const db = await openNostrDB();

        // Obtener userId actual
        const userId = await getCurrentUserId();
        const keyId = userId ? `user_${userId}` : 'guest';

        return new Promise((resolve, reject) => {
            const tx = db.transaction(NOSTR_STORE_NAME, 'readonly');
            const store = tx.objectStore(NOSTR_STORE_NAME);
            const request = store.get(keyId);

            request.onsuccess = () => {
                const result = request.result || null;
                console.log(`[getNostrKeys] Claves para ${keyId}:`, result ? '✓ Encontradas' : '✗ No encontradas');
                resolve(result);
            };
            request.onerror = () => reject(request.error);
        });
    }

    // Bech32 encoding para npub/nsec
    const BECH32_CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';

    function bech32Polymod(values) {
        const GEN = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
        let chk = 1;
        for (const v of values) {
            const b = chk >> 25;
            chk = ((chk & 0x1ffffff) << 5) ^ v;
            for (let i = 0; i < 5; i++) {
                if ((b >> i) & 1) chk ^= GEN[i];
            }
        }
        return chk;
    }

    function bech32HrpExpand(hrp) {
        const ret = [];
        for (const c of hrp) {
            ret.push(c.charCodeAt(0) >> 5);
        }
        ret.push(0);
        for (const c of hrp) {
            ret.push(c.charCodeAt(0) & 31);
        }
        return ret;
    }

    function bech32CreateChecksum(hrp, data) {
        const values = bech32HrpExpand(hrp).concat(data).concat([0, 0, 0, 0, 0, 0]);
        const polymod = bech32Polymod(values) ^ 1;
        const ret = [];
        for (let i = 0; i < 6; i++) {
            ret.push((polymod >> (5 * (5 - i))) & 31);
        }
        return ret;
    }

    function convertBits(data, fromBits, toBits, pad = true) {
        let acc = 0;
        let bits = 0;
        const ret = [];
        const maxv = (1 << toBits) - 1;

        for (const value of data) {
            acc = (acc << fromBits) | value;
            bits += fromBits;
            while (bits >= toBits) {
                bits -= toBits;
                ret.push((acc >> bits) & maxv);
            }
        }

        if (pad && bits > 0) {
            ret.push((acc << (toBits - bits)) & maxv);
        }

        return ret;
    }

    function bech32Encode(hrp, data) {
        const combined = data.concat(bech32CreateChecksum(hrp, data));
        let ret = hrp + '1';
        for (const d of combined) {
            ret += BECH32_CHARSET[d];
        }
        return ret;
    }

    function hexToBytes(hex) {
        const bytes = [];
        for (let i = 0; i < hex.length; i += 2) {
            bytes.push(parseInt(hex.substr(i, 2), 16));
        }
        return bytes;
    }

    function bytesToHex(bytes) {
        return Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
    }

    function hexToNpub(pubkeyHex) {
        const bytes = hexToBytes(pubkeyHex);
        const words = convertBits(bytes, 8, 5);
        return bech32Encode('npub', words);
    }

    function hexToNsec(privkeyHex) {
        const bytes = hexToBytes(privkeyHex);
        const words = convertBits(bytes, 8, 5);
        return bech32Encode('nsec', words);
    }

    // Decodificar bech32 (npub/nsec → hex)
    function npubToHex(npub) {
        const { prefix, words } = bech32Decode(npub);
        if (prefix !== 'npub') {
            throw new Error('Invalid npub format');
        }
        const bytes = convertBits(words, 5, 8, false);
        return bytesToHex(bytes);
    }

    function nsecToHex(nsec) {
        const { prefix, words } = bech32Decode(nsec);
        if (prefix !== 'nsec') {
            throw new Error('Invalid nsec format');
        }
        const bytes = convertBits(words, 5, 8, false);
        return bytesToHex(bytes);
    }
    function bech32Decode(bechString) {
        const checksum = bechString.toLowerCase();
        const sepIndex = checksum.lastIndexOf('1');

        if (sepIndex < 1) {
            throw new Error('Invalid bech32 string');
        }

        const hrp = checksum.substring(0, sepIndex);
        const data = checksum.substring(sepIndex + 1);

        if (data.length < 6) {
            throw new Error('Invalid bech32 data length');
        }

        const decoded = [];
        for (let i = 0; i < data.length; i++) {
            const v = BECH32_CHARSET.indexOf(data[i]);
            if (v === -1) {
                throw new Error('Invalid bech32 character');
            }
            decoded.push(v);
        }

        // Verificar checksum
        const hrpExpanded = bech32HrpExpand(hrp);
        const combined = hrpExpanded.concat(decoded);
        if (bech32Polymod(combined) !== 1) {
            throw new Error('Invalid bech32 checksum');
        }

        // Remover checksum (últimos 6 caracteres)
        const dataWithoutChecksum = decoded.slice(0, -6);

        return { hrp, data: dataWithoutChecksum };
    }

    // Convertir nsec/npub a hex
    function bech32ToHex(bech32String) {
        try {
            const decoded = bech32Decode(bech32String);
            const bytes = convertBits(decoded.data, 5, 8, false);
            return bytesToHex(bytes);
        } catch (error) {
            console.error('Error decoding bech32:', error);
            throw new Error('Formato inválido. Debe ser npub1... o nsec1...');
        }
    }

    // Derivar claves públicas desde clave privada
    async function deriveKeysFromNsec(nsec) {
        // Validar formato
        if (!nsec || !nsec.startsWith('nsec1')) {
            throw new Error('Formato inválido. La clave privada debe empezar con "nsec1"');
        }

        // Cargar noble-secp256k1 si no está disponible
        if (typeof nobleSecp256k1 === 'undefined') {
            await loadNobleSecp256k1();
        }

        // Convertir nsec a hex
        const privkeyHex = bech32ToHex(nsec);

        // Validar longitud (debe ser 64 caracteres hex = 32 bytes)
        if (privkeyHex.length !== 64) {
            throw new Error('Clave privada inválida (longitud incorrecta)');
        }

        // Derivar clave pública usando noble-secp256k1
        const pubkeyBytes = nobleSecp256k1.getPublicKey(privkeyHex, true); // compressed
        // Para Nostr usamos solo la coordenada X (32 bytes, sin prefijo 02/03)
        const pubkeyHex = bytesToHex(pubkeyBytes.slice(1));

        // Convertir a formato bech32
        const npub = hexToNpub(pubkeyHex);

        return {
            npub,
            nsec,
            pubkeyHex,
            privkeyHex
        };
    }

    // Importar identidad Nostr existente (pegar nsec)
    async function importNostrIdentity() {
        try {
            // Mostrar diálogo con dos opciones: importar nsec o vincular npub
            $("body").dialog({
                title: '<i class="fa fa-download"></i> Importar identidad Nostr',
                type: 'ajax',
                width: '600px',
                openAnimation: 'zoom',
                closeAnimation: 'fade',
                content: `/login/op=nostr_import_identity_dialog/html`,
                buttons: [
                    {
                        text: '<i class="fa fa-arrow-right"></i> Continuar',
                        class: 'btn btn-primary',
                        action: async function(event, overlay) {
                            const btn = event.target;
                            const originalText = btn.innerHTML;

                            // Detectar qué tab está activo (SimpleTabs añade clase st-active al panel activo)
                            const nsecTab = overlay.querySelector('#tab-content-import-nsec');
                            const nsecTabActive = nsecTab && nsecTab.classList.contains('st-active');

                            if (nsecTabActive) {
                                // === FLUJO 1: Importar nsec ===
                                const nsecInput = overlay.querySelector('#import-nsec-input');
                                const statusDiv = overlay.querySelector('#import-nsec-status');
                                const nsec = nsecInput.value.trim();

                                if (!nsec) {
                                    statusDiv.innerHTML = '<p style="color:#f44336;"><i class="fa fa-exclamation-circle"></i> Por favor, pega tu clave privada (nsec)</p>';
                                    return;
                                }

                                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';
                                btn.disabled = true;
                                statusDiv.innerHTML = '<p style="color:#666;"><i class="fa fa-cog fa-spin"></i> Derivando claves...</p>';

                                try {
                                    // Derivar claves desde nsec
                                    const keys = await deriveKeysFromNsec(nsec);

                                    // Confirmar con el usuario mostrando el npub
                                    statusDiv.innerHTML = `
                                        <div style="background:#e8f5e9;border:1px solid #81c784;border-radius:8px;padding:15px;">
                                            <p style="margin:0 0 10px 0;color:#2e7d32;font-weight:bold;">
                                                <i class="fa fa-check-circle"></i> Identidad verificada
                                            </p>
                                            <p style="margin:0 0 5px 0;font-size:0.85em;color:#388e3c;">
                                                Tu clave pública (npub):
                                            </p>
                                            <code style="display:block;background:#fff;padding:8px;border-radius:4px;word-break:break-all;font-size:0.7em;border:1px solid #ddd;margin-bottom:10px;">
                                                ${keys.npub}
                                            </code>
                                            <p style="margin:0;font-size:0.85em;color:#388e3c;">
                                                ¿Confirmas que esta es tu identidad?
                                            </p>
                                        </div>
                                    `;

                                    btn.innerHTML = '<i class="fa fa-check"></i> Confirmar y guardar';
                                    btn.disabled = false;
                                    btn.onclick = async function() {
                                        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
                                        btn.disabled = true;

                                        try {
                                            // Guardar en IndexedDB
                                            await saveNostrKeys(keys.npub, keys.nsec, keys.pubkeyHex, keys.privkeyHex);

                                            statusDiv.innerHTML = `
                                                <div style="background:#e8f5e9;border:1px solid #81c784;border-radius:8px;padding:15px;">
                                                    <p style="margin:0;color:#2e7d32;font-weight:bold;">
                                                        <i class="fa fa-check-circle"></i> ¡Identidad importada correctamente!
                                                    </p>
                                                </div>
                                            `;

                                            console.log('[importNostrIdentity] Identidad guardada en IndexedDB');

                                            // Si el usuario está logueado, vincular automáticamente
                                            <?php if (!empty($_SESSION['userid'])): ?>
                                            statusDiv.innerHTML += '<p style="margin-top:10px;color:#666;"><i class="fa fa-spinner fa-spin"></i> Vinculando a tu cuenta...</p>';

                                            const linkResult = await autoLinkNostrIdentity(keys);

                                            if (linkResult.success) {
                                                statusDiv.innerHTML = `
                                                    <div style="background:#e8f5e9;border:1px solid #81c784;border-radius:8px;padding:15px;">
                                                        <p style="margin:0 0 5px 0;color:#2e7d32;font-weight:bold;">
                                                            <i class="fa fa-check-circle"></i> ¡Identidad importada y vinculada!
                                                        </p>
                                                        <p style="margin:0;color:#388e3c;font-size:0.9em;">
                                                            Tu identidad Nostr ha sido asociada a tu cuenta correctamente.
                                                        </p>
                                                    </div>
                                                `;
                                            } else {
                                                console.warn('[importNostrIdentity] No se pudo vincular automáticamente:', linkResult.error);
                                            }
                                            <?php endif; ?>

                                            setTimeout(() => {
                                                document.body.removeChild(overlay);
                                                location.reload();
                                            }, 2000);

                                        } catch (error) {
                                            console.error('[importNostrIdentity] Error guardando:', error);
                                            statusDiv.innerHTML = `
                                                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:15px;">
                                                    <p style="margin:0;color:#c62828;">
                                                        <i class="fa fa-exclamation-triangle"></i> Error al guardar: ${error.message}
                                                    </p>
                                                </div>
                                            `;
                                            btn.innerHTML = originalText;
                                            btn.disabled = false;
                                        }
                                    };

                                } catch (error) {
                                    console.error('[importNostrIdentity] Error:', error);
                                    statusDiv.innerHTML = `
                                        <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:15px;">
                                            <p style="margin:0;color:#c62828;">
                                                <i class="fa fa-exclamation-triangle"></i> ${error.message}
                                            </p>
                                        </div>
                                    `;
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                }

                            } else {
                                // === FLUJO 2: Vincular npub con extensión ===
                                const npubInput = overlay.querySelector('#link-npub-input');
                                const statusDiv = overlay.querySelector('#link-npub-status');
                                const npub = npubInput.value.trim();

                                if (!npub) {
                                    statusDiv.innerHTML = '<p style="color:#f44336;"><i class="fa fa-exclamation-circle"></i> Por favor, pega tu clave pública (npub)</p>';
                                    return;
                                }

                                if (!npub.startsWith('npub1')) {
                                    statusDiv.innerHTML = '<p style="color:#f44336;"><i class="fa fa-exclamation-circle"></i> Formato inválido. Debe empezar con "npub1"</p>';
                                    return;
                                }

                                // Verificar que tiene extensión Nostr
                                if (typeof window.nostr === 'undefined') {
                                    statusDiv.innerHTML = `
                                        <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:15px;">
                                            <p style="margin:0 0 10px 0;color:#c62828;font-weight:bold;">
                                                <i class="fa fa-exclamation-triangle"></i> No se detectó extensión Nostr
                                            </p>
                                            <p style="margin:0;font-size:0.85em;color:#d32f2f;">
                                                Para usar este método necesitas tener instalada una extensión Nostr como
                                                <a href="https://getalby.com" target="_blank">Alby</a> o
                                                <a href="https://chromewebstore.google.com/detail/nos2x/kpgefcfmnafjgpblomihpgmejjdanjjp" target="_blank">nos2x</a>.
                                            </p>
                                        </div>
                                    `;
                                    return;
                                }

                                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Solicitando firma...';
                                btn.disabled = true;
                                statusDiv.innerHTML = '<p style="color:#666;"><i class="fa fa-info-circle"></i> Por favor, firma con tu extensión Nostr...</p>';

                                try {
                                    // Convertir npub a hex
                                    const pubkeyHex = npubToHex(npub);

                                    <?php if (!empty($_SESSION['userid'])): ?>
                                    // Si está logueado, solicitar challenge y firmar
                                    const challengeResp = await fetch('login/ajax/op=nostr_link_challenge', {
                                        method: 'POST'
                                    });
                                    const challengeData = await challengeResp.json();

                                    if (!challengeData.success) {
                                        throw new Error(challengeData.msg || 'Error obteniendo challenge');
                                    }

                                    // Crear evento para firmar
                                    const event = {
                                        kind: 22242,
                                        created_at: Math.floor(Date.now() / 1000),
                                        tags: [
                                            ['challenge', challengeData.challenge],
                                            ['domain', challengeData.domain]
                                        ],
                                        content: 'link_account',
                                        pubkey: pubkeyHex
                                    };

                                    // Calcular ID del evento
                                    event.id = await calculateEventId(event);

                                    // Solicitar firma a la extensión
                                    const signedEvent = await window.nostr.signEvent(event);

                                    // Enviar al backend
                                    const linkResp = await fetch('login/ajax/op=nostr_link', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                        body: new URLSearchParams({ event: JSON.stringify(signedEvent) })
                                    });
                                    const result = await linkResp.json();

                                    if (result.success) {
                                        statusDiv.innerHTML = `
                                            <div style="background:#e8f5e9;border:1px solid #81c784;border-radius:8px;padding:15px;">
                                                <p style="margin:0 0 5px 0;color:#2e7d32;font-weight:bold;">
                                                    <i class="fa fa-check-circle"></i> ¡Identidad vinculada!
                                                </p>
                                                <p style="margin:0;color:#388e3c;font-size:0.9em;">
                                                    ${result.msg}
                                                </p>
                                            </div>
                                        `;

                                        setTimeout(() => {
                                            document.body.removeChild(overlay);
                                            location.reload();
                                        }, 2000);
                                    } else {
                                        throw new Error(result.msg || result.error || 'Error al vincular');
                                    }
                                    <?php else: ?>
                                    // Si NO está logueado, solo mostrar que la clave es válida
                                    statusDiv.innerHTML = `
                                        <div style="background:#e8f5e9;border:1px solid #81c784;border-radius:8px;padding:15px;">
                                            <p style="margin:0 0 5px 0;color:#2e7d32;font-weight:bold;">
                                                <i class="fa fa-check-circle"></i> Clave pública válida
                                            </p>
                                            <p style="margin:0;color:#388e3c;font-size:0.9em;">
                                                Por favor, inicia sesión primero para vincular tu identidad Nostr.
                                            </p>
                                        </div>
                                    `;
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                    <?php endif; ?>

                                } catch (error) {
                                    console.error('[importNostrIdentity] Error vinculando npub:', error);
                                    statusDiv.innerHTML = `
                                        <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:15px;">
                                            <p style="margin:0;color:#c62828;">
                                                <i class="fa fa-exclamation-triangle"></i> ${error.message || 'Error al vincular identidad'}
                                            </p>
                                        </div>
                                    `;
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                }
                            }
                        }
                    },
                    {
                        text: '<i class="fa fa-times"></i> Cancelar',
                        class: 'btn btn-default',
                        action: function(event, overlay) {
                            document.body.removeChild(overlay);
                        }
                    }
                ],
                onLoad: function() {
                    // Inicializar SimpleTabs en el diálogo
                    /*. No es necsario, ya lo hace wquery.dialog.js :)
                    const tabsContainer = overlay.querySelector('#import-identity-tabs');
                    if (tabsContainer && typeof SimpleTabs !== 'undefined') {
                        new SimpleTabs(tabsContainer);
                    }
                    */



                    // Toggle mostrar/ocultar nsec
                    const checkbox = document.querySelector('#show-nsec-checkbox');
                    const input = document.querySelector('#import-nsec-input');

                    if (checkbox && input) {
                        checkbox.addEventListener('change', function() {
                            input.type = this.checked ? 'text' : 'password';
                        });
                    }
                    
                }
            });

        } catch (error) {
            console.error('[importNostrIdentity] Error al abrir diálogo:', error);
            alert('Error: ' + error.message);
        }
    }

    // Generar keypair Nostr usando Web Crypto API + secp256k1
    async function generateNostrKeypair() {
        // Cargar noble-secp256k1 dinámicamente si no está disponible
        if (typeof nobleSecp256k1 === 'undefined') {
            await loadNobleSecp256k1();
        }

        // Generar clave privada aleatoria (32 bytes)
        const privateKeyBytes = new Uint8Array(32);
        crypto.getRandomValues(privateKeyBytes);
        const privkeyHex = bytesToHex(privateKeyBytes);

        // Derivar clave pública usando noble-secp256k1
        const pubkeyBytes = nobleSecp256k1.getPublicKey(privkeyHex, true); // compressed
        // Para Nostr usamos solo la coordenada X (32 bytes, sin prefijo 02/03)
        const pubkeyHex = bytesToHex(pubkeyBytes.slice(1));

        // Convertir a formato bech32
        const npub = hexToNpub(pubkeyHex);
        const nsec = hexToNsec(privkeyHex);

        return { npub, nsec, pubkeyHex, privkeyHex };
    }

    // Cargar librería noble-secp256k1 desde archivo local (ES Module)
    function loadNobleSecp256k1() {
        return new Promise((resolve, reject) => {
            if (typeof nobleSecp256k1 !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.type = 'module';
            script.textContent = `
                import * as secp from '/_lib_/bitcoin/secp256k1.js';
                window.nobleSecp256k1 = secp;
                window.dispatchEvent(new Event('nobleSecp256k1Loaded'));
            `;

            const handler = () => {
                window.removeEventListener('nobleSecp256k1Loaded', handler);
                console.log('[loadNobleSecp256k1] ✓ Librería cargada desde archivo local (/_lib_/bitcoin/secp256k1.js)');
                resolve();
            };
            window.addEventListener('nobleSecp256k1Loaded', handler);

            script.onerror = () => {
                reject(new Error('Error al cargar noble-secp256k1 desde /_lib_/bitcoin/secp256k1.js'));
            };

            document.head.appendChild(script);

            // Timeout de seguridad
            setTimeout(() => {
                if (typeof nobleSecp256k1 === 'undefined') {
                    reject(new Error('Timeout: no se pudo cargar nobleSecp256k1'));
                }
            }, 5000);
        });
    }

    // Firmar evento Nostr con clave privada local (BIP-340 Schnorr)
    async function signNostrEventLocal(event, privkeyHex) {
        if (typeof nobleSecp256k1 === 'undefined') {
            await loadNobleSecp256k1();
        }

        // Serializar evento para hash (NIP-01)
        const serialized = JSON.stringify([
            0,
            event.pubkey,
            event.created_at,
            event.kind,
            event.tags,
            event.content
        ]);

        // Hash SHA-256
        const encoder = new TextEncoder();
        const data = encoder.encode(serialized);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashHex = bytesToHex(new Uint8Array(hashBuffer));

        // Firmar con Schnorr
        const signature = await nobleSecp256k1.schnorr.sign(hashHex, privkeyHex);
        const sigHex = bytesToHex(signature);

        return {
            ...event,
            id: hashHex,
            sig: sigHex
        };
    }

    // Flujo de registro con Nostr — muestra opciones y ejecuta el registro
    async function nostrRegisterFlow(btn) {
        const originalText = btn.html();
        const hasExtension = typeof window.nostr !== 'undefined';

        $("body").dialog({
            title: '<i class="fa fa-key"></i> Registro con Nostr',
            type: 'html',
            width: '460px',
            openAnimation: 'zoom',
            closeAnimation: 'fade',
            content: `
                <div style="padding:20px;">
                    <p style="margin-bottom:15px;color:#666;text-align:center;">¿Cómo quieres registrarte?</p>

                    <div id="nostr-reg-options" style="display:flex;flex-direction:column;gap:10px;">
                        <a id="nostr-reg-new" class="btn btn-success btn-block" style="padding:12px;text-align:left;">
                            <i class="fa fa-magic"></i> <strong>Crear nueva identidad Nostr</strong>
                            <br><small style="opacity:0.8;">Te generamos claves nuevas en segundos</small>
                        </a>

                        <a id="nostr-reg-nsec" class="btn btn-block" style="padding:12px;text-align:left;background:#8B5CF6;color:#fff;">
                            <i class="fa fa-key"></i> <strong>Ya tengo Nostr</strong>
                            <br><small style="opacity:0.8;">Importar mi clave privada (nsec)</small>
                        </a>

                        ${hasExtension ? `
                        <a id="nostr-reg-ext" class="btn btn-block" style="padding:12px;text-align:left;background:#f59e0b;color:#fff;">
                            <i class="fa fa-puzzle-piece"></i> <strong>Usar extensión del navegador</strong>
                            <br><small style="opacity:0.8;">Firmar con nos2x, Alby u otra extensión</small>
                        </a>
                        ` : ''}
                    </div>

                    <div id="nostr-reg-nsec-form" style="display:none;margin-top:15px;">
                        <p style="font-size:0.85em;color:#666;margin-bottom:8px;">Introduce tu clave privada Nostr:</p>
                        <input type="password" id="nostr-reg-nsec-input" class="form-control"
                            placeholder="nsec1..." style="font-family:monospace;margin-bottom:10px;">
                        <label style="font-size:0.8em;color:#888;cursor:pointer;">
                            <input type="checkbox" id="nostr-reg-show-nsec"> Mostrar clave
                        </label>
                        <a id="nostr-reg-nsec-submit" class="btn btn-success btn-block" style="margin-top:8px;">
                            <i class="fa fa-check"></i> Registrar con esta identidad
                        </a>
                        <a id="nostr-reg-nsec-back" class="btn btn-reset btn-block" style="margin-top:5px;font-size:0.85em;">
                            <i class="fa fa-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div id="nostr-reg-new-form" style="display:none;">
                        <div id="nostr-reg-new-loading" style="text-align:center;padding:20px;">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p style="margin-top:10px;color:#666;">Generando tu identidad Nostr...</p>
                        </div>
                        <div id="nostr-reg-new-content" style="display:none;">
                            <p style="font-size:0.9em;color:#27ae60;margin-bottom:15px;text-align:center;">
                                <i class="fa fa-check-circle"></i> <strong>¡Identidad Nostr generada!</strong>
                            </p>
                            <div style="margin-bottom:12px;">
                                <label style="font-size:0.85em;font-weight:bold;display:block;margin-bottom:4px;">Elige un alias o username:</label>
                                <input type="text" id="nostr-reg-username" 
                                    placeholder="mi_alias" style="margin-bottom:4px; width: 100%;padding: 10px 10px;width: -webkit-fill-available;" maxlength="30" autocomplete="off">
                                <small id="nostr-reg-username-feedback" style="font-size:0.8em;color:#999;">Mínimo 5 caracteres</small>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                <a id="nostr-reg-download" class="btn btn-block" style="padding:10px;background:#f59e0b;color:#fff;cursor:pointer;">
                                    <i class="fa fa-download"></i> Guardar claves en archivo
                                </a>
                                <a id="nostr-reg-continue" class="btn btn-success btn-block" style="padding:10px;pointer-events:none;opacity:0.5;cursor:default;"
                                    title="Primero guarda tus claves">
                                    <i class="fa fa-arrow-right"></i> Continuar con el registro
                                </a>
                                <a id="nostr-reg-new-back" class="btn btn-reset btn-block" style="font-size:0.85em;cursor:pointer;">
                                    <i class="fa fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            onLoad: function(dialog) {
                var el = dialog.overlay;
                var generatedKeys = null;
                var usernameValid = false;
                var keysDownloaded = false;

                function updateContinueBtn() {
                    var btn = $(el).find('#nostr-reg-continue');
                    if (usernameValid && keysDownloaded) {
                        btn.css({'pointer-events':'auto','opacity':'1','cursor':'pointer'}).attr('title','');
                    } else {
                        btn.css({'pointer-events':'none','opacity':'0.5','cursor':'default'});
                        if (!keysDownloaded) btn.attr('title', 'Primero guarda tus claves');
                        else btn.attr('title', 'Elige un username válido');
                    }
                }

                async function doRegister(method, nsecVal, preKeys, chosenUsername) {
                    dialog.close();
                    btn.html('<i class="fa fa-spinner fa-spin"></i> Registrando...');
                    btn.css('pointer-events', 'none');

                    try {
                        const chResp = await fetch('login/ajax/op=nostr_challenge', { method: 'POST' });
                        const chData = await chResp.json();
                        if(!chData.success) throw new Error(chData.msg || 'Error obteniendo challenge');

                        var signedEvent;
                        var usedKeys = null;

                        if (method === 'extension') {
                            const ev = { kind: 22242, created_at: Math.floor(Date.now()/1000), tags: [['challenge', chData.challenge],['domain', chData.domain]], content: '' };
                            signedEvent = await window.nostr.signEvent(ev);
                        } else if (method === 'nsec') {
                            usedKeys = await deriveKeysFromNsec(nsecVal);
                            await saveNostrKeys(usedKeys.npub, usedKeys.nsec, usedKeys.pubkeyHex, usedKeys.privkeyHex);
                            const ev = { pubkey: usedKeys.pubkeyHex, created_at: Math.floor(Date.now()/1000), kind: 22242, tags: [['challenge', chData.challenge],['domain', chData.domain]], content: '' };
                            signedEvent = await signNostrEventLocal(ev, usedKeys.privkeyHex);
                        } else {
                            usedKeys = preKeys || await generateNostrKeypair();
                            if (!preKeys) await saveNostrKeys(usedKeys.npub, usedKeys.nsec, usedKeys.pubkeyHex, usedKeys.privkeyHex);
                            const ev = { pubkey: usedKeys.pubkeyHex, created_at: Math.floor(Date.now()/1000), kind: 22242, tags: [['challenge', chData.challenge],['domain', chData.domain]], content: '' };
                            signedEvent = await signNostrEventLocal(ev, usedKeys.privkeyHex);
                        }

                        var vp = { event: JSON.stringify(signedEvent) };
                        if(window._nostr_invitation_code) vp.invitation_code = window._nostr_invitation_code;
                        if(chosenUsername) vp.username = chosenUsername;
                        const vResp = await fetch('login/ajax/op=nostr_verify', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams(vp) });
                        const res = await vResp.json();

                        if(res.success) {
                            // Re-guardar claves en IndexedDB con el userId real (la sesión ya está creada)
                            if (usedKeys) {
                                try {
                                    await saveNostrKeys(usedKeys.npub, usedKeys.nsec, usedKeys.pubkeyHex, usedKeys.privkeyHex);
                                    // Borrar entrada guest huérfana
                                    var db = await openNostrDB();
                                    var tx = db.transaction(NOSTR_STORE_NAME, 'readwrite');
                                    tx.objectStore(NOSTR_STORE_NAME).delete('guest');
                                } catch(e) { console.warn('[doRegister] No se pudo actualizar IndexedDB:', e); }
                            }

                            btn.html('<i class="fa fa-check"></i> ¡Bienvenido!');
                            if (res.is_new && method !== 'new') {
                                await alert('Tu nombre de usuario se ha generado automáticamente. Puedes cambiarlo cuando quieras desde tu perfil.');
                            }
                            window.location.href = res.redirect || '/';
                        } else {
                            throw new Error(res.msg || res.error || 'Verificación fallida');
                        }
                    } catch(err) {
                        console.error('Nostr register error:', err);
                        alert('Error: ' + err.message);
                        btn.html(originalText);
                        btn.css('pointer-events', '');
                    }
                }

                // Crear nueva identidad — mostrar formulario con username + descarga
                $(el).find('#nostr-reg-new').on('click', async function() {
                    $(el).find('#nostr-reg-options').hide();
                    $(el).find('#nostr-reg-new-form').show();
                    $(el).find('#nostr-reg-new-loading').show();
                    $(el).find('#nostr-reg-new-content').hide();

                    try {
                        generatedKeys = await generateNostrKeypair();
                        await saveNostrKeys(generatedKeys.npub, generatedKeys.nsec, generatedKeys.pubkeyHex, generatedKeys.privkeyHex);
                        $(el).find('#nostr-reg-new-loading').hide();
                        $(el).find('#nostr-reg-new-content').show();
                        $(el).find('#nostr-reg-username').focus();
                    } catch(err) {
                        alert('Error generando claves: ' + err.message);
                        $(el).find('#nostr-reg-new-form').hide();
                        $(el).find('#nostr-reg-options').show();
                    }
                });

                // Validación de username (debounced) — addEventListener nativo para evitar bug wquery con InputEvent.data
                var usernameTimer = null;
                var usernameInput = $(el).find('#nostr-reg-username')[0];
                if (usernameInput) usernameInput.addEventListener('input', function() {
                    var val = this.value.trim().toLowerCase();
                    var feedback = $(el).find('#nostr-reg-username-feedback');
                    usernameValid = false;

                    
                    updateContinueBtn();
                    clearTimeout(usernameTimer);

                    if (!val) {
                        feedback.html('<span style="color:#999;">Mínimo 5 caracteres</span>');
                        return;
                    }
                    if (val.length < 5) {
                        feedback.html('<span style="color:#999;">Mínimo 5 caracteres</span>');
                        return;
                    }
                    if (!/^[a-z][a-z0-9_]*$/.test(val)) {
                        feedback.html('<span style="color:#e74c3c;">Solo letras, números y guión bajo. Debe empezar por letra.</span>');
                        return;
                    }

                    feedback.html('<i class="fa fa-spinner fa-spin"></i> Verificando...');
                    usernameTimer = setTimeout(function() {

                        var email = val + '@' + window.location.hostname;

                        $.post('control_panel/ajax/op=method/table=<?=TB_USER?>/method=validate/field=user_email', {"value": email}, function(data) {
                            if (data.valid < 1) {
                                feedback.html('<span style="color:#e74c3c;"><i class="fa fa-times"></i> ' + (data.msg || 'No válido') + '</span>');
                            } else if (data.field > 0) {
                                feedback.html('<span style="color:#e74c3c;"><i class="fa fa-times"></i> Ya está en uso</span>');
                            } else {
                                usernameValid = true;
                                feedback.html('<span style="color:#27ae60;"><i class="fa fa-check"></i> Disponible</span>');
                            }
                            updateContinueBtn();
                        }, 'json').fail(function() {
                            feedback.html('<span style="color:#999;">No se pudo verificar</span>');
                            usernameValid = true;
                            updateContinueBtn();
                        });
                        

                    }, 500);
                });

                // Descargar claves a archivo
                $(el).find('#nostr-reg-download').on('click', function() {
                    if (!generatedKeys) return;

                    var now = new Date();
                    var fileContent = {
                        "generado": now.toISOString(),
                        "tipo": "Nostr Key Pair",
                        "advertencia": "\u26a0\ufe0f MANT\u00c9N ESTE ARCHIVO EN SECRETO. Quien tenga acceso a tu nsec puede actuar como si fueras t\u00fa.",
                        "npub": generatedKeys.npub,
                        "nsec": generatedKeys.nsec,
                        "pubkey_hex": generatedKeys.pubkeyHex
                    };

                    var blob = new Blob([JSON.stringify(fileContent, null, 2)], { type: 'application/json' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'nostr-keys-' + now.toISOString().slice(0,19).replace(/:/g,'-') + '.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);

                    var origHTML = this.innerHTML;
                    this.innerHTML = '<i class="fa fa-check"></i> \u00a1Guardado!';
                    var self = this;
                    setTimeout(function() { self.innerHTML = origHTML; }, 2000);

                    keysDownloaded = true;
                    updateContinueBtn();
                });

                // Continuar con el registro
                $(el).find('#nostr-reg-continue').on('click', function() {
                    if (!usernameValid || !keysDownloaded) {
                        if (!keysDownloaded) alert('\u26a0\ufe0f Primero debes guardar tus claves.\n\nHaz click en "Guardar claves en archivo".');
                        else alert('\u26a0\ufe0f Elige un username v\u00e1lido.');
                        return;
                    }
                    var username = $(el).find('#nostr-reg-username').val().trim().toLowerCase();
                    doRegister('new', null, generatedKeys, username);
                });

                // Volver a opciones desde formulario nueva identidad
                $(el).find('#nostr-reg-new-back').on('click', function() {
                    $(el).find('#nostr-reg-new-form').hide();
                    $(el).find('#nostr-reg-options').show();
                    generatedKeys = null;
                    keysDownloaded = false;
                    usernameValid = false;
                    $(el).find('#nostr-reg-username').val('');
                    $(el).find('#nostr-reg-username-feedback').html('<span style="color:#999;">Mínimo 5 caracteres</span>');
                    updateContinueBtn();
                });

                // Mostrar formulario nsec
                $(el).find('#nostr-reg-nsec').on('click', function() {
                    $(el).find('#nostr-reg-options').hide();
                    $(el).find('#nostr-reg-nsec-form').show();
                    $(el).find('#nostr-reg-nsec-input').focus();
                });

                // Toggle mostrar nsec
                $(el).find('#nostr-reg-show-nsec').on('change', function() {
                    var inp = $(el).find('#nostr-reg-nsec-input')[0];
                    inp.type = this.checked ? 'text' : 'password';
                });

                // Volver a opciones
                $(el).find('#nostr-reg-nsec-back').on('click', function() {
                    $(el).find('#nostr-reg-nsec-form').hide();
                    $(el).find('#nostr-reg-options').show();
                });

                // Enviar nsec
                $(el).find('#nostr-reg-nsec-submit').on('click', function() {
                    var nsecVal = $(el).find('#nostr-reg-nsec-input').val().trim();
                    if (!nsecVal) { alert('Introduce tu nsec'); return; }
                    if (!nsecVal.startsWith('nsec1')) { alert('La clave debe empezar con nsec1...'); return; }
                    doRegister('nsec', nsecVal);
                });

                // Usar extensión
                if (hasExtension) {
                    $(el).find('#nostr-reg-ext').on('click', function() {
                        doRegister('extension');
                    });
                }
            }
        });
    }

    // Comprobar si hay claves guardadas para el usuario actual
    async function checkLocalNostrKeys() {
        try {
            const keys = await getNostrKeys();
            return keys !== null;
        } catch {
            return false;
        }
    }

    // Buscar CUALQUIER clave Nostr en IndexedDB (para login)
    // Esta función busca en TODOS los registros, no solo del usuario actual
    async function findAnyNostrKeys() {
        try {
            const db = await openNostrDB();

            return new Promise((resolve, reject) => {
                const tx = db.transaction(NOSTR_STORE_NAME, 'readonly');
                const store = tx.objectStore(NOSTR_STORE_NAME);
                const request = store.getAll();

                request.onsuccess = () => {
                    const allKeys = request.result || [];
                    console.log(`[findAnyNostrKeys] Encontradas ${allKeys.length} identidad(es) en IndexedDB`);

                    // Si hay múltiples, devolver la más reciente
                    if (allKeys.length > 0) {
                        // Ordenar por fecha de creación (más reciente primero)
                        allKeys.sort((a, b) => {
                            const dateA = new Date(a.createdAt || 0);
                            const dateB = new Date(b.createdAt || 0);
                            return dateB - dateA;
                        });
                        resolve(allKeys[0]); // Devolver la más reciente
                    } else {
                        resolve(null);
                    }
                };
                request.onerror = () => reject(request.error);
            });
        } catch (error) {
            console.error('[findAnyNostrKeys] Error:', error);
            return null;
        }
    }

    // Borrar claves Nostr del usuario actual
    async function clearCurrentUserNostrKeys() {
        try {
            const db = await openNostrDB();
            const userId = await getCurrentUserId();
            const keyId = userId ? `user_${userId}` : 'guest';

            return new Promise((resolve, reject) => {
                const tx = db.transaction(NOSTR_STORE_NAME, 'readwrite');
                const store = tx.objectStore(NOSTR_STORE_NAME);
                const request = store.delete(keyId);

                tx.oncomplete = () => {
                    console.log(`[clearCurrentUserNostrKeys] Claves borradas para: ${keyId}`);
                    resolve(true);
                };
                tx.onerror = () => reject(tx.error);
            });
        } catch (error) {
            console.error('[clearCurrentUserNostrKeys] Error:', error);
            throw error;
        }
    }

    // Login con clave Nostr local (guardada en IndexedDB del usuario actual)
    async function loginWithLocalNostrKey() {
        try {
            const keys = await getNostrKeys();
            if (!keys) {
                alert('No se encontró identidad Nostr guardada');
                return;
            }

            await loginWithSpecificNostrKey(keys);
        } catch (err) {
            console.error('Error in loginWithLocalNostrKey:', err);
            alert('Error al hacer login: ' + err.message);
        }
    }

    // Login con claves específicas (usado internamente)
    async function loginWithSpecificNostrKey(keys) {
        try {
            console.log('[loginWithSpecificNostrKey] Logging in with Nostr key:', keys.npub);

            // Obtener challenge del servidor
            const challengeResponse = await fetch('login/ajax/op=nostr_challenge_for_pubkey', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'pubkey=' + encodeURIComponent(keys.pubkeyHex)
            });

            const challengeData = await challengeResponse.json();

            if (!challengeData.success) {
                alert(challengeData.msg || 'Error obteniendo challenge');
                return;
            }

            // Crear evento de autenticación
            const authEvent = {
                pubkey: keys.pubkeyHex,
                created_at: Math.floor(Date.now() / 1000),
                kind: 22242,
                tags: [
                    ['challenge', challengeData.challenge],
                    ['domain', window.location.hostname]
                ],
                content: ''
            };

            // Firmar localmente
            const signedEvent = await signNostrEventLocal(authEvent, keys.privkeyHex);

            console.log('Signed event:', signedEvent);

            // Verificar en servidor
            var _vbody = 'event=' + encodeURIComponent(JSON.stringify(signedEvent));
            if(window._nostr_invitation_code) _vbody += '&invitation_code=' + encodeURIComponent(window._nostr_invitation_code);
            const verifyResponse = await fetch('login/ajax/op=nostr_verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: _vbody
            });

            const verifyData = await verifyResponse.json();

            if (verifyData.success) {
                if (typeof notify !== 'undefined') {
                    notify(verifyData.msg || '¡Bienvenido!', 'success', 2000);
                } else {
                    alert(verifyData.msg || '¡Bienvenido!');
                }
                setTimeout(() => {
                    window.location.href = verifyData.redirect || '/';
                }, 1000);
            } else {
                alert(verifyData.msg || 'Error de verificación');
            }

        } catch (err) {
            console.error('Error in loginWithSpecificNostrKey:', err);
            alert('Error al hacer login: ' + err.message);
        }
    }

    // Buscar clave en IndexedDB por pubkey exacto (para login multi-usuario)
    async function findNostrKeyByPubkey(pubkeyHex) {
        try {
            const db = await openNostrDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction(NOSTR_STORE_NAME, 'readonly');
                const store = tx.objectStore(NOSTR_STORE_NAME);
                const request = store.getAll();
                request.onsuccess = () => {
                    const allKeys = request.result || [];
                    const match = allKeys.find(k => k.pubkeyHex === pubkeyHex);
                    resolve(match || null);
                };
                request.onerror = () => reject(request.error);
            });
        } catch (err) {
            console.error('[findNostrKeyByPubkey] Error:', err);
            return null;
        }
    }

    // Verificar evento firmado y hacer login (helper compartido)
    async function verifyNostrAndLogin(signedEvent) {
        var body = 'event=' + encodeURIComponent(JSON.stringify(signedEvent));
        if (window._nostr_invitation_code) body += '&invitation_code=' + encodeURIComponent(window._nostr_invitation_code);
        const resp = await fetch('login/ajax/op=nostr_verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        });
        const result = await resp.json();
        if (result.success) {
            if (typeof notify !== 'undefined') notify(result.msg || '¡Bienvenido!', 'success', 2000);
            setTimeout(() => { window.location.href = result.redirect || '/'; }, 1000);
        } else {
            throw new Error(result.msg || result.error || 'Verificación fallida');
        }
    }

    // Login con Nostr buscando por email/username
    async function loginNostrByUsername(btn, originalText, username) {
        btn.html('<i class="fa fa-spinner fa-spin"></i> Buscando...');
        btn.css('pointer-events', 'none');
        try {
            // 1. Buscar pubkey del usuario por email/username
            const resp = await fetch('login/ajax/op=nostr_challenge_for_user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'username=' + encodeURIComponent(username)
            });
            const data = await resp.json();
            if (!data.success) throw new Error(data.msg || 'Error buscando usuario');

            // 2. Buscar clave correspondiente en IndexedDB
            const localKey = await findNostrKeyByPubkey(data.pubkey_hex);

            if (localKey) {
                btn.html('<i class="fa fa-spinner fa-spin"></i> Firmando...');
                const authEvent = {
                    pubkey: data.pubkey_hex,
                    created_at: Math.floor(Date.now() / 1000),
                    kind: 22242,
                    tags: [['challenge', data.challenge], ['domain', data.domain]],
                    content: ''
                };
                const signedEvent = await signNostrEventLocal(authEvent, localKey.privkeyHex);
                await verifyNostrAndLogin(signedEvent);
                return;
            }

            // 3. Intentar extensión NIP-07
            if (typeof window.nostr !== 'undefined') {
                btn.html('<i class="fa fa-spinner fa-spin"></i> Firmando con extensión...');
                const extPubkey = await window.nostr.getPublicKey();
                if (extPubkey !== data.pubkey_hex) {
                    throw new Error('La extensión Nostr tiene una clave diferente (' + extPubkey.slice(0,8) + '...) a la de ' + username + ' (' + data.pubkey_hex.slice(0,8) + '...)');
                }
                const authEvent = {
                    kind: 22242,
                    created_at: Math.floor(Date.now() / 1000),
                    tags: [['challenge', data.challenge], ['domain', data.domain]],
                    content: ''
                };
                const signedEvent = await window.nostr.signEvent(authEvent);
                await verifyNostrAndLogin(signedEvent);
                return;
            }

            // 4. No se encontraron claves
            throw new Error('No se encontraron claves Nostr para "' + username + '" en este navegador. Necesitas tener las claves guardadas o una extensión con la clave correcta.');

        } catch (err) {
            console.error('Nostr login by username error:', err);
            if (typeof error !== 'undefined') error('Error: ' + err.message);
            else alert('Error: ' + err.message);
        } finally {
            btn.html(originalText);
            btn.css('pointer-events', '');
        }
    }

    // Descargar claves Nostr como archivo de texto
    function downloadNostrKeys() {
        const npub = document.getElementById('new-npub').textContent;
        const nsec = document.getElementById('new-nsec').textContent;
        const date = new Date().toISOString().split('T')[0];
        const hostname = window.location.hostname;

        const content_es = `═══════════════════════════════════════════════════════════════\n`
                      + `CLAVES NOSTR - ¡GUARDA ESTE ARCHIVO EN LUGAR SEGURO!\n`
                      + `═══════════════════════════════════════════════════════════════\n`
                      + `\n`
                      + `📅 Fecha de creación: ${date}\n`  
                      + `🌐 Creado en: ${hostname}\n`
                      + `\n`
                      + `───────────────────────────────────────────────────────────────\n`
                      + `🔓 CLAVE PÚBLICA (npub) - Puedes compartirla\n`
                      + `───────────────────────────────────────────────────────────────\n`
                      + `${npub}\n`     
                      + `\n`
                      + `───────────────────────────────────────────────────────────────\n`
                      + `🔐 CLAVE PRIVADA (nsec) - ¡¡NUNCA LA COMPARTAS!!\n`   
                      + `───────────────────────────────────────────────────────────────\n`
                      + `${nsec}\n`
                      + `\n`
                      + `═══════════════════════════════════════════════════════════════\n`
                      + `⚠️  IMPORTANTE:\n`  
                      + `    - La clave privada (nsec) es como tu contraseña\n`
                      + `    - Quien la tenga puede hacerse pasar por ti\n`
                      + `    - Si la pierdes, pierdes tu identidad para siempre\n` 
                      + `    - Guarda este archivo en un lugar seguro (USB, papel, etc.)\n`
                      + `\n`
                      + `📚 Aprende más: https://nostrfacil.com https://nostr.how/es\n`
                      + `═══════════════════════════════════════════════════════════════\n`;
        const content_en = `═══════════════════════════════════════════════════════════════\n`
                      + `NOSTR KEYS- ¡SAVE THIS FILE IN A SAFE PLACE!\n`
                      + `═══════════════════════════════════════════════════════════════\n`
                      + `\n`
                      + `📅 Date created: ${date}\n`  
                      + `🌐 Created at: ${hostname}\n`
                      + `\n`
                      + `───────────────────────────────────────────────────────────────\n`
                      + `🔓 PUBLIC KEY (npub) - You can share it\n`
                      + `───────────────────────────────────────────────────────────────\n`
                      + `${npub}\n`     
                      + `\n`
                      + `───────────────────────────────────────────────────────────────\n`
                      + `🔐 PRIVATE KEY (nsec) - NEVER SHARE IT!!\n`   
                      + `───────────────────────────────────────────────────────────────\n`
                      + `${nsec}\n`
                      + `\n`
                      + `═══════════════════════════════════════════════════════════════\n`
                      + `⚠️  IMPORTANT:\n`  
                      + `    - The private key (nsec) is like your password\n`
                      + `    - Whoever has it can impersonate you\n`
                      + `    - If you lose it, you lose your identity forever\n` 
                      + `    - Save this file in a safe place (USB, paper, etc.)\n`
                      + `\n`
                      + `📚 Learn more: https://nostrfacil.com https://nostr.how/en\n`
                      + `═══════════════════════════════════════════════════════════════\n`;
        const content = <?= $_SESSION['lang']==='en' ? 'content_en' : 'content_es' ?>;
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `nostr-keys-${date}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        notify('Archivo descargado - ¡Guárdalo en lugar seguro!', 'success', 3000);
    }

    // Vincular automáticamente identidad Nostr recién creada a la cuenta actual
    async function autoLinkNostrIdentity(keys) {
        try {
            console.log('[autoLinkNostrIdentity] Iniciando vinculación automática...');

            // 1. Obtener challenge del servidor
            const challengeResp = await fetch('login/ajax/op=nostr_link_challenge', {
                method: 'POST'
            });
            const challengeData = await challengeResp.json();

            if (!challengeData.success) {
                throw new Error(challengeData.msg || 'Error obteniendo challenge');
            }

            console.log('[autoLinkNostrIdentity] Challenge obtenido');

            // 2. Crear evento para firmar
            const event = {
                kind: 22242,
                created_at: Math.floor(Date.now() / 1000),
                tags: [
                    ['challenge', challengeData.challenge],
                    ['domain', challengeData.domain]
                ],
                content: 'link_account',
                pubkey: keys.pubkeyHex
            };

            // 3. Firmar evento con la clave privada recién generada
            const signedEvent = await signNostrEventLocal(event, keys.privkeyHex);

            console.log('[autoLinkNostrIdentity] Evento firmado');

            // 4. Enviar evento firmado al servidor
            const linkResp = await fetch('login/ajax/op=nostr_link', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ event: JSON.stringify(signedEvent) })
            });
            const result = await linkResp.json();

            if (result.success) {
                console.log('[autoLinkNostrIdentity] ✓ Vinculación exitosa:', result.msg);
                return { success: true, message: result.msg };
            } else {
                throw new Error(result.msg || result.error || 'Error al vincular');
            }

        } catch (error) {
            console.error('[autoLinkNostrIdentity] Error:', error);
            return { success: false, error: error.message };
        }
    }

    // Crear identidad Nostr (función global disponible en login y perfil)
    async function createNostrIdentity() {
        // Mostrar diálogo de progreso
        $("body").dialog({
            title: '<i class="fa fa-magic"></i> Creando tu identidad Nostr',
            type: 'html',
            width: '550px',
            openAnimation: 'zoom',
            closeAnimation: 'fade',
            content: `
                <div style="padding:20px;text-align:center;">
                    <div id="nostr-create-progress">
                        <i class="fa fa-spinner fa-spin fa-3x" style="color:#8B5CF6;"></i>
                        <p style="margin-top:15px;color:#666;">Generando claves criptográficas...</p>
                    </div>
                    <div id="nostr-create-result" style="display:none;">
                        <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:15px;margin-bottom:15px;">
                            <i class="fa fa-check-circle fa-2x" style="color:#4caf50;"></i>
                            <p style="margin:10px 0 0;color:#2e7d32;font-weight:bold;">¡Identidad Nostr creada!</p>
                        </div>

                        <div style="text-align:left;background:#f5f5f5;border-radius:8px;padding:15px;margin-bottom:15px;">
                            <p style="margin:0 0 5px;font-size:0.9em;color:#333;"><strong>🔓 Clave Pública</strong> <span style="color:#888;font-size:0.8em;">(npub)</span></p>
                            <p style="margin:0 0 8px;font-size:0.75em;color:#666;">Esta es tu "usuario" en Nostr. Puedes compartirla públicamente.</p>
                            <code id="new-npub" style="display:block;background:#fff;padding:8px;border-radius:4px;word-break:break-all;font-size:0.72em;border:1px solid #ddd;"></code>
                            <button onclick="navigator.clipboard.writeText(document.getElementById('new-npub').textContent);notify('Clave pública copiada','success',2000);" class="btn btn-sm" style="margin-top:8px;"><i class="fa fa-copy"></i> Copiar</button>
                        </div>

                        <div style="text-align:left;background:#fff3e0;border-radius:8px;padding:15px;margin-bottom:15px;">
                            <p style="margin:0 0 5px;font-size:0.9em;color:#e65100;"><strong>🔐 Clave Privada</strong> <span style="color:#bf360c;font-size:0.8em;">(nsec)</span> <strong>- ¡GUÁRDALA!</strong></p>
                            <p style="margin:0 0 8px;font-size:0.75em;color:#666;">Esta es tu "contraseña". <strong>NUNCA la compartas</strong>. Quien la tenga controla tu identidad.</p>
                            <code id="new-nsec" style="display:block;background:#fff;padding:8px;border-radius:4px;word-break:break-all;font-size:0.72em;border:1px solid #ffcc80;"></code>
                            <button onclick="navigator.clipboard.writeText(document.getElementById('new-nsec').textContent);notify('Clave privada copiada - ¡Guárdala en lugar seguro!','warning',3000);" class="btn btn-sm btn-warning" style="margin-top:8px;"><i class="fa fa-copy"></i> Copiar</button>
                            <button onclick="downloadNostrKeys();" class="btn btn-sm btn-info" style="margin-top:8px;margin-left:5px;"><i class="fa fa-download"></i> Guardar archivo</button>
                        </div>

                        <div style="background:#e3f2fd;border:1px solid #90caf9;border-radius:8px;padding:12px;margin-bottom:15px;text-align:left;">
                            <p style="margin:0;font-size:0.8em;color:#1565c0;">
                                <i class="fa fa-info-circle"></i> <strong>¿Qué es Nostr?</strong><br>
                                <span style="color:#1976d2;">Un protocolo descentralizado donde TÚ controlas tu identidad.
                                Estas claves te pertenecen y funcionan en cientos de apps.</span><br>
                                <a href="https://nostr.how/es" target="_blank" style="color:#1565c0;text-decoration:underline;">
                                    Aprende más sobre Nostr →
                                </a>
                            </p>
                        </div>

                        <p style="font-size:0.8em;color:#888;">
                            <i class="fa fa-mobile"></i> Apps compatibles: Damus, Amethyst, Primal, Snort, Nostrudel...
                        </p>
                    </div>
                </div>
            `,
            buttons: [
                {
                    text: '<i class="fa fa-check"></i> Cerrar',
                    class: 'btn btn-success',
                    action: function(event, overlay) {
                        document.body.removeChild(overlay);
                        <?php if (!empty($_SESSION['userid'])): ?>
                        // Si el usuario está logueado, recargar para mostrar el nuevo estado
                        location.reload();
                        <?php endif; ?>
                    }
                }
            ]
        });

        try {
            // Generar keypair
            const keys = await generateNostrKeypair();

            // Guardar en IndexedDB
            await saveNostrKeys(keys.npub, keys.nsec, keys.pubkeyHex, keys.privkeyHex);

            // Mostrar resultado
            document.getElementById('nostr-create-progress').style.display = 'none';
            document.getElementById('nostr-create-result').style.display = 'block';
            document.getElementById('new-npub').textContent = keys.npub;
            document.getElementById('new-nsec').textContent = keys.nsec;

            console.log('Nostr identity created:', keys.npub);

            // Si el usuario está logueado, vincular automáticamente la identidad a la cuenta
            <?php if (!empty($_SESSION['userid'])): ?>
            document.getElementById('nostr-create-progress').style.display = 'block';
            document.getElementById('nostr-create-progress').innerHTML = `
                <i class="fa fa-spinner fa-spin fa-2x" style="color:#8B5CF6;"></i>
                <p style="margin-top:15px;color:#666;">Vinculando identidad a tu cuenta...</p>
            `;
            document.getElementById('nostr-create-result').style.display = 'none';

            const linkResult = await autoLinkNostrIdentity(keys);

            document.getElementById('nostr-create-progress').style.display = 'none';
            document.getElementById('nostr-create-result').style.display = 'block';

            if (linkResult.success) {
                // Añadir mensaje de éxito de vinculación
                const successDiv = document.querySelector('#nostr-create-result > div:first-child');
                successDiv.innerHTML = `
                    <i class="fa fa-check-circle fa-2x" style="color:#4caf50;"></i>
                    <p style="margin:10px 0 0;color:#2e7d32;font-weight:bold;">¡Identidad Nostr creada y vinculada!</p>
                    <p style="margin:5px 0 0;color:#388e3c;font-size:0.85em;">Tu identidad ha sido asociada a tu cuenta correctamente.</p>
                `;
                console.log('[createNostrIdentity] ✓ Identidad vinculada automáticamente');
            } else {
                // Mostrar advertencia si la vinculación falló
                const warningDiv = document.createElement('div');
                warningDiv.style.cssText = 'background:#fff3e0;border:1px solid #ffb74d;border-radius:8px;padding:12px;margin-bottom:15px;';
                warningDiv.innerHTML = `
                    <p style="margin:0;font-size:0.85em;color:#e65100;">
                        <i class="fa fa-exclamation-triangle"></i> <strong>Advertencia:</strong>
                        La identidad se creó pero no se pudo vincular automáticamente a tu cuenta.
                    </p>
                    <p style="margin:5px 0 0;font-size:0.8em;color:#666;">
                        Puedes vincularla manualmente recargando la página y usando el botón "Vincular esta identidad a mi cuenta".
                    </p>
                `;
                document.getElementById('nostr-create-result').insertBefore(
                    warningDiv,
                    document.getElementById('nostr-create-result').firstChild.nextSibling
                );
                console.warn('[createNostrIdentity] ⚠ Error vinculando:', linkResult.error);
            }
            <?php endif; ?>

        } catch (error) {
            console.error('Error creating Nostr identity:', error);
            document.getElementById('nostr-create-progress').innerHTML = `
                <i class="fa fa-exclamation-triangle fa-3x" style="color:#f44336;"></i>
                <p style="margin-top:15px;color:#c62828;">Error al crear identidad: ${error.message}</p>
            `;
        }
    }
    
    
    </script>
    <?php

    if($_SESSION['message_error']){
        ?>
        <script type="text/javascript">
            ready(function (){
              //shake(document.querySelector('.login-login'),20,true);
                document.querySelectorAll('#loginformXX,#registerform,.login-login').forEach((el, i) => {shake(el)});
              //document.querySelectorAll('i,img,p,a,b,li').forEach((el, i) => {shake(el)});
            }); 
            </script>
        <?php 
    }

    // ========================================================================
    // PASSWORDLESS: Guardar claves pendientes después de verificar email
    // ========================================================================
    if ($verify == 'ok' && $_SESSION['userid']) {
        ?>
        <script>
        // Esperar a que el DOM y passwordless.js estén cargados
        window.addEventListener('DOMContentLoaded', async function() {
            console.log('[VERIFY] Verificando claves pendientes...');

            // Verificar si hay claves passwordless pendientes de guardar
            // Usamos localStorage porque el link de verificación puede abrirse en otra pestaña
            const pendingData = localStorage.getItem('pendingPasswordlessKeys');
            console.log('[VERIFY] pendingData:', pendingData ? 'SÍ existe' : 'NO existe');

            if (!pendingData) {
                console.log('[VERIFY] No hay claves pendientes en localStorage');
                return;
            }

            if (typeof PasswordlessAuth === 'undefined') {
                console.error('[VERIFY] ERROR: PasswordlessAuth no está definido');
                console.log('[VERIFY] Reintentando en 1 segundo...');

                // Reintentar después de 1 segundo
                setTimeout(async () => {
                    if (typeof PasswordlessAuth !== 'undefined') {
                        console.log('[VERIFY] PasswordlessAuth ahora disponible');
                        await guardarClavesPendientes();
                    } else {
                        console.error('[VERIFY] PasswordlessAuth sigue sin estar disponible');
                    }
                }, 1000);
                return;
            }

            await guardarClavesPendientes();
        });

        async function guardarClavesPendientes() {
            try {
                const pendingData = localStorage.getItem('pendingPasswordlessKeys');
                const data = JSON.parse(pendingData);
                // IMPORTANTE: Forzar userId a número para consistencia con IndexedDB
                const userId = parseInt(<?=$_SESSION['userid']?>, 10);

                console.log('[VERIFY] ===== GUARDANDO CLAVES PENDIENTES =====');
                console.log('[VERIFY] userId:', userId, '(type:', typeof userId, ')');
                console.log('[VERIFY] deviceId:', data.deviceId);
                console.log('[VERIFY] keys.signPubB64 (primeros 50 chars):', data.keys.signPubB64?.substring(0, 50));
                console.log('[VERIFY] keys.signPrivB64 exists:', !!data.keys.signPrivB64);

                // Guardar claves en IndexedDB
                await PasswordlessAuth.saveKeys(data.keys, data.deviceId, userId);

                // Verificar que se guardaron correctamente
                const savedKeys = await PasswordlessAuth.loadKeysFromIndexedDB(userId);
                console.log('[VERIFY] Verificación - claves recuperadas de IndexedDB:', savedKeys ? 'SÍ' : 'NO');
                if (savedKeys) {
                    console.log('[VERIFY] savedKeys.deviceId:', savedKeys.deviceId);
                    console.log('[VERIFY] savedKeys.signPublicKey (primeros 50 chars):', savedKeys.signPublicKey?.substring(0, 50));
                    console.log('[VERIFY] Claves coinciden:', savedKeys.signPublicKey === data.keys.signPubB64 ? 'SÍ' : 'NO');
                }

                // Limpiar localStorage
                localStorage.removeItem('pendingPasswordlessKeys');

                console.log('[VERIFY] ✓ Claves guardadas correctamente en IndexedDB');
                console.log('[VERIFY] ===== FIN =====');

                // Actualizar mensaje para usuario
                const msgEl = document.querySelector('p[style*="margin:40px"]');
                if (msgEl) {
                    msgEl.innerHTML += '<br><br><span style="color: #8B5CF6; font-weight: bold;">✓ Login sin contraseña activado en este dispositivo</span>';
                }
            } catch (error) {
                console.error('[VERIFY] ERROR al guardar claves:', error);
            }
        }
        </script>
        <?php
    }


?>
<style>
table.header{display:none;}  /* Hide html header in 'My account' tab */
</style>
