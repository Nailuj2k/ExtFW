

  
  <!-- Create the toolbar container -->
<!--
<div id="toolbar">
  <button class="ql-bold">Bold</button>
  <button class="ql-italic">Italic</button>
</div>

-->
<!-- Initialize Quill editor -->
	<script type="text/javascript"> 
	$(document).ready(
		function()
		{
      var quill = new Quill('#<?=$this->name?>');
//      var quill = new Quill('#editor');
 //     quill.addModule('toolbar', { container: '#toolbar' });

		}
	);

	</script>	
