<?php

                    //BEGIN links
                    $sql_links = $_SESSION['userid']
                               ? "SELECT  l.ID,l.NAME,l.DESCRIPTION,l.LOGO,l.LINK,l.FIXED,l.LAST_UPDATE_DATE,c.CLICKS FROM CFG_LINKS l LEFT JOIN CFG_CLICKS c ON l.ID=c.ID_ITEM  AND c.T4BLE_NAME='CFG_LINKS' AND c.ID_USER={$_SESSION['userid']} WHERE l.ACTIVE='1' ORDER BY l.FIXED DESC, c.CLICKS DESC"
                             //: "SELECT  l.ID,l.NAME,l.DESCRIPTION,l.LOGO,l.LINK,SUM(c.CLICKS) AS CLICKS FROM CFG_LINKS l LEFT JOIN CFG_CLICKS c ON l.ID=c.ID_ITEM AND c.T4BLE_NAME='CFG_LINKS' WHERE l.ACTIVE='1'  GROUP BY c.ID_ITEM ORDER BY CLICKS DESC";
                               : "SELECT ID,NAME,DESCRIPTION,LOGO,LINK,FIXED,LAST_UPDATE_DATE FROM CFG_LINKS WHERE ACTIVE='1' ORDER BY FIXED DESC, CLICKS DESC";  

                    $rows_links = Table::sqlQuery($sql_links); 
                    if($rows_links){
                        ?><div class="inner items-links xmini" id="items-links"><!--<h3>Enlaces de interés</h3>--><?php
                        $hide_link = false; $n_link=0;
                        foreach ($rows_links as $row_links){
                            $ver = hash('crc32b',$row_links['LAST_UPDATE_DATE']);   
                            if(++$n_link>14) $hide_link =true;
                            ?><div id="item-link-<?=Str::sanitizeName($row_links['NAME'])?>" class="item-link shadow <?=$hide_link?'hideable_link hidden_link':''?>" title="<?=$row_links['DESCRIPTION']?>"   data-tb="CFG_LINKS" data-link="<?=$row_links['LINK']?>" data-item="<?=$row_links['ID']?>">
                            <div class="item-link-img"><img src="<?=SCRIPT_DIR_MEDIA?>/links/logos/<?=$row_links['LOGO']?>?v=<?=$ver?>"></div>
                            <?php if(1==2){?>
                            <div class="item-link-text"><a <?=strpos('ttps',$row_links['LINK'])>0?'target="new"':''?> data-tb="CFG_LINKS" data-link="<?=$row_links['LINK']?>" data-item="<?=$row_links['ID']?>"><?=$row_links['NAME']?></a><br /><?=$row_links['DESCRIPTION']?></div>
                            <?php } ?>
                            <div class="item-link-text"><span  class="title"><?=$row_links['NAME']?></span><br /><?=$row_links['DESCRIPTION']?></div>
                            </div><?php
                        }
                        ?><div class="inner buttons-bar">
                             <a class="btn btn-small btn-primary" id="th-large"><i class="fa fa-th"></i></a> 
                             <?php if(++$n_link>15) { ?> <a class="btn btn-small btn-primary" id="more_links">Ver mas aplicaciones ...</a><?php } ?>
                        </div></div><script>
                            let view_all=false;
                            let th_large=false;
                            //$(function(){ 

                                // Click in item 
                                $('#items-links .item-link').click( function(){  
                                    console.log($(this))
                                    link_click( $(this) ); 
                                });

                                // Click only in item title
                                //$('#items-links .item-link-text a').click( function(){  
                                //    link_click( $(this) ); 
                                //}); 

                            //}); 
                            <?php if(++$n_link>5) { ?>
                                $('#more_links').click(function(){ 
                                    view_all =!view_all; 
                                    $(this).text(view_all?'Ver menos ...':'Ver mas...'); 
                                    $('.item-link.hideable_link').toggleClass('hidden_link'); 
                                }); 
                            <?php } ?>
                                $('#th-large').click(function(){ 
                                    th_large =!th_large; 
                                    $('#items-links').toggleClass('mini');
                                    $(this).find('.fa').toggleClass('fa-th').toggleClass('fa-th-large'); 
                                }); 
                        </script><?php 
                    }
                    //END links
