<script type="text/javascript" src="<?=SCRIPT_DIR_MODULE?>/script.js?ver=1.0.0"></script>


<?php

    if ($_ARGS[1]=='old'){

        ?>

        <script>
        $(function () {

            $('li a').click(function(e) {
                e.preventDefault();
                $(this).next().toggle('fast');
                return false;
            });


            $('#expand_all').click(function(){
                $('.mainmenu ul').show('fast');
            });

            $('#contract_all').click(function(){
                $('.mainmenu ul').hide('fast');
            });

            $('.mainmenu li').map(function(){
                $(this).prepend(' <i class="fa fa-eye"></i> '); 
            });

            $('.mainmenu li>.fa-eye').click(function(){
            console.log(  $(this).closest('li').find('a').attr('href') ); 
            $('#iframe-a8').attr('src',$(this).closest('li').find('a').attr('href'));
            });

        });

        </script>
        <?php

    }else{

        ?>        
        <!--
        <script>self.Bliss = { shy: true };</script>
        <script src="<?=SCRIPT_DIR_JS?>/bliss.js"></script>
        -->
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

            $('.tree2 a').click(function(e) {
                let url = $(this).data('href');
                console.log(url);
                $('#page_frame').attr('src',url);
                $('.tree2 .selected').removeClass('selected');    
                click_parent('item-'+url);
                
            });


            $('.tree2 a.tree-link').click(function(e) {
                e.stopPropagation();
                //console.log('url');
            });


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
            
        //    click_parent(current_item);


        });

        $(function(){

            /* code from https://codepen.io/leaverou/pen/JpyZMO **/
            /*
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

            
            $('.tree3 a.tree-link').click(function(e) {
                e.stopPropagation();
                console.log('url');
            });

            $('.tree3 a').click(function(e) {
                let url = $(this).data('href');
                console.log('::URL::',url);
                $('#page_frame').attr('src',url);
                $('.tree3 .selected').removeClass('selected');    
                click_parent('item-'+url);
                
            });

            show_lines();
            */
        });
        </script>


        <?php

    }