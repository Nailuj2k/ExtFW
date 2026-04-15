<style>

.tree2 {margin-left: 40px;min-height:500px;overflow:auto;padding:1px 1px 1px 10px}
.tree2,
.tree2 ul {margin: 0;	padding: 0; padding:1px 1px 1px 1px}
.tree2 li {list-style-type: none;margin: 2px 0 2px 12px;position: relative;}
.tree2 li:before {content: "";position: absolute;top: -6px;left: -6px;border-left: 1px solid #ddd;border-bottom: 1px solid #ddd;width: 6px;height: 15px;}
.tree2 li:after {position: absolute;content:"";top: 9px;left: -6px;border-left: 1px solid #ddd;border-top: 1px solid #ddd;width: 6px;height: 100%;}
.tree2 li:last-child:after {display: none;}
.tree2 li span a,
.tree2 li span {font-size:10px;cursor:pointer;}
.tree2 li span {border:1px solid #eaeaea;padding:2px 5px;color:#888;text-decoration: none;background-color:white;z-index: 1; position: relative;}
.tree2 li span:hover, 
.tree2 li span:focus {background:#eee;color: #000;border:1px solid #aaa;}
.tree2 li span:hover + ul li span, 
.tree2 li span:focus + ul li span {background: #eee;color:#000;border:1px solid #aaa;}
.tree2 li span:hover + ul li:after, 
.tree2 li span:hover + ul li:before, 
.tree2 li span:focus + ul li:after, 
.tree2 li span:focus + ul li:before {border-color: #aaa;}
.tree2 .has-childs>span>a{ color:#346ba9;}
.tree2 .has-childs>span:before{ content:'+ ';}
.tree2 li.selected>span {background:#eee;color: red;border:1px solid #aaa;}
.tree2 li.selected>span>a {color: red;}

#sidebar-links{
               position:fixed;
               /*left:-220px;
               top:200px;
               height:28px;*/
               width:250px;
               border:2px solid #0a769e;
               background-color:#fdfdfd;/**/transition: left 0.4s ease-in-out;/**/
               z-index:10;
}


#sidebar-links.collapsed{  border-radius: 5px;  background-color: #0a769e;  opacity: 0.5;}
#sidebar-links.collapsed:hover {opacity:1;}

#sidebar-links .btn-small { /*padding: 3px 10px;*/}
</style>
            <p style="padding:2px;text-align:right;margin:0;border-bottom: 1px solid #0a769e66;font-size:12px !important">
                <a class="btn btn-small" id="expand_all">Expandir todo</a> 
                <a class="btn btn-small" id="contract_all">Contraer todo</a>
                <a class="btn btn-small" id="toggle-sidebar"> >> </a>
            </p> 

<div style="overflow:auto;position:absolute;top:30px;left:0;right:0;bottom:0;"><!--<p><?=$_ARGS['item']?></p>-->
<?php

    $menuz1 = new Menu(1);
    $menuz1->markup['header'] = '<ul class="tree2">'.$nl; 
    $menuz1->markup['item_link']  = '<li id="item-[URL]"  class="[CLASSES]"><span><a href="[URL]">[CAPTION]</a></span>[CHILDS]</li>'.$nl;
    $menuz1->markup['item_sep']   = '<li id="item-[NAME]"  class="[CLASSES]"><span>[CAPTION]</span>[CHILDS]</li>'.$nl;
    $menuz1->markup['separator']  = '';
    $menuz1->markup['footer']     = '</ul>';
    $menuz1->markup['header_sub'] = '<ul style="display:none;">'.$nl;
    $menuz1->markup['item_sub']   = '<li id="item-[URL]" class="[CLASSES]"><span><a href="[URL]">[CAPTION]</a></span>[CHILDS]</li>'.$nl;
    $menuz1->markup['footer_sub'] = '</ul>';
    $menuz1->get_items();
    $menuz1->nested_menus=true;
    $menuz1->print_menu(0); //,Menu::$current_item);   

    //print_r(Menu::$current_item);
    //$menuz1->breadcrumb();
    //Vars::debug_var($_ARGS['item']);
?>
</div>


<script>

$(function(){

    var current_item = 'item-<?=$_ARGS['item']?>';
    //console.log('CURRENT_ITEM',current_item);

    $('.tree1 li,.tree2 li').click(function(e) {
        e.preventDefault();

        // $(this).find('ul').toggle('fast');

        if($(this).find('ul').is(':visible')){
           $(this).find('ul').hide('fast');
        }else{
           $(this).closest('ul').not(this).find('ul').hide('fast');
           $(this).find('ul').show('fast');
        }

        //show_lines();
        return false;
    });

    $('#expand_all').click(function(){
        $('.tree1 ul,.tree2 ul').show('fast');
    });

    $('#contract_all').click(function(){
        $('.tree1 ul,.tree2 ul').hide('fast');
    });

    $('.tree2 a').click(function(e) {
        let url = $(this).attr('href');
        // console.log(url);
        location.href = url;
    });

   // $('#'+current_item).parent().closest('li').attr('id').show();

    function click_parent(id){
      //console.log('    id:',id);
      let parent = $('#'+id).parent().closest('li').attr('id');
      $('#'+id).addClass('selected');    
      if(typeof parent !== "undefined"){
          //console.log('parent:',parent);
          //    $( '#'+parent ).click();
          $( '#'+parent+'>ul' ).show();
          let pl = $('#'+parent).length
          //console.log('length:', pl )
          if(pl===1) {
              //console.log('click_parent('+parent+')');
              click_parent(parent);
          }
          //console.log('parent',parent.length,parent)
      }
    }
    
    click_parent(current_item);

  //  $('#'+current_item).closest('ul').closest('ul').show();

    function sidebar_show(){
        $("#sidebar-links")[0].style.left = '0px';
        $('#sidebar-links #toggle-sidebar').html(' << ');//.css('text-align','right');
        $("#sidebar-links").css('top','0')
                           .css('bottom','0')
                           .css('height','auto')  
                           .removeClass('collapsed');
        saveCookie('sidebar_visible','yes');
    } 

    function sidebar_hide(){
        let fw = $("#sidebar-links").outerWidth();
        $("#sidebar-links")[0].style.left = (0-fw+32)+'px';
        $('#sidebar-links #toggle-sidebar').html(' >> ');//.css('text-align','left')
        $("#sidebar-links").css('top','200px')
                           .css('height','28px')
                           .css('bottom','auto')
                           .addClass('collapsed');
        saveCookie('sidebar_visible','no');
    } 

    $('#toggle-sidebar').click(function(){
        var pos = $("#sidebar-links").offset();
        var hidden = pos['left']<0;
        if(hidden) 
            sidebar_show();
        else
            sidebar_hide();
    });

    var sidebar_visible = getCookie('sidebar_visible');

    if(sidebar_visible=='yes') 
        sidebar_show();       
    else       
        sidebar_hide();
    

});
</script>