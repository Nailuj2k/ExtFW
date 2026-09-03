<?php 

$this->html_pdf_header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style>
    /*
    @import url(\'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap\');
    @font-face {font-family: "Montserrat Thin";      src:local("Montserrat Thin"),      local("Montserrat-Thin"),      url(_fonts_/Montserrat-Thin.ttf)       format("truetype");}
    @font-face {font-family: "Montserrat ExtraLight";src:local("Montserrat ExtraLight"),local("Montserrat-ExtraLight"),url(_fonts_/Montserrat-ExtraLight.ttf) format("truetype");}
    @font-face {font-family: "Montserrat Light";     src:local("Montserrat Light"),     local("Montserrat-Light"),     url(_fonts_/Montserrat-Light.ttf)      format("truetype");}
    @font-face {font-family: "Montserrat Regular";   src:local("Montserrat Regular"),   local("Montserrat-Regular"),   url(_fonts_/Montserrat-Regular.ttf)    format("truetype");}
    @font-face {font-family: "Montserrat Medium";    src:local("Montserrat Medium"),    local("Montserrat-Medium"),    url(_fonts_/Montserrat-Medium.ttf)     format("truetype");}
    @font-face {font-family: "Montserrat SemiBold";  src:local("Montserrat SemiBold"),  local("Montserrat-SemiBold"),  url(_fonts_/Montserrat-SemiBold.ttf)   format("truetype");}
    @font-face {font-family: "Montserrat Bold";      src:local("Montserrat Bold"),      local("Montserrat-Bold"),      url(_fonts_/Montserrat-Bold.ttf)       format("truetype");}
    @font-face {font-family: "Montserrat ExtraBold"; src:local("Montserrat ExtraBold"), local("Montserrat-ExtraBold"), url(_fonts_/Montserrat-ExtraBold.ttf)  format("truetype");}
    @font-face {font-family: "Montserrat Black";     src:local("Montserrat Black"),     local("Montserrat-Black"),     url(_fonts_/Montserrat-Black.ttf)      format("truetype");}
    html {margin:0px;padding:0px;}
    body { background-color:#fffffe;  width:100%; padding:0px;margin:0;font-family: \'Open Sans\', sans-serif;font-size:12px;}
    * {padding: 0;margin:0;font-family: \'Open Sans\', sans-serif;}
    h1,h2,h3,h4{font-family: \'Montserrat Bold\';color:#000000;margin:20px;font-weight:700;}
    div,li,p,span{font-family: \'Montserrat Light\'; color:#000000;margin:20px;}
    #watermark {position: fixed;bottom:0px;left:0px;top:0px;right:0px;z-index:-1000;}
    img{max-width:100%;}
    ul{list-style-type:bullet;margin:5px 0 0 40px;}
    #top{position:relative;height: 140px;width:789px; border:0px solid red;background-color:transparent;padding:0;}
    #top #logo{height: 50px;position:absolute;left:20px;bottom:20px;}
    #top #logo-r{height: 36px;position:absolute;left:585px;bottom:20px;}
    table{border-collapse:collapse;margin:0;}
    table tr th{border-bottom:2px solid black;font-family: \'Montserrat Medium\';font-weight:normal;}
    table tr td{border-bottom:1px solid #444;font-family: \'Montserrat Light\'; }
    table tr th,
    table tr td{margin:0;padding:3px 5px;color:#000000;}
    */
   .page {page-break-after: always;position:relative; width:100%;}
   .footer { position: fixed; bottom: 5px; left:5px; right:5px;height:26px;}
   .pagenum:before { content: counter(page)  }
</style>
</head>';


//  https://makitweb.com/how-to-set-different-font-family-in-dompdf/

