<style>


.tree1 {max-height:450px;min-height:450px;overflow:auto;border:1px solid var(--red);}
.tree1, 
.tree1 li {position: relative;list-style-type: none;padding: 1px;}
.tree1 {list-style: none;padding-left: 32px;}
.tree1 ul {padding-left: 30px;}
.tree1 li::before, 
.tree1 li::after {content: "";position: absolute;left: -12px;}
.tree1 li::before {border-top: 1px solid #ddd;top: 9px;width: 12px;height: 0;}
.tree1 li::after {border-left: 1px solid #ddd;height: 100%;width: 0px;top: 2px;}
.tree1 li:last-child::after {height: 8px;}

.tree1 li span, 
.tree1 li span a{font-size:10px;cursor:pointer;} 

.tree1 li span{border:1px solid #eaeaea;padding:2px 10px;color:#888;text-decoration: none;background-color:white;}
.tree1 li span:hover, 
.tree1 li span:focus {background:#eee;color: #000;border:1px solid #aaa;}
.tree1 li span:hover + ul li span, 
.tree1 li span:focus + ul li span {background: #eee;color:#000;border:1px solid #aaa;}
.tree1 li span:hover + ul li:after, 
.tree1 li span:hover + ul li:before, 
.tree1 li span:focus + ul li:after, 
.tree1 li span:focus + ul li:before {border-color: #aaa;}
.tree1 .has-childs>span,
.tree1 .has-childs>span>a{ color:#346ba9;}
.tree1 .has-childs>span:before{ content:'+ ';}

.tree2 {margin-left: 40px;/*max-height:450px;*/min-height: 625px;overflow:auto;border:1px solid var(--red);}
.tree2 li {list-style-type: none;margin: 2px 0 2px 12px;position: relative;}
.tree2 li:before {content: "";position: absolute;top: -6px;left: -20px;border-left: 1px solid #ddd;border-bottom: 1px solid #ddd;width: 20px;height: 15px;}
.tree2 li:after {position: absolute;content:"";top: 9px;left: -20px;border-left: 1px solid #ddd;border-top: 1px solid #ddd;width: 20px;height: 100%;}
.tree2 li:last-child:after {display: none;}
.tree2 li span a,
.tree2 li span {font-size:10px;cursor:pointer;}
.tree2 li span {border:1px solid #eaeaea;padding:2px 10px;color:#888;text-decoration: none;background-color:white;z-index: 1; position: relative;}
.tree2 li span:hover, 
.tree2 li span:focus {background:#eee;color: #000;border:1px solid #aaa;}
.tree2 li span:hover + ul li span, 
.tree2 li span:focus + ul li span {background: #eee;color:#000;border:1px solid #aaa;}
.tree2 li span:hover + ul li:after, 
.tree2 li span:hover + ul li:before, 
.tree2 li span:focus + ul li:after, 
.tree2 li span:focus + ul li:before {border-color: #aaa;}
.tree2 .has-childs>span>a{ color:#346ba9;}
.tree2 .has-childs>span:before{ content:'+ ';/*vertical-align: middle;*/}

.tree2 ul{display:none;}

.tree1,
.tree1 ul,
.tree2,
.tree2 ul {margin: 0;	padding: 0; padding:1px 15px 1px 30px}
.tree3,
.tree3 ul {margin: 0;	padding: 0; padding: 1px 15px 1px 0px;}


.tree3 {--dx: 5rem;/*max-height:450px;*/min-height:450px;overflow:auto;border:1px solid var(--red);}
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

.tree2 li.selected>span {background:#eee;color: red;border:1px solid #aaa;}
.tree2 li.selected>span>a {color: red;}

.tree2 li      >span>.tree-link>.fa{color:transparent !important;}
.tree2 li:hover>span>.tree-link>.fa{color:var(--red) !important;margin-left:10px;}

</style>

            <p>
                <a class="btn btn-small" id="expand_all">Expandir todo</a> 
                <a class="btn btn-small" id="contract_all">Contraer todo</a>
            </p> 

<?php

    $menuz1 = new Menu(1);
    $menuz1->markup['header'] = '<ul class="tree2">'.$nl; 
    $menuz1->markup['item_link']  = '<li id="item-[URL]" class="[CLASSES]"><span><a data-href="[URL]">[CAPTION]</a> <a class="tree-link" target="new" href="[URL]"><i class="fa fa-external-link"></i></a></span>[CHILDS]</li>'.$nl;
    $menuz1->markup['item_sep']   = '<li id="item-[NAME]" class="[CLASSES]"><span>[CAPTION]</span>[CHILDS]</li>'.$nl;
    $menuz1->markup['separator']  = '';
    $menuz1->markup['footer']     = '</ul>';
    $menuz1->markup['header_sub'] = '<ul>'.$nl;
    $menuz1->markup['item_sub']   = '<li id="item-[URL]" class="[CLASSES]"><span><a data-href="[URL]">[CAPTION]</a> <a class="tree-link" target="new" href="[URL]"><i class="fa fa-external-link"></i></a></span>[CHILDS]</li>'.$nl;
    $menuz1->markup['footer_sub'] = '</ul>';
    $menuz1->get_items();
    $menuz1->nested_menus=true;
    $menuz1->print_menu(0); //,Menu::$current_item);   
