<?php
     
$shop_css_file = SCRIPT_DIR_THEME.'/style.shop.css';
HTML::js(SCRIPT_DIR_THEME.'/script.shop.js?ver='.VERSION, 'defer');

//$shop_header = ''; //'<div id="header-shop"></div>';
$shop_list_footer = '<div style="display:block;width:100%;height:40px;"></div>';
$shop_list_header = '<div id="items">';

$shop_list_item = '
<div class="jxCart_shelf_item item div_item [CLASSES] [TAGS] NOshadow page-[PAGE]" 
     id="item-[KEY]"  
     data-token="[TOKEN]"
     data-item-id="[ID]" 
     data-item-name="[NAME]" 
     data-item-price="[PRICE]" 
     data-item-thumb="[THUMB]" 
     data-item-link="[LINK]" 
     style="[STYLE]">
    <div class="image shop-image-gallery" data-images=\'[IMAGES]\' data-image-index="0">
        <button type="button" class="shop-image-arrow shop-image-prev" aria-label="Imagen anterior">‹</button>
        <div class="shop-image-viewport">
            <div class="shop-image-track">
                <img class="item_thumb thumb" src="[IMAGE]" alt="[NAME]">
            </div>
        </div>
        <button type="button" class="shop-image-arrow shop-image-next" aria-label="Imagen siguiente">›</button>
    </div>'
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
           <!-- [TABLE_ACTIONS] -->
     </div>
</div>
';

$shop_list_item_no_stock = '
<div class="jxCart_shelf_item item div_item [CLASSES] [TAGS] NOshadow page-[PAGE]" 
     id="item-[KEY]"  
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
        <!-- [TABLE_ACTIONS] -->
     </div>
</div>
';

$shop_list_footer = '<div id="items">';

$shop_item = '<div id="item">ITEM</div>';
