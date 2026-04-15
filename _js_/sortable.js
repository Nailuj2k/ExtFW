var sortableIn = 0;


function init_sortable(){
    if(console_log) console.log('init_sortable - VERSIÓN CON LOGS COMPLETOS');
    $(".kbn_items").children().each(function() {
        //$(this).parent().height( Math.max( $(this).parent().height(), $(this).height() ));
    });

    $(".tb_id.kbn_items").sortable({
      
      forcePlaceholderSize: true,
      revert  : true,
      axis    : 'yx',
      cancel  : "a, input, textarea, button, select, option", // Cancelar el inicio del arrastre si se hace clic en estos elementos
      
      start: function(e, ui){
        console.log('LOG :: kbn_items::start');
         //ui.placeholder.height(ui.item.height());
         // $('.kbn_items').height(ui.item.height());
      },
      
      stop: function(e, ui) {
        console.log('LOG :: kbn_items::stop');
         //ui.item.effect("highlight", {}, 2000); 
         ///////////////////////                        ui.effect("highlight", {}, 3000);
      },

      update: function(e, ui){    // The function is called after the todos are rearranged
        console.log('LOG :: kbn_items::update');
        var tablename = $(this).closest('.tb_id').attr('id');
        console.log('LOG :: kbn_items::tablename', tablename);
        
        // Usar el método toArray() que devuelve un array con los IDs
        var arr = $(this).closest(".kbn_items").sortable('toArray');
        console.log('LOG :: kbn_items::update - IDs obtenidos:', arr);
        console.log('LOG :: kbn_items::arr tipo:', typeof arr, Array.isArray(arr));
        
        var keys=positions=comma='';
        var grp = '-1'; //ui.item.attr('id').replace('group_','');  //id de la columna
        
        // Convertir a array JavaScript si no lo es ya
        if (arr && typeof arr === 'object' && !Array.isArray(arr)) {
          console.log('LOG :: kbn_items::arr no es un array, intentando convertir');
          try {
            // Si es un objeto jQuery, intentar convertir
            if (arr.jquery) {
              var arrTemp = [];
              for (var i = 0; i < arr.length; i++) {
                arrTemp.push(arr[i]);
              }
              arr = arrTemp;
            } else {
              arr = Array.from(arr);
            }
            console.log('LOG :: kbn_items::arr después de convertir:', arr);
          } catch (e) {
            console.error('LOG :: kbn_items::error al convertir:', e);
            arr = [];
          }
        }
        
        // Verificar si arr es realmente un array después de la conversión
        if (!Array.isArray(arr)) {
          console.error('LOG :: kbn_items::arr sigue sin ser un array después de la conversión');
          arr = [];
        }
        
        console.log('LOG :: kbn_items::antes de procesar IDs');
        
        // Usar for tradicional en lugar de forEach por compatibilidad
        for (var i = 0; i < arr.length; i++) {
          var val = arr[i];
          var key = i;
          
          console.log('LOG :: kbn_items::procesando ID:', val, 'índice:', key);
          
          // Asegurar que val es string antes de usar replace
          if (val === null || val === undefined) {
            console.log('LOG :: kbn_items::val es null/undefined, saltando');
            continue;
          }
          
          val = val.toString();
          console.log('LOG :: kbn_items::val como string:', val);
          
          try {
            val = parseInt(val.replace('group_',''));
            console.log('LOG :: kbn_items::val después de replace y parseInt:', val);
            
            if (/^[0-9]+$/.test(val)) {
              keys += comma+val;
              positions += comma+(key+1);
              comma = ',';
              console.log('LOG :: kbn_items::IDs acumulados:', keys);
              console.log('LOG :: kbn_items::Posiciones acumuladas:', positions);
            } else {
              console.log('LOG :: kbn_items::val no es un número, saltando:', val);
            }
          } catch (e) {
            console.error('LOG :: kbn_items::error procesando val:', e);
          }
        }
        
        console.log('LOG :: kbn_items::update POST','URL que debería enviarse via POST:','/'+module_name+'/ajax/module='+module_name+'/op=rearrange/table='+tablename+'/keys='+keys+'/positions='+positions+'/group='+grp);
        
        // SOLO COMO FALLBACK, intentar obtener los IDs de otra manera
        if (!keys && !positions) {
          console.log('LOG :: kbn_items::FALLBACK - intentando obtener IDs directamente de los elementos');
          var elementos = $(this).closest(".kbn_items").children();
          console.log('LOG :: kbn_items::FALLBACK - elementos encontrados:', elementos.length);
          
          elementos.each(function(idx) {
            var id = $(this).attr('id');
            console.log('LOG :: kbn_items::FALLBACK - elemento:', idx, 'id:', id);
            
            if (id) {
              var val = parseInt(id.replace('group_',''));
              if (/^[0-9]+$/.test(val)) {
                keys += comma+val;
                positions += comma+(idx+1);
                comma = ',';
              }
            }
          });
          
          console.log('LOG :: kbn_items::FALLBACK - keys:', keys, 'positions:', positions);
        }
        
        // Realizar la petición POST
        $.post('/'+module_name+'/ajax/module='+module_name+'/op=rearrange',
              {"table":tablename,"keys":keys,"positions":positions,"group":grp},
              function(data, textStatus, jqXHR){
                if(data.error==0){
                  showMessageInfo(data.msg);
                  console.log('LOG :: kbn_items::POST exitoso');
                }else{
                  showMessageError(data.msg);
                  console.log('LOG :: kbn_items::POST error:', data.msg);
                }
        }, 'json');
        
      }

    });

    $(".kbn_section_items.edit").sortable({
      //placeholder:'.list_items_section',
      connectWith: '.kbn_section_items',
      forcePlaceholderSize: true,
      revert  : true,
      helper : 'clone',
      // cancel: '.noedit',
      // items: "li:not(.noedit)",
      items: "li.div_item",
      // items: ".edit",
      axis    : 'yx',        // Only vertical movements allowed
      // containment  : 'window',      // Constrained by the window
      update: function(e, ui){    // The function is called after the todos are rearranged
        
        console.log('LOG :: kbn_section_items.edit::update');

        // The toArray method returns an array with the ids of the todos
        var tablename = $(this).closest('.tb_id').attr('id');
        console.log('LOG :: kbn_section_items.edit::tablename', tablename);
        
        // Usar el método toArray() que devuelve un array con los IDs
        var arr = $(this).closest(".kbn_section_items").sortable('toArray');
        console.log('LOG :: kbn_section_items.edit::update - IDs obtenidos:', arr);
        console.log('LOG :: kbn_section_items.edit::arr tipo:', typeof arr, Array.isArray(arr));
        
        var keys=positions=comma='';

        // Convertir a array JavaScript si no lo es ya
        if (arr && typeof arr === 'object' && !Array.isArray(arr)) {
          console.log('LOG :: kbn_section_items.edit::arr no es un array, intentando convertir');
          try {
            // Si es un objeto jQuery, intentar convertir
            if (arr.jquery) {
              var arrTemp = [];
              for (var i = 0; i < arr.length; i++) {
                arrTemp.push(arr[i]);
              }
              arr = arrTemp;
            } else {
              arr = Array.from(arr);
            }
            console.log('LOG :: kbn_section_items.edit::arr después de convertir:', arr);
          } catch (e) {
            console.error('LOG :: kbn_section_items.edit::error al convertir:', e);
            arr = [];
          }
        }
        
        // Verificar si arr es realmente un array después de la conversión
        if (!Array.isArray(arr)) {
          console.error('LOG :: kbn_section_items.edit::arr sigue sin ser un array después de la conversión');
          arr = [];
        }
        
        console.log('LOG :: kbn_section_items.edit::antes de procesar IDs');
        
        // Usar for tradicional en lugar de forEach por compatibilidad
        for (var i = 0; i < arr.length; i++) {
          var val = arr[i];
          var key = i;
          
          console.log('LOG :: kbn_section_items.edit::procesando ID:', val, 'índice:', key);
          
          // Asegurar que val es string antes de usar replace
          if (val === null || val === undefined) {
            console.log('LOG :: kbn_section_items.edit::val es null/undefined, saltando');
            continue;
          }
          
          val = val.toString();
          console.log('LOG :: kbn_section_items.edit::val como string:', val);
          
          try {
            val = parseInt(val.replace('row-',''));
            console.log('LOG :: kbn_section_items.edit::val después de replace y parseInt:', val);
            
            if (/^[0-9]+$/.test(val)) {
              keys += comma+val;
              positions += comma+(key+1);
              comma = ',';
              console.log('LOG :: kbn_section_items.edit::IDs acumulados:', keys);
              console.log('LOG :: kbn_section_items.edit::Posiciones acumuladas:', positions);
            } else {
              console.log('LOG :: kbn_section_items.edit::val no es un número, saltando:', val);
            }
          } catch (e) {
            console.error('LOG :: kbn_section_items.edit::error procesando val:', e);
          }
        }
        
        // SOLO COMO FALLBACK, intentar obtener los IDs de otra manera
        if (!keys && !positions) {
          console.log('LOG :: kbn_section_items.edit::FALLBACK - intentando obtener IDs directamente de los elementos');
          var elementos = $(this).closest(".kbn_section_items").children();
          console.log('LOG :: kbn_section_items.edit::FALLBACK - elementos encontrados:', elementos.length);
          
          elementos.each(function(idx) {
            var id = $(this).attr('id');
            console.log('LOG :: kbn_section_items.edit::FALLBACK - elemento:', idx, 'id:', id);
            
            if (id) {
              var val = parseInt(id.replace('row-',''));
              if (/^[0-9]+$/.test(val)) {
                keys += comma+val;
                positions += comma+(idx+1);
                comma = ',';
              }
            }
          });
          
          console.log('LOG :: kbn_section_items.edit::FALLBACK - keys:', keys, 'positions:', positions);
        }
        
        console.log('LIST','kbn_section_items.edit::positions: '+keys+'>>'+positions);
        var grp = $(this).parent().attr('id');
        // Asegurar que grp es string antes de usar replace
        grp = (grp || '').toString();
        grp = grp.replace('group_','');
        
        console.log('LOG :: kbn_section_items.edit::update POST','URL que debería enviarse via POST:','/'+module_name+'/ajax/module='+module_name+'/op=rearrange/table='+tablename+'/keys='+keys+'/positions='+positions+'/group='+grp);

        $.post('/'+module_name+'/ajax/module='+module_name+'/op=rearrange',
              {"table":tablename,"keys":keys,"positions":positions,"group":grp},
              function(data, textStatus, jqXHR){
                if(data.error==0){
                  showMessageInfo(data.msg);
                  console.log('LOG :: kbn_section_items.edit::POST exitoso');
                }else{
                  showMessageError(data.msg);
                  console.log('LOG :: kbn_section_items.edit::POST error:', data.msg);
                }
        }, 'json');
      },
      stop: function(e, ui) {
        console.log('LOG :: kbn_section_items.edit::stop');
        // ui.item.css({'top':'0','left':'0'});  
        // ui.item.effect("highlight", {}, 2000); 
    ////////////////////////////////////////              ui.effect("highlight", {}, 3000);

      //  $( e.toElement ).one('click', function(e){ e.stopImmediatePropagation(); } );
      },  
      beforeStop: function(e, ui) { 
        console.log('LOG :: kbn_section_items.edit::beforeStop');
        //if (sortableIn == 0) {sortableIn = 0;alert('remove');} /*if (sortableIn == 0){ui.item.remove();}*/
      },
      over: function(e, ui) { console.log('LOG :: kbn_section_items.edit::over'); sortableIn = 1; },
      out: function(e, ui) { console.log('LOG :: kbn_section_items.edit::out'); sortableIn = 0; },
      receive: function(e, ui) { console.log('LOG :: kbn_section_items.edit::receive'); sortableIn = 1; }   
    });
}

