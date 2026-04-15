
<script type="module">
//    function init_editor(tselector){

        if(console_log) console.log('SELECTOR','#<?=$this->name?>');

      //  setTimeout( function(){
     //   console.log('_ID_',_ID_)
     //   console.log('_TB_NAME_',_TB_NAME_)
        //console.log('MODULE',MODULE)
        

            import { WysiwygEditor } from '/_js_/wysiwyg/editor.js?ver=<?=$ver?>';
            import { basicFormattingPlugin } from '/_js_/wysiwyg/basicFormattingPlugin.js?ver=<?=$ver?>';
            import { tablePlugin } from '/_js_/wysiwyg/tablePlugin.js?ver=<?=$ver?>';
            import { linkPlugin } from '/_js_/wysiwyg/linkPlugin.js?ver=<?=$ver?>';
            import { formatPlugin } from '/_js_/wysiwyg/formatPlugin.js?ver=<?=$ver?>';
            import { fullscreenPlugin } from '/_js_/wysiwyg/fullscreenPlugin.js?ver=<?=$ver?>';
            import { imagePlugin } from '/_js_/wysiwyg/imagePlugin.js?ver=<?=$ver?>';
            import { htmlViewPlugin } from '/_js_/wysiwyg/htmlViewPlugin.js?ver=<?=$ver?>';
            import { editableImagesPlugin } from '/_js_/wysiwyg/editableImagesPlugin.js?ver=<?=$ver?>';
            import { infoPlugin } from '/_js_/wysiwyg/infoPlugin.js?ver=<?=$ver?>';

            const myEditor = new WysiwygEditor('#<?=$this->name?>');
            myEditor.registerPlugin(basicFormattingPlugin);
            myEditor.registerPlugin(tablePlugin);
            myEditor.registerPlugin(linkPlugin);
            myEditor.registerPlugin(formatPlugin);
            myEditor.registerPlugin(fullscreenPlugin);
            myEditor.registerPlugin(imagePlugin);
            myEditor.registerPlugin(htmlViewPlugin);
            myEditor.registerPlugin(editableImagesPlugin);
            myEditor.registerPlugin(infoPlugin);

    //    },2000);

  //  }

</script>