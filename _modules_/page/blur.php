        if( $item_id>=24 && CFG::$vars['prefix']=='gfe'){
            ?>
                <style>
                body ,
                body.blur {
                  overflow: auto;
                  position: relative;
                }
                body:before {
                  content: "";
                  position: fixed;
                  left: 0;
                  right: 0;
                  z-index: -1;
                  display: block;
                  background-image: url(<?=$bg_img?>);
                  background-size:cover; background-repeat: no-repeat;background-position: center; background-attachment: fixed;
                  width: 100%;
                  height: 100%;
                  transition: all 2s;-wenkit-transition: all 2s;-moz-transition: all 2s;-o-transition: all 2s;-ms-transition: all 2s;
                }
                body.blur:before{
                  content: "";
                  position: fixed;
                  left: 0;
                  right: 0;
                  z-index: -1;
                  display: block;
                  background-image: url(<?=$bg_img?>);
                  background-size:cover;
                  width: 100%;
                  height: 100%;
                    -webkit-filter: blur(5px);
                  -moz-filter: blur(5px);
                  -o-filter: blur(5px);
                  -ms-filter: blur(5px);
                  filter: blur(5px);
                 }
                </style>
                <script type="text/javascript">
                    $(function() {     
                        $('.vino-botella').click(function() {
                          console.log('blur');
                          $('body').addClass('blur');
                        });
                        $('.vino-botella').mouseenter(function() {
                          console.log('mouseenter');
                          $('body').addClass('blur');
                        });

                        $('.vino-botella').mouseleave(function() {
                          console.log('mouseleave');
                          $('body').removeClass('blur');
                        });
                    });
                </script>
            <?php 
        }
