<script>
    //CKEDITOR.replace( '<?=$this->name?>' ,{ height: 245,
    //  // Remove the redundant buttons from toolbar groups defined above.
    //  removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
    //});


    ClassicEditor
        .create( document.querySelector( '#<?=$this->name?>' ) )
        .catch( error => {
            console.error( error );
        } );
</script>
		
		