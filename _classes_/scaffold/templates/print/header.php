<?php 


 // include('../../configuration.php');

?>

    <style type="text/css"  media="screen,print">
      #print_content{font-family:Arial,helvetica;}
      html,body{min-width:800px;}
      .sombra{/* border: 1px solid #827C71; padding:40px;background-color:#fff;-webkit-box-shadow:3px 3px 13px #777; -moz-box-shadow: 3px 3px 13px #777; box-shadow: 3px 3px 13px #777;*/}
      #print_print_button{width:800px;padding:15px;text-align:center;margin: 0 auto;}
      #print_content{ /*width:550px;*/ width:90%; min-height:650px;/*border:1px solid #d9d9d9;*/font-size:12px;margin: 0 auto; }
      #print_content>div{margin-top: 30px; text-align: left;}
      #print_logo { width:800px;margin-bottom: 50px;text-align: left;}
      #print_logo img{ height:45px;}
      #print_title h1{font-size:20px;margin-top:10px;}
      #print_footer {  }
      #print_footer p{ text-align:center; margin: 0 auto 0 auto; }
      .zebra {font-size: 1.1em; border-collapse:collapse; line-height: .95em;text-align: left;width: 100%;border: 1px solid #DEEFFE; /* #ddeeff;*/}
      .zebra th {font-weight: bold;background-color: #acf;border-bottom: 1px solid #0AA6D6; padding: 2px;}
      .odd {background-color: #ddeeff; }
      .even {background-color: #fcfeff;/* #DEEFFE; */}
      .zebra td,
      .zebra th{padding: 2px;font-size: .8em;border:1px solid #ddeeff;}
      .zebra th{border:1px solid #13C5F9;/*#0AA6D6;*/}
      .zebra td{border:1px solid #bcdeFF;/*#AACCFF;*//*#0AA6D6;*/}
    </style>
    
    <style type="text/css" media="print">
      .print_hide { visibility:hidden; display:none;   }
      *{font-family:Arial,helvetica;}
    </style>


    
    <div id="print_print_button" class="print_hide">
      <input type="button" value="Imprimir esta página" onclick="window.print(); return false;" />
      <input type="button" value="cerrar" onclick="window.close(); return false;" />
    </div>
    
    <div id="print_content" class="sombra normal">
<!---->
      <div id="print_logo" style="display:none">
        <img src="/media/images/logo.png" />
      </div>
          <!--  -->
      <div id="print_header">
      </div>
      

<?php       
      echo  CFG::$vars['templates']['view']['header'];
      echo $this->format_item['begin'];                                                    // move to print/body.php
      
      
