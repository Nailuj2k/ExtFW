<?php

            $actions = '<div class="actions_icons">';
             if ($_ACL->hasPermission('noticias_admin')) $actions .= '<a id="xdele_row_'.$row[TB_PREFIX.'_ID'].'" class="dele_row" title="Eliminar" style="text-decoration:none;" item="'.$row[TB_PREFIX.'_ID'].'"><i class="fa fa-remove" style="color:#ff6600;"> </i></a>';
             if ($_ACL->hasPermission('noticias_admin')) $actions .= '<a id="xedit_row_'.$row[TB_PREFIX.'_ID'].'" class="edit_row" title="Modificar" style="text-decoration:none;" item="'.$row[TB_PREFIX.'_ID'].'"><i class="fa fa-edit" style="color:#ffffcc;"> </i></a>';
            $actions .= '</div>';
             echo $actions;
            ?>
            <script type="text/javascript">
               var id =  <?=$row[TB_PREFIX.'_ID']?>;
               var currentROW = $('#item');
               $('#xedit_row_<?=$row[TB_PREFIX.'_ID']?>').click(function() {
//                  $('.modalformOverlay').remove();
                  var url = AJAX_URL+'/'+_MODULE_+'/ajax/module='+_MODULE_+'/op=edit/table='_TB_PREFIX_+'_'+_TB_NAME_+'/id='+id+'/page=1';
                  console.log(url);
                  $.modalform({ 'title' : 'Modificar fila: '+id, 'url': url  });
                  return false;
                });              
                $('#xdele_row<?=$row[TB_PREFIX.'_ID']?>').click(function() {
                  //var url = AJAX_URL+'?module=galeria&ajax=delete&table=NOT_NEWS_FILES&id='+id+'&page=1';
                  //$.modalform({ 'title' : 'Eliminar fila: '+id, 'url': url  });
                  //currentROW.addClass('animated hinge');
                  setTimeout(function(){location.href = _MODULE_;},2000);
                  /*
                  $.get('/news/ajax/module=news/op=delete/table=NOT_NEWS/id='+id,function(data){
                      if(data.error==0){       //if( msg.toLowerCase().indexOf('ok')>-1)
                          currentROW.addClass('animated hinge');
                          setTimeout(function(){currentROW.fadeOut('slow'); },1800 );
                          showMessageInfo(data.msg);
                      }else{
                          showMessageError(data.msg);
                          $.modalform({'title' : 'ERROR','text': data.msg ,'buttons':'close'});
                      }
                  },'json');
                  */
                  return false;
                });
            </script>
