<?
Header('Content-type: text/html');
Header('charset: utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">

<?
if($message_error){
  echo $message_error;
}else{
?>

<head>
    <title><?=$_VAR_name?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="../../_themes_/<?=$_SESSION['theme']?>/print.css" />
    <style type="text/css" media="print">
      .print_hide {
        visibility:hidden;
        display:none;
      }

      #print_print_button{width:900px;padding:20px;text-align:center;margin: 0 auto;}

      #print_content{ width:900px;border:2px solid #eee;font-family:Arial,helvetica;font-size:12px;margin: 0 auto; }
      #print_content div{margin-top: 30px; text-align: center;}

      #print_logo {margin-bottom: 50px;text-align: left;}

      #print_header{ border-left: 90px solid white; border-right: 90px solid white; }
      #print_title{float:left;}
      #print_title strong{color:#666;display:block;text-align: left;}
      #print_title h1{font-size:20px;margin-top:10px;}

      #print_reference {float:right;}
      #print_reference strong{color:#666;display:block;text-align:right;}
      #print_reference p{font-size:20px;font-weight:bold;margin-top:10px;}

      #print_foto {text-align: center;}
      #print_foto .center{border:4px solid #c9c9c9; margin: 30px auto 0 auto;}

      #print_text { }
      #print_text p{margin: 0 90px 0 90px; text-align: left;}

      #print_thumbs {text-align: center;}
      #print_thumbs p{ margin: 0 auto 0 auto;}
      #print_thumbs p img{border:4px solid #c9c9c9;

      #print_footer {  }
      #print_footer p{ text-align:center; margin: 0 auto 0 auto; }
    </style>
</head>

<body>
    
    <div id="print_print_button" class="print_hide">
      <input type="button" value="Print this page" onclick="window.print(); return false;" />
    </div>
    
    <div id="print_content">
      
      <div id="print_logo">
        <img src="../../media/fotos/images/logo.png" />
      </div>
            
      <div id="print_header">
        
        <div id="print_title">
          <strong>VIVIENDA</strong>
          <h1><?=$_VAR_name?></h1>
        </div>
        
        <div id="print_reference">
          <strong>REFERENCIA</strong>
          <p><?=$_VAR_reference?></p>
        </div>
        
      </div>
      
      <div id="print_foto">
        <p><br /><img class="center" src="../../<?=$_VAR_foto?>"></p>
      </div>
      
      <div id="print_text">
        <p><?=$_VAR_intro.$_VAR_text?></p>
      </div>
      
      <div id="print_list">
        <ul>
          <li><strong><?=t('Tipo de vivienda')?></strong><span><?=t($_VAR_type)?></span></li>
          <li><strong><?=t('Dormitorios / Baños')?></strong><span><?=$_VAR_rooms?> / <?=$_VAR_baths?></span></li>
          <li><strong><?=t('Superficie')?></strong><span><?=$_STR_sup?></span></li>
          <li><strong><?=t('Zona turística')?></strong><span><?=$_VAR_region?></span></li>
          <li><strong><?=t('Zona')?></strong><span><?=$_VAR_zone?></span></li>
          <li><strong><?=t('Localización')?></strong><span><?=$_STR_location?></span></li>
          <li><strong><?=t('Estado')?></strong><span><?=($_VAR_used) ? t('Segunda mano') : (($_VAR_keyinhand) ? t('Llave en mano') : t('Nuevo')) ?></span></li>
          
          <li><strong><?=t('Características')?></strong>
            <div class="extras">
            <? if ($_VAR_pool!='0')      {?><span><?=t('Piscina '.$_STR_pool)?></span><? } ?>
            <? if ($_VAR_garage=='1')    {?><span><?=t('Aparcamiento')?></span><? } ?>
            <? if ($_VAR_furnished=='1') {?><span><?=t('Amueblado')?></span><? } ?>
            <? if ($_VAR_furnished=='2') {?><span><?=t('Parcialmente amueblado')?></span><? } ?>
            <? if ($_VAR_garden=='1')    {?><span><?=t('Jardín')?></span><? } ?>
            <? if ($_VAR_solarium=='1')  {?><span><?=t('Solárium')?></span><? } ?>
            <? if ($_VAR_alarm=='1')     {?><span><?=t('Alarma')?></span><? } ?>
            <? if ($_VAR_grills=='1')    {?><span><?=t('Rejas')?></span><? } ?>
            <? if ($_VAR_air=='1')       {?><span><?=t('Aire Acondicionado')?></span><? } ?>
            <? if ($_VAR_heating=='1')   {?><span><?=t('Calefacción')?></span><? } ?>
            <? if ($_VAR_store=='1')     {?><span><?=t('Trastero')?></span><? } ?>
            <? if ($_VAR_utility=='1')   {?><span><?=t('Lavadero')?></span><? } ?>
            <? if ($_VAR_fireplace=='1') {?><span><?=t('Chimenea')?></span><? } ?>
            <? if ($_VAR_whitegoods=='1'){?><span><?=t('Electrodomésticos')?></span><? } ?>
            </div>
          </li>
        </ul>
      </div>      
          
      <div>
        <h2>Precio: <?=$_STR_price?></h2>
      </div>
      
      <div id="print_thumbs">
        <p><?=$images_code?></p>
      </div>
      
      <div id="print_footer">
        <p><? echo get_translated_item('footer'); ?></p>    
      </div>
      
    </div> <!-- /print_content -->

</body>
  
</html>
<?
}
?>