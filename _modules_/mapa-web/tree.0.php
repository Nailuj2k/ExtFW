<style>


.tree1 {border:1px solid var(--red);max-height:450px;min-height:450px;overflow:auto;}
.tree1, 
.tree1 li {position: relative;list-style-type: none;}
.tree1 {list-style: none;padding-left: 32px;}
.tree1 ul {padding-left: 30px;}
.tree1 li::before, 
.tree1 li::after {content: "";position: absolute;left: -12px;}
.tree1 li::before {border-top: 1px solid #000;top: 9px;width: 12px;height: 0;}
.tree1 li::after {border-left: 1px solid #000;height: 100%;width: 0px;top: 2px;}
.tree1 li:last-child::after {height: 8px;}

.tree1 li span, 
.tree1 li span a{font-size:12px;cursor:pointer;} 

.tree1 li span{padding:2px 5px;}
.tree1 li span:hover, 
.tree1 li span:focus {background:#eee;color: #000;border:1px solid #aaa;}
.tree1 li span:hover + ul li span, 
.tree1 li span:focus + ul li span {background: #eee;color:#000;border:1px solid #aaa;}
.tree1 li span:hover + ul li:after, 
.tree1 li span:hover + ul li:before, 
.tree1 li span:focus + ul li:after, 
.tree1 li span:focus + ul li:before {border-color: #aaa;}

.tree2 {margin-left: 20px;max-height:450px;min-height:450px;overflow:auto;border:1px solid var(--red);}
.tree2 li {list-style-type: none;margin: 2px 0 2px 10px;position: relative;}
.tree2 li:before {content: "";position: absolute;top: -6px;left: -20px;border-left: 1px solid #ddd;border-bottom: 1px solid #ddd;width: 20px;height: 15px;z-index: -1;}
.tree2 li:after {position: absolute;content:"";top: 9px;left: -20px;border-left: 1px solid #ddd;border-top: 1px solid #ddd;width: 20px;height: 100%;}
.tree2 li:last-child:after {display: none;}
.tree2 li span a,
.tree2 li span {font-size:10px;cursor:pointer;}
.tree2 li span {border:1px solid #eaeaea;padding:2px 10px;color:#888;text-decoration: none;background-color:white;}
.tree2 li span:hover, 
.tree2 li span:focus {background:#eee;color: #000;border:1px solid #aaa;}
.tree2 li span:hover + ul li span, 
.tree2 li span:focus + ul li span {background: #eee;color:#000;border:1px solid #aaa;}
.tree2 li span:hover + ul li:after, 
.tree2 li span:hover + ul li:before, 
.tree2 li span:focus + ul li:after, 
.tree2 li span:focus + ul li:before {border-color: #aaa;}

.tree3 {--dx: 5rem;max-height:450px;min-height:450px;overflow:auto;border:1px solid var(--red);}
.tree3,
.tree3 ul {margin: 0;	padding: 0;}
.tree3 > li {margin: 0;}
.tree3 li {position: relative;	display: flex;align-items: center;	margin: .3em var(--dx);	}
.tree3 span {padding: .3em .5em;border:1px solid #e0e0e0;border-radius: .2rem;white-space: nowrap;}
.tree3 li  li span::before {content: "";position: absolute;top: 50%;right: 100%;width: var(--dx);height: calc(.02rem + .06em);
transform: translateY(-50%) skewY(calc(var(--angle, 0) * 1deg))  scaleY(calc(1 / var(--cos-angle, 1)));
transform-origin: right;background: inherit;background-image: linear-gradient(to right, var(--parent-color), transparent);}
.tree3 > li li span {/*border:1px solid #eaeaea;*/ --parent-color: var(--cyan);}
.tree3 > li li li span {/*border:1px solid #eaeaea;*/--parent-color: var(--pink);}
.tree3 span,
.tree3 a{color: var(--dark);/*font-weight:400;*/font-size:12px;cursor:pointer;}
.tree3 li span:hover, 
.tree3 li span:focus {background:#eee;color: #000;border:1px solid #aaa;}
.tree3 li span:hover + ul li span, 
.tree3 li span:focus + ul li span {background: #eee;color:#000;border:1px solid #aaa;}
.tree3 li span:hover + ul li:after, 
.tree3 li span:hover + ul li:before, 
.tree3 li span:focus + ul li:after, 
.tree3 li span:focus + ul li:before {border-color: #aaa;}

.tree2 .has-childs>span{ color:#346ba9;}
.tree2 .has-childs>span:before{ content:'+ ';}

</style>

            <p>
                <a class="btn btn-small" id="expand_all">Expandir todo</a> 
                <a class="btn btn-small" id="contract_all">Contraer todo</a>
            </p> 

<?php

    $menuz1 = new Menu(1);
    $menuz1->markup['header'] = '<ul class="tree2">'.$nl; 
    $menuz1->markup['item_link']  = '<li class="[CLASSES]"><span><a href="[URL]">[CAPTION]</a></span>[CHILDS]</li>'.$nl;
    $menuz1->markup['item_sep']   = '<li class="[CLASSES]"><span>[CAPTION]</span>[CHILDS]</li>'.$nl;
    $menuz1->markup['separator']  = '';
    $menuz1->markup['footer']     = '</ul>';
    $menuz1->markup['header_sub'] = '<ul>'.$nl;
    $menuz1->markup['item_sub']   = '<li class="[CLASSES]"><span><a href="[URL]">[CAPTION]</a></span>[CHILDS]</li>'.$nl;
    $menuz1->markup['footer_sub'] = '</ul>';
    $menuz1->get_items();
    $menuz1->nested_menus=true;
    $menuz1->print_menu(0); //,Menu::$current_item);   


?>

<script>self.Bliss = { shy: true };</script>
<script src="<?=SCRIPT_DIR_JS?>/bliss.js"></script>

<script>

$(function(){

    $('.tree1 li,.tree2 li').click(function(e) {
        e.preventDefault();

        // $(this).find('ul').toggle('fast');

        if($(this).find('ul').is(':visible')){
           $(this).find('ul').hide('fast');
        }else{
           $(this).closest('ul').not(this).find('ul').hide('fast');
           $(this).find('>ul').show('fast');
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

});

$(function(){

    /* code from https://codepen.io/leaverou/pen/JpyZMO **/
    let $ = Bliss, $$ = Bliss.$;

    function show_lines(){
        $$("ul.tree3").forEach(ul => {
            // Wrap each text node with a span
            $$("ul.tree3 li", ul).forEach(li => {
                if (li.childNodes[0].nodeType == 3) {
                    $.create("span", {
                        around: li.childNodes[0]
                    });
                }
            });
            
            // Calculate angle
            $$("ul.tree3 li li > span", ul).forEach(span => {
                var li = span.closest("ul").parentNode;
                var lineCS = getComputedStyle(span, "::before");

                var top = span.parentNode.offsetTop + span.parentNode.offsetHeight / 2;
                var parentTop = li.offsetHeight / 2;
                var dy = top - parentTop;
                var dx = parseInt(lineCS.width);

                var angle = Math.atan2(dy, dx);
                var θ = angle * 180 / Math.PI;
                span.style.setProperty("--angle", θ);
                span.style.setProperty("--cos-angle", Math.cos(angle));
            });
        });
    };

    show_lines();

});
</script>
