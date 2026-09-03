<?php




/* * * * * * * * * * *
 *
 * FORM
 * static text2arrayValues($text) -> array
 * public addElement($element)    // $elment must be fieldset or form element
 * public render($submit_activated = false)
 *
 * * * */
 
 
class FORM extends fieldset{
  private $elements = array();
  private $action;  // = $_SERVER['PHP_SELF'];
  public $displaytype = 'form';
  private $method     = 'post';
  public $classname   = false; 
  public $tablename; 
  public $id = false;
  public $ajax = true;

  protected function beginTag(){

    if(!$this->id) $this->id = $this->name;

    $a_form = array('[LEGEND]'    => $this->legend,
                    '[CLASS]'     => $this->classname, 
                    '[ID]'        => $this->id,
                    '[NAME]'      => $this->name,
                    '[AJAX]'      => $this->ajax ? '': 'no-ajax',
                    '[ACTION]'    => $this->action,
                    '[METHOD]'    => $this->method
                    );
                    
 	 echo str_replace( array_keys($a_form), array_values($a_form), FORM_BEGIN ) ;
 	
  }

  protected function endTag(){
    echo FORM_END;
  }
  
  public function setAction($action){  
      $this->action = $action; 
  }
  
  public function addElement($element){
      parent::addElement($element); 
  }

  public function render(){
      $field = new dummyField();
      $field->name = 'formkey';
      $field->value = $this->name;
      parent::addElement(new formHidden($field));       
      parent::render();
      if($this->ajax) $this->footer();
  }
  
  public function footer() {
      ?>
      <script type="text/javascript">
          /***********************************************
          // prepare the form when the DOM is ready 
          $(document).ready(function() { 
              var options_<?=$this->id?> = { 
                  target:        '#result_<?=$this->id?>',   // target element(s) to be updated with server response 
                  beforeSend:    onBeforeSend_<?=$this->id?>,
                  beforeSubmit:  showRequest_<?=$this->id?>,  // pre-submit callback 
                  success:       showResponse_<?=$this->id?>, // post-submit callback 
                  error:         showError_<?=$this->id?>,    // post-submit callback 
                  type:       'post',          // 'get' or 'post', override for form's 'method' attribute 
                  dataType:   'json'           // 'xml', 'script', or 'json' (expected server response type) 
              }; 

              $('#<?=$this->id?>').ajaxForm(options_<?=$this->id?>); 
              //console.log('form target: '+options_<?=$this->id?>.target);
              //console.log($('#<?=$this->id?>').find('input[type=text]').first().attr('id'));
              //$('#<?=$this->id?>').find('input[type=text]').first().focus(); // No funciona!!
              //$('#<?=$this->id?>').find('.enter_as_atb').first().focus(); // No funciona!!
              //console.log($('#<?=$this->id?>').find('.enter_as_atb').first().attr('id'));
         }); 
           

          function onBeforeSend_<?=$this->id?>() {
            $('#<?=$this->id?> .submit').attr('disabled', '');
          }


          function showError_<?=$this->id?>()  {
            showMessageError( '<?php echo __LINE__; ?> - Error al enviar datos');
          }
          
          // pre-submit callback 
          function showRequest_<?=$this->id?>(formData, jqForm, options) { 
              var queryString = $.param(formData); 
              console.log('Datos a enviar:' , queryString);
              return true; 
          } 
           
          // Esta función se ejecuta cuando se recibe la respuesta al envñio del formulario 
          function showResponse_<?=$this->id?>(data, statusText, xhr, $form)  {

              if (data.error>0){
              
                  // Si tenemos error en la respuesta lo mostramos y dejamos el formulario
                  // visible, para que se pueda enviar otra vez. 
                  // TODO: Si recibimos un json en data.errors con una lista de fieldnames y
                  // sus errores podemos mostrar el error correspondiente a cada campo.
                  showMessageError(data.error+': '+data.msg);
                  <?php  if (!$this->tablename){ ?>
                    fail(data);
                  <?php }?>
                  
              }else{
                    
                 if(data.msg) showMessageInfo(data.msg); 

                  <?php  if ($this->tablename){ ?>
                      
                      // Ocultamos el formulario, cuyos datos ya han sido enviados y si
                      // estamos aqui es que ya hemos recibido respuesta.
                      $('#<?=$this->id?>').closest('.form').fadeOut('fast');
                      if(data.op=='update'){
                          
                          $.each(data.row, function(k, v) {
                              var cell_id = $('#cell-'+data.tb+'-'+data.pk+'-'+data.id+'-'+k);
                              var cur_val = cell_id.html();
                              var new_val = v.displayvalue;
                              if(cur_val!=new_val) {
                                cell_id.html(new_val).css('color','red').highlight(); //addClass('item-highlight');
                              }
                          });
                        
                      }else{
                        
                        loadTable(false,
                                  '<?=Vars::mkUrl( MODULE,'ajax','table='.$this->tablename) ?>',
                                  '<?=$this->tablename?>',
                                  data.id,
                                  'show',
                                  'table',
                                  'json');

                      }

                  <?php }else{?>
                    complete(data);
                    //showMessageError(data.msg);
                  <?php }?>
              }
          }
          *************************/
    </script>
    <?php 
  }
  
}