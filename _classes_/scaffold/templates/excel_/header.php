<?php

     require_once '../../_classes_/PHPOffice/PHPExcel.php';

?>
<style type="text/css"  media="screen,print">
#excel_link{font-family:Arial,helvetica;max-height:160px;overflow:auto;margin-bottom:10px;text-align:center;}
 .sombra{/* border: 1px solid #827C71; padding:40px;background-color:#ffffff;-webkit-box-shadow:3px 3px 13px #777; -moz-box-shadow: 3px 3px 13px #777; box-shadow: 3px 3px 13px #777;*/}
  #print_content{/* width:620px;min-height:450px;border:1px solid #d9d9d9;font-size:12px;margin: 0 auto; */}
 .modalformBox{ width:500px; background: #fff; position: relative;margin:10% auto;padding: 30px;
   min-height:150px;border:1px solid #d9d9d9;font-size:12px;
  -moz-border-radius: 0px; border-radius: 0px;  
  -webkit-box-shadow: 0 3px 20px rgba(0,0,0,0.6); -moz-box-shadow: 0 3px 20px rgba(0,0,0,0.6);box-shadow: 0 3px 20px rgba(0,0,0,0.6);
  background: -moz-linear-gradient(#ffffff, #f9f9f9);
  background: -webkit-gradient(linear, right bottom, right top, color-stop(1, #ffffff), color-stop(0.57,#f9f9f9));
  background-color:white;
  text-shadow:mone;
  overflow:hidden;
}
#excel_link a{text-decoration:none;}
.dialog-buttons{position:absolute; right:30px; bottom:15px;}

      
    </style>
    
    
    <div id="excel_link"><br /><br /><br /> <i class="fa fa-file-excel-o"></i> <!-- id="print_content" class="sombra normal"-->


<?php       
          /////////////////////////////  echo $this->format_item['begin'];                                                    // move to print/body.php
