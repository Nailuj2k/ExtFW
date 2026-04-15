<?php 

class Captcha{
    
    public static function create( $min = 0, $max = 4 ){
            $_captcha['nums']     = [t('ZERO','cero'),t('ONE','uno'),t('TWO','dos'),t('THREE','tres'),t('FOUR','cuatro'),t('FIVE','cinco'),t('SIX','seis'),t('SEVEN','siete'),t('EIGHT','ocho'),t('NINE','nueve'),t('TEN','diez'),t('ELEVEN','once'),t('TWUELVE','doce'),t('THIRTEEN','trece'),t('FOURTEEN','catorce'),t('FIFTEEN','quince')];
            $_captcha['ssquares'] = [1=>t('ONE','uno'),4=>t('FOUR','cuatro'),9=>t('NINE','nueve'),16=>t('SIXTEEN','dieciseis'),25=>t('TWENTY-FIVE','veinticinco'),36=> t('THIRTY-SIX','treintayseis'),42=>t('FORTY-NINE','cuarentaynueve'),64=>t('SIXTY-FOUR','sesentaycuatro'),81=>t('EIGHTY-ONE','ochentayuno'),100=>t('HUNDRED','cien')];
            $_captcha['help']     = t('SPAM_PROTECTION','Protección anti-spam');
            $_captcha['min']      = 1;
            $_captcha['max']      = 13;
            $_captcha['ops']      = ['+','-','x','sqrt','**'];
            $_captcha['opst']     = [t('PLUS','mas'),t('MINUS','menos'),t('PER','por'),t('SQUARE_ROOT_OF','raiz cuadrada de'),t('SQUARED', 'al cuadrado')];
            $_captcha['op']       = rand($min,$max); // rand(0,$dificultad)
            // Test minus op with same numbers: $_captcha['op2'] =  $_captcha['op1'];
            if ($_captcha['op']==4){
                $_captcha['op1'] = rand($_captcha['min'],9);
                $_captcha['label'] = $_captcha['nums'][$_captcha['op1']].' '.$_captcha['opst'][$_captcha['op']];
                $_SESSION['captcha']=pow(intval($_captcha['op1']),2);
            }else if ($_captcha['op']==3){
                $_captcha['op1'] = array_keys($_captcha['ssquares'])[rand(0,9)];
                $_captcha['label'] = $_captcha['opst'][$_captcha['op']].' '.$_captcha['ssquares'][$_captcha['op1']];
                $_SESSION['captcha']=sqrt($_captcha['op1']);
            }else{
                $_captcha['op1'] = rand($_captcha['min'],$_captcha['max']);
                $_captcha['op2'] = ($_captcha['op']==1)?rand($_captcha['min'],$_captcha['op1']-1):rand($_captcha['min'],$_captcha['max']);
                if ($_captcha['op1']==$_captcha['op2']){if($_captcha['op']==1) $_captcha['op1']=$_captcha['op1']+1;} 
                $_captcha['label'] = $_captcha['nums'][$_captcha['op1']].' '.$_captcha['opst'][$_captcha['op']].' '.$_captcha['nums'][$_captcha['op2']]; 
                if     ($_captcha['op']==0) $_SESSION['captcha']=$_captcha['op1']+$_captcha['op2'];
                else if($_captcha['op']==1) $_SESSION['captcha']=$_captcha['op1']-$_captcha['op2'];
                else if($_captcha['op']==2) $_SESSION['captcha']=$_captcha['op1']*$_captcha['op2'];
                else                        $_SESSION['captcha']=false;
            }
            return $_captcha;
    }

    public static function check($value){
       return ($_SESSION['captcha']==$value);  // add check token
    }

}



/*******

How to use:

1. In a form:
            include(SCRIPT_DIR_CLASSES.'/captcha.class.php'); 
            $MyCaptcha = Captcha::create();
            ?>
            <label for="captcha" title="<?=$MyCaptcha['help']?>">Resolver</label>
            <input type="text" id="captcha" name="captcha" placeholder="<?=$MyCaptcha['label']?>">
            <?php

2. When receive POST:

             $allow = Captcha::check($_POST['captcha']);


**/

