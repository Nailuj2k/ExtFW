/**
@name jQuery pwdMeter 1.0.1
@author Shouvik Chatterjee (mailme@shouvik.net)
@date 31 Oct 2010
@modify 31 Dec 2010
@license Free for personal and commercial use as long as the author's name retains
*/
(function($){

$.fn.pwdMeter = function(options){


        options = $.extend({
        
                minLength: 8,
                displayGeneratePassword: false,
            //    generatePassText: 'Password Generator',
            //    generatePassClass: 'GeneratePasswordLink',
                randomPassLength: 13,
                passwordBox: this,
                displayText: true
        
        }, options);


        return this.each(function(index){
        
                $(this).keyup(function(){
                        console.log('keyup')
                        evaluateMeter();
                });
                
                //var updateMeter = function(width, background, text) {
                   // $('.password-background').css({"width": width, "background-color": background});
                   // $('.password-background').css({"width": width, "background-color": background});
                    //$('.strength').text('Strength: ' + text).css('color', background);
                //}
                
                function evaluateMeter(){

                        var passwordStrength   = 0;
                        var password = $(options.passwordBox).val();
 
                        console.log('evaluateMeter',password)
                        
                        // Validar que password no sea null, undefined o vacío
                        if (!password) {
                            password = '';
                        }

                        passwordStrength=0;  
                        if ((password.length >0) && (password.length <=5)) passwordStrength++;                
                        if (password.length >= options.minLength) passwordStrength++;
                        if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/)) ) passwordStrength++;
                        if (password.match(/\d+/)) passwordStrength++;  //4
                        if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))        passwordStrength++;  //5
                        if (password.length > 12) passwordStrength++;

                      console.log('passwordStrength',password.length,passwordStrength);

                        $('#pwdMeter').removeClass();
                        $('#pwdMeter').addClass('neutral');
        
                        $(options.passwordBox).attr('strength',passwordStrength);
                        
                        switch(passwordStrength){
                        case 1:
                          $('#pwdMeter').addClass('veryweak');
                          if(options.displayText) $('#pwdMeter').text('Very Weak');
                          //updateMeter("20%", "#ffa0a0", "very weak");
                          break;
                        case 2:
                          $('#pwdMeter').addClass('weak');
                          if(options.displayText) $('#pwdMeter').text('Weak');
                          //updateMeter("40%", "#ffb78c", "weak");
                          break;
                        case 3:
                          $('#pwdMeter').addClass('medium');
                          if(options.displayText) $('#pwdMeter').text('Medium');
                          //updateMeter("60%", "#ffec8b", "medium");
                          break;
                        case 4:
                          $('#pwdMeter').addClass('strong');
                          if(options.displayText) $('#pwdMeter').text('Strong');
                          //updateMeter("80%", "#c3ff88", "strong");
                          break;
                        case 5:
                          $('#pwdMeter').addClass('verystrong');
                          if(options.displayText) $('#pwdMeter').text('Very Strong');
                          //updateMeter("100%", "#ACE872", "very strong");
                          break;                                                      
                        default:
                          $('#pwdMeter').addClass('neutral');
                          if(options.displayText) $('#pwdMeter').text('Very Weak');
                          //updateMeter("0%", "#ffa0a0", "none");
                        }                
                
                }
        /***
	        
	         $('#newpassword').on('propertychange change keyup paste input', function() {
    // TODO: only use the first 128 characters to stop this from blocking the browser if a giant password is entered
    var password = $(this).val();
    var passwordScore = zxcvbn(password)['score'];
    
    var updateMeter = function(width, background, text) {
      $('.password-background').css({"width": width, "background-color": background});
      $('.strength').text('Strength: ' + text).css('color', background);
    }
    
    if (passwordScore === 0) {
      if (password.length === 0) {
        updateMeter("0%", "#ffa0a0", "none");
      } else {
        updateMeter("20%", "#ffa0a0", "very weak");
      }
    }
    if (passwordScore == 1) updateMeter("40%", "#ffb78c", "weak");
    if (passwordScore == 2) updateMeter("60%", "#ffec8b", "medium");
    if (passwordScore == 3) updateMeter("80%", "#c3ff88", "strong");
    if (passwordScore == 4) updateMeter("100%", "#ACE872", "very strong"); // Color needs changing
    
  });

	        
	        ***/        
        
        
        
        
        
        
        
                /*********
                if(options.displayGeneratePassword){
                        $('#pwdMeter').after('&nbsp;<span id="Spn_PasswordGenerator" class="'+options.generatePassClass+'">'+ options.generatePassText +'</span>&nbsp;<span id="Spn_NewPassword" class="NewPassword"></span>');
                }
                
                $('#Spn_PasswordGenerator').click(function(){
                        var randomPassword = random_password();
                        $('#Spn_NewPassword').text(randomPassword);
                        $(options.passwordBox).val(randomPassword);
                        evaluateMeter();
                });
                
                
                function random_password() {
                        var allowed_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz!?$?%^&*()_-+={[}]:;@~#|\<,>.?/";
                        var pwd_length = options.randomPassLength;
                        var rnd_pwd = '';
                        for (var i=0; i<pwd_length; i++) {
                                var rnd_num = Math.floor(Math.random() * allowed_chars.length);
                                rnd_pwd += allowed_chars.substring(rnd_num,rnd_num+1);
                        }
                        return rnd_pwd;
                }
                *********/
        
        });

}

})(jQuery)