$this->html_pdf_header_00 = '';
$this->html_pdf_header_BAK = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style>
* { color: #000033;  font-family: "Helvetica Neue",Helvetica,Arial,"verdana", "sans-serif";}
hr{margin:0px;}
body { color: #000033; margin: 0px; font-size:12px;}
h1 {  font-weight:normal; font-size: 1.7em;  color: #114C8D;  font-style: italic;margin:60px auto;}
h2 {  font-weight:normal; font-size: 1.6em;  color: #114C8D;margin-top:60px auto;}
h3 {  font-weight:normal; font-size: 1.5em;  color: #114C8D;margin-top:50px auto;}
h4 {  font-weight:normal; font-size: 1.2em;  color: #114C8D;text-transform: capitalize;}
img {   border: none;}
img.border {  border: 1px solid #114C8D;}

.page { width:90%; margin:30px auto 30px auto;page-break-after: always;position:relative; }
.page:last-child{page-break-after: avoid;}
.header { position: fixed; top: 10px; }
.footer { position: fixed; bottom: 155px; }

.footer-pagenum{display:block;text-align:right;}
.pagenum:before { content: counter(page)  }
.pre{ font-size:1.1em;}
.pre h4,
.pre h5{text-transform: capitalize; }
.info{color:silver;font-weight:normal;font-size:0.9em;line-height:1em;}

.zebra {line-height: 1.0em;text-align: left;width: 100%;border: 0px solid #DEEFFE;/* border-collapse:collapse;*/}
.zebra  td,
.zebra  th{padding: 4px;/*font-family: Courier;*/font-size:1em;}
.zebra .even,
.zebra .even td {background-color: #f3f3f3; }
.zebra .odd,
.zebra .odd  td {background-color: #e1e3e3; }
.zebra .group_footer td{background-color: #efefef; font-weight:100;}
.zebra .group_header_1,
.zebra .group_header_1 th{background-color: #efefef; font-weight:100;}
.zebra .group_header_2,
.zebra .group_header_2 th{background-color: #efefef;}
.zebra .zebra .group_header_2, .group_header_2 .text{text-align:left;font-weight:bold;}
.zebra .group_header_2,
.zebra .group_header_2 .num{color:#aaaaaa;font-weight:normal;}
.zebra .group_footer_1,
.zebra .group_footer_1 td{background-color: #eceff0; font-weight:100;}
.zebra .group_footer_2,
.zebra .group_footer_2 td{background-color: #f3f5f6; font-weight:100;}
.zebra .group_footer_1 .text{/*font-weight:bold;*/}
.zebra th.num {width:90px;text-align:right; padding-right:22px; }
.zebra td.num {width:90px;text-align:right; padding-right:30px; }
.zebra td.text,
.zebra th.text {text-align:right; padding-right:50px;text-transform: capitalize; }

.zebra {width:100%;} 
.zebra td{/*border:2px solid black;*/text-align:center;padding:3px 0;} 

.zebra {font-size: 0.95em; line-height: .95em;text-align: left;width: 100%;border: 0px solid #DEEFFE; border-collapse:collapse; }
.zebra th {font-weight: bold;background-color: #acf; padding: 2px;}
.zebra .odd {background-color: #ddeeff; }
.zebra .even {background-color:#ECF5FF; }
.zebra td,
.zebra th{padding: 2px;font-size: .8em;border:1px solid #ddeeff;}
.zebra select{ margin:0 0 0 5px;margin:0px; padding: 0px;}
.zebra select{line-height:.8em;font-size:0.8em;border:1px solid #ccc;height: 16px; background-color:#F9F9F9;}
.zebra th{border:1px solid #13C5F9;}
.zebra td{border:1px solid #bcdeFF;}

.zebra td.key {width:33%;}

.zebra th.key,
.zebra td.key{width:125px !important;}
.zebra th,
.zebra td,
.zebra td p,
.zebra td>*{font-size:10px !important;}
.zebra td p{line-height:1em !important;margin:2px !important;}

.zebra-detail {line-height: 1.0em;text-align: left;width: 100%;border: 0px solid #DEEFFE;}
.zebra-detail td,
.zebra-detail th{padding: 4px 22px;font-size:1em;}
.zebra-detail .even,
.zebra-detail .even td {background-color: #f3f3f3; }
.zebra-detail .odd, 
.zebra-detail .odd  td {background-color: #e1e3e3; }
.zebra-detail .group_footer td{background-color: #efefef; font-weight:100;}
.zebra-detail .group_header_1,
.zebra-detail .group_header_1 th{background-color: #efefef; font-weight:100;}
.zebra-detail .group_header_2,
.zebra-detail .group_header_2 th{background-color: #efefef;}
.zebra-detail .group_header_2,
.zebra-detail .group_header_2 .text{text-align:left;font-weight:bold;}
.zebra-detail .group_header_2,
.zebra-detail .group_header_2 .num{color:#aaaaaa;font-weight:normal;}
.zebra-detail .group_footer_1, 
.zebra-detail .group_footer_1 td{background-color: #eceff0; font-weight:100;}
.zebra-detail .group_footer_2,
.zebra-detail .group_footer_2 td{background-color: #f3f5f6; font-weight:100;}

.zebra-detail .group_footer_1 .text,
.zebra-detail .group_footer_2 .text{color:#aaaaaa; font-weight:100;}

.zebra-detail th.num {/*width:40px;*/text-align:right;/* padding-right:22px; */}
.zebra-detail td.num {/*width:40px;*/text-align:right;/* padding-right:22px; */}
.zebra-detail td.text,
.zebra-detail th.text {text-align:right; /*padding-right:50px;*/text-transform: capitalize; }
.zebra-detail td{text-align:center;/*padding:3px 0;*/} 

</style>
</head>';
/*
$this->html_pdf_content  = '<body>'.'PDF Content'.'</body>';
$this->html_pdf_footer  = '</html>';
$this->html_pdf_detail = '<p>line</p>'
*/



/****************
if ($this->html_pdf_page_num) 
   $this->html_pdf_content  = '<body>'.''.'<div class="footer">Página: <span class="pagenum"></span></div></body>';
else
   $this->html_pdf_content  = '<body>'.''.'</body>';

$this->html_pdf_footer  = '</html>';


$this->html_pdf_detail = '<p>line</p>'
**********/
?>