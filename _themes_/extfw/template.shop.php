<?php
     


//$shop_header = ''; //'<div id="header-shop"></div>';
$shop_list_footer = '<div style="display:block;width:100%;height:40px;"></div>';
$shop_list_header = '<div id="items">';

if(1==1){ //$_SESSION['userid']==1){

$shop_list_item = '
<div class="jxCart_shelf_item item [CLASSES] [TAGS] NOshadow page-[PAGE]" 
     id="item-[KEY]>"  
     data-token="[TOKEN]"
     data-item-id="[ID]" 
     data-item-name="[NAME]" 
     data-item-price="[PRICE]" 
     data-item-thumb="[THUMB]" 
     data-item-link="[LINK]" 
     style="[STYLE]">
    <div class="image"><img class="item_thumb thumb" src="[IMAGE]" alt="[NAME]"></div>'
    .(CFG::$vars['shop']['options']['animation']?'<div class="image" style="visibility:hidden;"><img class="img_mini" src="[THUMB]"></div>':'').
    '<div class="datos">
        <span class="item_id id" style="display:none;">[ID]</span>
        <span class="item_name xxname">[NAME]</span>
        <span class="desc">[DESCRIPTION]</span>
        <span class="item_price price">[PRICE] [COIN]</span>
        <div class="input-number small" id="input-number-item-qty-[KEY]">
           <a  class="button-minus button-number-minus" data-field="quantity">-</a>
           <input type="text" step="1" max="" value="1" name="item-qty quantity" id="item-qty-[KEY]" class="quantity-field item_Quantity">
           <a class="button-plus button-number-plus" data-field="quantity">+</a>
           </div><label for="item-qty-[KEY]" style="position:fixed;width:0;height:0;overflow:hidden;">Quantity</label>
           <span class="item_add item-add button" href="javascript:;" data-link="cart/[ID]/[KEY]"> <i class="fa fa-shopping-cart"></i> '.t('ADD_TO_CART').' </span>
     </div>
</div>
';

$shop_list_item_no_stock = '
<div class="jxCart_shelf_item item [CLASSES] [TAGS] NOshadow page-[PAGE]" 
     id="item-[KEY]>"  
     data-token="[TOKEN]"
     data-item-id="[ID]" 
     data-item-name="[NAME]" 
     data-item-price="[PRICE]" 
     data-item-thumb="[THUMB]" 
     data-item-link="[LINK]" 
     style="[STYLE]">
    <div class="image"><img class="item_thumb thumb" src="[IMAGE]" alt="[NAME]"></div>
    <div class="stock">'.t('OUT_OF_STOCK').'</div>
    <div class="datos">
        <span class="item_id id" style="display:none;">[ID]</span>
        <span class="item_name name">[NAME]</span>
        <span class="desc">[DESCRIPTION]</span>
        <span class="item_price price">[PRICE] [COIN]</span>
     </div>
</div>
';
}else{
$shop_list_item = '
<div class="jxCart_shelf_item item [CLASSES] [TAGS] NOshadow page-[PAGE]" 
     id="item-[KEY]>"  
     data-token="[TOKEN]"
     data-item-id="[ID]" 
     data-item-name="[NAME]" 
     data-item-price="[PRICE]" 
     data-item-thumb="[THUMB]" 
     data-item-link="[LINK]" 
     style="[STYLE]">
    <div class="image"><img class="item_thumb thumb" src="[IMAGE]" alt="[NAME]"></div>
    <div class="stock">'.t('OUT_OF_STOCK').'</div>
    <div class="datos">
        <span class="item_id id" style="display:none;">[ID]</span>
        <span class="item_name name">[NAME]</span>
        <span class="desc">[DESCRIPTION]</span>
        <!--<span class="item_price price">[PRICE] [COIN]</span>-->
     </div>
</div>
';
}

$shop_list_footer = '<div id="items">';


$shop_item = 
'<div id="item">
       ITEM
</item>'
;



if(1==2){



            ?>        
            <div class="jxCart_shelf_item item <?=$v['tags']?> NOshadow bodega-<?=$v['bodega']?> tipo-<?=$v['tipo']?> do-<?=$v['do']?> page-<?=$page?> price-<?=price2class($v['price'])?>  <?=(!$v['stock'])?'outofstock':''?>" 
                 id="item-<?=$k?>"  
                 data-token="<?=$_SESSION['token']?>"
                 data-item-id="<?=$v['id']?>" 
                 data-item-name="<?=$v['name']?>" 
                 data-item-price="<?=$v['price']?>" 
                 data-item-thumb="/<?=SCRIPT_DIR_MEDIA?>/<?=MODULE?>/images/<?=$v['id']?>/<?=$v['file']?>" 
                 data-item-link="<?=MODULE?>/item/<?=$v['id']?>/<?=$k?>" 
                 style="<?=$display?'':'display:none;'?>">
                <div class="image"><img class="item_thumb thumb" src="/<?=SCRIPT_DIR_MEDIA?>/<?=MODULE?>/images/<?=$v['id']?>/<?=$v['thumb']?>?ver=<?=$v['ver']?>" alt="<?=$v['name']?>"></div>    <!--small-->
                <?if(CFG::$vars['shop']['options']['animation']){?>
                <div class="image" style="visibility:hidden;"><img class="img_mini" src="/<?=SCRIPT_DIR_MEDIA?>/<?=MODULE?>/images/<?=$v['id']?>/<?=$v['file']?>"></div>   <?  /* hash('crc32b',$d); */ ?>
                <?}?>
                <?if(!$v['stock']){?><div class="stock"><?=t('OUT_OF_STOCK')?></div><?}?>
                <div class="datos">
                    <span class="item_id id" style="display:none;"><?=$v['id']?></span>
                    <span class="item_name name"><?=$v['name']?></span>
                    <span class="desc"><?=$v['description']?></span>
                    <span class="item_price price"><?=$v['price']?> €</span>
                    <?if($v['stock']){?>
                    <div class="input-number small" id="input-number-item-qty-<?=$k?>"><!--
                      --><a  class="button-minus button-number-minus" data-field="quantity">-</a><!--
                      --><input type="text" step="1" max="" value="1" name="item-qty quantity" id="item-qty-<?=$k?>" class="quantity-field item_Quantity"><!--
                      --><a class="button-plus button-number-plus" data-field="quantity">+</a><!--
                    --></div><label for="item-qty-<?=$k?>" style="position:fixed;width:0;height:0;overflow:hidden;">Quantity</label>

                    <span class="item_add item-add button" href="javascript:;" data-link="cart/<?=$v['id']?>/<?=$k?>"> <i class="fa fa-shopping-cart"></i> <?=t('Comprar')?> </span>
                    <?}?>
                </div>
            </div>
            <?

}