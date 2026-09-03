<?php
if(!isset($_EPUB_LOADED)){
    $_EPUB_LOADED=true;



    HTML::js(SCRIPT_DIR_LIB.'/epub/jszip.min.js');
    HTML::js(SCRIPT_DIR_LIB.'/epub/epub.min.js');
    

?>
         

            <!--<img id="epub-img" style="z-index:2222;border:2px solid black;width:100px;height:150px;position:fixed;top:30px;left:30px;">-->
            <div id="epub-reader">
                <div id="epub-cover"><img id="epub-cover-img" src="_images_/indicator.gif" /></div>
                <span id="epub-title">
                    <span id="epub-title-title"></span><span>-</span><span id="author" style="font-style:italic;"></span> &nbsp; <span id="isbn" style="vertical-align:middle;color:#ff0099;font-size:0.8em;"></span>
                </span>
                <span id="epub-toolbar" class="squared">
                    <i class="fa fa-font" style="position:relative;">
                    <select size="11" class="scrollable" id="select-font-family" style="font-family:'IM Fell English';display:none;">
                        <option style="font-family:'IM Fell English';"   value="IM Fell English">IM Fell English</option>
                        <option style="font-family:'Palatino Linotype';" value="Palatino Linotype">Palatino Linotype</option>
                        <option style="font-family:'Bookman Old Style';" value="Bookman Old Style">Bookman Old Style</option>
                        <option style="font-family:'EB Garamond';"       value="EB Garamond">EB Garamond</option>
                        <option style="font-family:Georgia;"             value="Georgia">Georgia</option>
                        <option style="font-family:Montserrat;"          value="Montserrat">Montserrat</option>
                        <option style="font-family:Felipa;"              value="Felipa">Felipa</option>
                        <option style="font-family:Merriweather;"        value="Merriweather">Merriweather</option>
                        <option style="font-family:Roboto;"              value="Roboto">Roboto</option>
                        <option style="font-family:IbarraReal;font-size:1.1em;" value="IbarraReal">Ibarra Real</option>
                        <option style="font-family:serif;"          value="serif">Serif</option>
                    </select>
                    </i><!--
                 --><i class="fa fa-minus"></i><!--
                 --><i class="fa fa-square-o"></i><!--
                 --><i class="fa fa-plus"></i><!--
                 --><i id="toggle-justify" class="fa fa-align-left"></i><!--
                 --><i id="toggle-mode" class="fa fa-sun-o"></i><!--
                 --><i id="epub_reload" class="fa fa-refresh"></i><!--
                 --><i id="epub_toc" class="fa fa-list"></i><!--
                 --><i id="epub_viewer_close" class="fa fa-close"></i>
                </span>
                <div id="prev" class="arrow">‹</div>
                <div id="reader" class="paper0">
                    <div id="toc" style="display:none;"></div>
                    <div id="area"></div>
                    <div id="epub-status">
                        <span>Página </span><span id="epub-page-number">0</span><span> de </span><span id="epub-total-pages">0</span>
                        <span id="epub-progress">0%</span>
                     </div>
                </div>
                <div id="next" class="arrow">›</div>
            </div>
<?php 



HTML::js(SCRIPT_DIR_LIB . '/file_viewer/epub_viewer.js?v='.VERSION);


}
