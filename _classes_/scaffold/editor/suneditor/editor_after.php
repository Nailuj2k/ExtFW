<script>
console.log('Edit: <?=$this->name?>')
editor_<?=$this->name?> = SUNEDITOR.create('<?=$this->name?>', {
        buttonList : [
            ['undo', 'redo', /*'font', 'fontSize',*/ 'formatBlock'],
            ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript', 'removeFormat'],
            ['outdent', 'indent'],
            //'/' // Line break
            ['fontColor', 'hiliteColor', 'align', 'horizontalRule', 'list', 'table'],
            ['link', 'image', 'video', 'fullScreen', 'showBlocks', 'codeView'/*, 'preview', 'print', 'save'*/]
        ],
        display: 'block',
        width: '100%',
        height: '290px',
        codeMirror: CodeMirror
});


editor_<?=$this->name?>.onChange = function (contents, core) { 
     //console.log('onChange', contents) ;
     editor_<?=$this->name?>.save();
}


// http://get-simple.info/forums/showthread.php?tid=11769
// Spaces bug
// /dist/suneditor.min.js replace  
//else t+=e.replace(/(?!>)\s+?(?=<)/g,""); 
//with 
//else t+=e.replace(/(?!>)\s+?(?=<)/g," ");

</script>
		
		