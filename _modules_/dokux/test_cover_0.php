
        <h1>BuscaPortadas</h1>
        <section id="form">
            <p>No hace falta rellenarlo todo ni ser muy exacto. Deja a google que haga su trabajo....... :)</p>

            <form method="post" action="https://intranet.hulamm.sms.carm.es/hulamm_ware/test">
                <label>Título: </label><input name="title" placeholder="título del libro" type="text" value="<?=$_POST['title']?>"/><br/>
                <label>Autor: </label><input name="author" placeholder="nombre del autor" type="text" value="<?=$_POST['author']?>"/><br/>
                <label>IMDB: </label><input name="imdb" placeholder="imdb" type="text" value="<?=$_POST['imdb']?>"/><br/>
                <input name="submit" class="submit" type="submit" value="Buscar"/>
            </form>

        </section>
<style>
    
.cover{display:inline-table;width:200px;height:200px;margin:5px;border:3px solid black;text-align:center;vertical-align:top;}
.cover img{border:1px solid #dedede;}
.cover.norl{opacity:0.2;}
</style>
        <section id="result">

            <?php

                function valid_cover($image){
                   return ($image->height > 60)  && ($image->width > 40) && ($image->height > $image->width) && (!strpos($image->src, 'google')>0); 
                }
                function valid_url($url){
                   return ( strpos($url->href, 'imgurl')>0 );
                }

                if ($_POST){
                    if($_POST['title']||$_POST['author']||$_POST['imdb']){
/**********
$url = "https://api.spotify.com/v1/search?q=ADELE+HELLO&type=track,artist";
echo $url;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:x.x.x) Gecko/20041107 Firefox/x.x");
$output = curl_exec($ch);
curl_close($ch);

$get_json  = json_decode($output);
$cover     = $get_json->images[1];
echo $cover;  
}}
******/

/***********OKIS****/
                if ($_POST){
                    if($_POST['title']||$_POST['author']||$_POST['imdb']){
                        //https://stackoverflow.com/questions/9813273/web-scraping-in-php
                        include SCRIPT_DIR_LIB.'/simplehtmldom/simple_html_dom.php';
                        $search_query = implode(' ',[$_POST['title'],$_POST['author'],$_POST['imdb']]);
                        $search_query = urlencode( $search_query );
                        $url_search = "https://www.google.com/search?q={$search_query}&tbm=isch"; // 
                      //$url_search = "https://duckduckgo.com/?q={$search_query}&iax=images&ia=images"; // 
                        $html = file_get_html($url_search);
                        $images = $html->find('img.t0fcAb');
                        //  $urls = $html->find('a');
/*******/
                        /**
                        $url_count = 10; //Enter the amount of images to be shown
                        $u = 0;
                        foreach($urls as $url){
                            if (valid_url($url)){ 
                                $u++;
                                if($u == $url_count) break;
                                ?><pre><?=$url->href?></pre><?
                            }
                        }
                        **/

/*************************************OKIS**/


            ini_set('memory_limit', '1024M');


//Vars::debug_var($url_search);

                        $image_count = 13; //Enter the amount of images to be shown
                        $i = 0;
                        foreach($images as $image){
                                //if (valid_cover($image)){ 
                                    if($i == $image_count) break;
                                    $i++;
                                    echo '<div class="cover"><img class="img" title="'.$image->src.'" src="'.$image->src.'"><br />'//.$image->height.'x'.$image->width.'<br />'
                                        //. $image->src.' '.$image->class.' '.$image->width.' '.$image->height.'<br />'
                                         . '<span class="dim"></span><br>'
                                         . '<button class="save_cover">Guardar</button></div>';
                                      
                                   // Vars::debug_var($image);
                                     $html->clear();
                               //}
                        }
                    }            
                }

/************/

            ?>
        </section>
<script>
$(function() {     

$('.img').each(function(i,j){
    let w = $(j).width();
    let h = $(j).height();
    let title = $(j).attr('title');
    let r = 0.2;  
    let dif = Math.abs(w-h);  
    let ratio = Math.abs(w/h).toFixed(2);  
    let okis = ratio>(1-r)&&ratio<(1+r);
    
    if(!okis) {
        $(j).closest('.cover').addClass('norl').find('.save_cover').hide(); //.html('okis');
        
    }
    $(j).closest('.cover').find('.dim').html(w+' x '+h+' ['+dif+'] '+' ['+ratio+'] ' );
    console.log(i,w,h);
});

});
</script>








<?php


/********************************
//Las cautivas que fueron botín de Patroclo y Aquiles  por la puerta, afligidas, salieron lanzando gemidos  y rodearon a Aquiles magnánimo y se golpearon 30  con las manos el pecho y sintieron vacíos los miembros.  Gimió Antíloco y se echó a llorar, y en las manos tenía  las de Aquiles, pues su corazón generoso en suspiros  se partía, temiendo que el hierro su cuello cortara. Exhaló un cruel gemido y lo oyó su augustísima madre,  que en el fondo del mar, junto al padre, se hallaba sentada,  y al oírlo lloró.
$googleRealURL = 'https://www.google.com/search?hl=en&biw=1360&bih=652&tbs=isz:lt,islt:svga,itp:photo&tbm=isch&sa=1&q=la+cosa&oq=la+cosa&gs_l=psy-ab.12...0.0.0.10572.0.0.0.0.0.0.0.0..0.0....0...1..64.psy-ab..0.0.0.wFdNGGlUIRk';

// Call Google with CURL + User-Agent
$ch = curl_init($googleRealURL);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux i686; rv:20.0) Gecko/20121230 Firefox/20.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$google = curl_exec($ch);   
$array_imghtml = explode("\"ou\":\"", $google); //the big url is inside JSON snippet "ou":"big url"
foreach($array_imghtml as $key => $value){
  if ($key > 0) {
    $array_imghtml_2 = explode("\",\"", $value);
    $array_imgurl[] = $array_imghtml_2[0];
  }
}
echo '<pre>';
var_dump($array_imgurl); //array contains the urls for the big images
echo '</pre>';

**************/












/*
$ch = $_REQUEST['url']; //curl_init('http://example.com/image.php');
$fp = $_REQUEST['image']; //fopen('/my/folder/flower.gif', 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fclose($fp);
*/