<?php
/**
 * Theme "example" - plantilla minima del modulo shop.
 *
 * Punto de partida para temas nuevos: solo el HTML que el modulo necesita
 * para funcionar, sin decoracion. Copiar este archivo y style.shop.css al
 * tema nuevo y crecer desde ahi.
 *
 * ---------------------------------------------------------------------
 * GANCHOS OBLIGATORIOS (si se quitan, algo deja de funcionar)
 * ---------------------------------------------------------------------
 * .jxCart_shelf_item   contenedor que delega el "add to cart"  (cart.js)
 * .item                cart.js hace closest('.item') para la animacion
 * id="item-[KEY]"      TABLE_CLI_PRODUCTS lo reescribe a id="row-N" para
 *                      los botones de editar/borrar del scaffold
 * data-item-link       click en imagen o nombre -> ficha  (script.js:367)
 * .item_id             valor = PRODUCT_ID. jxCart indexa el carrito por el
 * .item_name           columna "name" del carrito + zona clicable
 * .item_price          columna "price" del carrito
 * .item_thumb          columna "thumb" + origen del clon de la animacion
 * .item_Quantity       cantidad (opcional: sin el se añade 1 unidad)
 * .item_add            boton de añadir
 * .button-number-plus / .button-number-minus   controles del +/- (cart.js).
 *                      Son <button type="button">: se centran solos y son
 *                      focusables, asi el CSS no tiene que recolocar nada.
 *                      item.php usa <a> con esas mismas clases.
 * <!-- [TABLE_ACTIONS] -->  donde el scaffold inyecta editar/borrar
 *
 * cart.js recolecta los campos leyendo las clases item_XXX del card, asi
 * que el nombre de la clase ES el nombre del campo en el carrito. Cuidado
 * al renombrarlas.
 *
 * ---------------------------------------------------------------------
 * PLACEHOLDERS disponibles
 * ---------------------------------------------------------------------
 * [KEY] [ID] [NAME] [DESCRIPTION] [PRICE] [COIN] [LINK] [IMAGE] [THUMB]
 * [CLASSES] [TAGS] [PAGE] [STYLE] [TOKEN] [IMAGES]
 *
 * [IMAGES] es un JSON con todas las imagenes del producto. Este tema no lo
 * usa (una imagen por card); ver _themes_/default para un slider que si.
 * Si se añade: [IMAGES] debe sustituirse ANTES que [IMAGE], porque
 * str_replace va en orden y [IMAGE] es prefijo de [IMAGES].
 *
 * La ficha de producto (item.php) la pinta el modulo y no es sustituible
 * desde el tema: solo se le da estilo. Ver style.shop.css.
 */

$shop_css_file = SCRIPT_DIR_THEME.'/style.shop.css';

$shop_header = '';
$shop_footer = '';

$shop_list_header = '<div id="items">';
$shop_list_footer = '</div>';

$shop_list_item = '
<div class="jxCart_shelf_item item [CLASSES] page-[PAGE]" id="item-[KEY]" data-item-link="[LINK]" style="[STYLE]">
    <div class="image"><img class="item_thumb" src="[IMAGE]" alt="[NAME]"></div>
    <div class="datos">
        <span class="item_id" style="display:none">[ID]</span>
        <span class="item_name">[NAME]</span>
        <span class="desc">[DESCRIPTION]</span>
        <span class="item_price">[PRICE] [COIN]</span>
        <div class="input-number">
            <button type="button" class="button-number-minus">-</button>
            <input type="text" inputmode="numeric" value="1" id="item-qty-[KEY]" class="quantity-field item_Quantity" aria-label="'.t('QUANTITY','Cantidad').'">
            <button type="button" class="button-number-plus">+</button>
        </div>
        <span class="item_add button">'.t('ADD_TO_CART').'</span>
        <!-- [TABLE_ACTIONS] -->
    </div>
</div>';

$shop_list_item_no_stock = '
<div class="jxCart_shelf_item item [CLASSES] page-[PAGE]" id="item-[KEY]" data-item-link="[LINK]" style="[STYLE]">
    <div class="image">
        <img class="item_thumb" src="[IMAGE]" alt="[NAME]">
        <span class="stock">'.t('OUT_OF_STOCK').'</span>
    </div>
    <div class="datos">
        <span class="item_id" style="display:none">[ID]</span>
        <span class="item_name">[NAME]</span>
        <span class="desc">[DESCRIPTION]</span>
        <span class="item_price">[PRICE] [COIN]</span>
        <!-- [TABLE_ACTIONS] -->
    </div>
</div>';
