<?php
    
    Header('Content-type: text/html');
    Header('charset: utf-8'); 
 
    include(SCRIPT_DIR_MODULE.'/index.php');
?>

<script type="text/javascript" src="/<?=SCRIPT_DIR_JS.'/jquery/jquery-3.5.1.min.js'?>"></script>
<script type="text/javascript" src="/<?=SCRIPT_DIR_JS.'/jquery/qrcode.min.js'?>"></script>


<?php
//Vars::debug_var($_SERVER);

$url = SCRIPT_HOST.dirname($_SERVER['REQUEST_URI']);

?>
<div style="text-align:center;margin:20px auto;max-width:256px;">
<p><?=$url?></p>
<div  style="display:inline;text-align:center;" id="qrcode"></div>
<script type="text/javascript">
//new QRCode(document.getElementById("qrcode"), "<?=$url?>");
/*******/
var qrcode = new QRCode(document.getElementById("qrcode"), {
	text: "<?=$url?>",
	width: 256,
	height: 256,
	colorDark : "#000000",
	colorLight : "#ffffff",
	correctLevel : QRCode.CorrectLevel.H
});
/**/
</script>
</div>
