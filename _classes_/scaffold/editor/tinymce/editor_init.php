<?php

if(1===2){

?>

<?php
     HTML::js(SCRIPT_DIR_LIB.'/tinymce/tinymce.min.js?ver=1.0.4');
?>
<script type="text/javascript"> 

    console.log('EDITOR_INIT');
    wysiwyg_editor = 'tinymce';

    function init_editor(selector){

        //console.log('EDITOR_INIT','init_editor',selector);

        //tinymce.remove(selector);
        //   tinymce.remove() 
        setTimeout( function(){

            tinymce.init({
                selector: selector,
                skin: 'tinymce-5',
                plugins: "image code fullscreen table",
               // toolbar: "undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link code ",
                toolbar: 'undo redo | bold italic underline strikethrough | formatselect | alignleft aligncenter alignright alignjustify | outdent indent | table | numlist bullist | forecolor backcolor removeformat | link  | image | code | fullscreen',
              //  | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',          code                          
                menubar:false,
                statusbar: false,
                height: 290,
                width:"auto",
                license_key: 'gpl'
            });
        },1000);

    }

</script>

<style>
.control-group.textarea-wysiwyg {
    min-height: 300px;
    max-height: 300px;
}      
.control-group.textarea-wysiwyg label{display:none !important;}
.control-group.textarea-wysiwyg .controls{left:0 !important;width: 100%;}
</style>


<?php } else if (CFG::$vars['tinymce']['apikey']) { ?>


<!-- Place the first <script> tag in your HTML's <head> -->
<script src="https://cdn.tiny.cloud/1/<?=CFG::$vars['tinymce']['apikey']?>/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

<!-- Place the following <script> and <textarea> tags your HTML's <body> -->
<script>


console.log('SELECTOR.BEFORE');

function init_editor(tselector){

    console.log('SELECTOR',tselector);

    setTimeout( function(){
    
       console.log('SELECTOR.LOADING',tselector);
       tinymce.init({
            selector: tselector,
            menubar: false,
            statusbar: false,
            height: 290,
            width:"auto",            
       //   plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',           
            plugins: "anchor autolink link emoticons image code fullscreen table",
       //   toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            toolbar: 'undo redo | bold italic underline strikethrough | formatselect | alignleft aligncenter alignright alignjustify | outdent indent | table | numlist bullist | forecolor backcolor removeformat | link  | image | code | fullscreen',

       }); 
   
    },2000);

}




//ready( init_editor )


</script>

<?php } else { ?>



<?php }  ?>
