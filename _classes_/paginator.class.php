<?php

class Paginator {
  // mandat
  public $rowid;
  public $total;
  public $num_items;
  public $page_num;
  public $link;      // "javascript:load_page('hulamm_user',%s);"
  // optional
  public $id = 'id';
  public $parent_value = false;
  public $class = 'nav_div';
  public $page_links = true;
  public $num_links = 0;
  public $mid_links = true;
  public $begin_end_links = true;
  public $prev_next_links = true;
  public $aux_links  = true;
  public $label_page = true;
  public $label_item = true;
  public $links      = array();
  public $labels     = array();
  public $markup_link = '<a class="page_link" data-page="%s" data-parent="" title=%s"><span class="%s">%s</span></a>';
  public $markup_label = '<span title="%s" class="%s">%s</span>';
  public $markup = '<div id="%s" class="%s">%s</div>';
  public $text_before;
  public $text_after;
  public $link_add;   
  public $link_view;   
  public $link_print;   
  public $link_pdf;
  public $input_page_num;
  public $link_word;  //ADD 20131009
  public $link_excel; //ADD 20131009
  public $link_csv; //ADD 20131009
  public $link_reload;  
  public $link_filter = true;  
  public $link_setup;
  public $link_cfg;
  public $link_gallery_mode;   
  public $link_upload_files; // = true;
  public $theme = 'font-awesome'; // 'default', 'images'
  public $paginator_simple = true;


  public function __construct($rowid,$total,$num_items,$page_num,$link){

    $this->rowid     = $rowid;
    $this->total     = $total;
    $this->num_items = $num_items;
    $this->page_num  = $page_num;
    $this->link      = $link;     
    $this->setTheme();
  
  }

  function label_page( $actual , $max_paginas)        { return sprintf( $this->labels['page'] , $actual , $max_paginas );         }
  function label_item($page_from , $page_to , $total) { return sprintf( $this->labels['item'] , $page_from , $page_to , $total ); }
  
  function get_label($title,$class,$caption) { 
    return  sprintf( $this->markup_label, $title, 'nav_link_aux '.$class, $caption );
  }  

  function get_link($url,$title,$class,$caption,$id=false) { 
    if(!$title) $title=$caption;
    //if($url) return $this->makeLink( $url, $title, 'nav_link_aux '.$class, $caption, $id =false);
    if($url) return sprintf( $this->markup_link, $url, $title, 'nav_link_aux '.$class, $caption );
        else return $this->get_label($title,$class,$caption) ;
  }

  public function setTheme($theme='font-awesome'){
    if ($theme=='default'){ 
      $this->labels['first']     = '« '.t('FIRST');   // «
      $this->labels['prev']      = '‹ '.t('PREV');  // ‹ ◄
      $this->labels['next']      = t('NEXT').' ›'; // › ►
      $this->labels['last']      = t('LAST').' »';    // »
      $this->labels['first_off'] = $this->labels['first'];
      $this->labels['prev_off']  = $this->labels['prev']; 
      $this->labels['next_off']  = $this->labels['next'];
      $this->labels['last_off']  = $this->labels['last'];
    }else if ($theme=='simple'){ 
      $this->labels['first']     = '«' ; 
      $this->labels['prev']      = '<' ;                  
      $this->labels['next']      = '>' ;
      $this->labels['last']      = '»' ;
      $this->labels['first_off'] = '«' ; 
      $this->labels['prev_off']  = '<' ; 
      $this->labels['next_off']  = '>' ;
      $this->labels['last_off']  = '»' ;
    } else if ($theme=='images'){
      $this->labels['first']     = '<img src="_images_/famfam/resultset_first.png">'; 
      $this->labels['prev']      = '<img src="_images_/famfam/prev.png">';                  
      $this->labels['next']      = '<img src="_images_/famfam/next.png">';
      $this->labels['last']      = '<img src="_images_/famfam/resultset_last.png">';
      $this->labels['first_off'] = '<img src="_images_/famfam/resultset_first_gray.png">'; 
      $this->labels['prev_off']  = '<img src="_images_/famfam/prev_gray.png">'; 
      $this->labels['next_off']  = '<img src="_images_/famfam/next_gray.png" >';
      $this->labels['last_off']  = '<img src="_images_/famfam/resultset_last_gray.png">';
    } else if ($theme=='font-awesome'){
      $this->labels['first']     = '<i class="fa fa-angle-double-left"></i>'; 
      $this->labels['prev']      = '<i class="fa fa-angle-left"></i>'; 
      $this->labels['next']      = '<i class="fa fa-angle-right"></i>'; 
      $this->labels['last']      = '<i class="fa fa-angle-double-right"></i>'; 
      $this->labels['first_off'] = '<i class="fa fa-angle-double-left"></i>'; 
      $this->labels['prev_off']  = '<i class="fa fa-angle-left"></i>'; 
      $this->labels['next_off']  = '<i class="fa fa-angle-right"></i>'; 
      $this->labels['last_off']  = '<i class="fa fa-angle-double-right"></i>'; 
      $this->labels['add']       = '<i class="fa fa-plus fa-inverse"></i> Añadir'; 
    }
    
    $this->labels['view']   = '<span> <i class="fa fa-eye fa-inverse"></i> </span>';
    $this->labels['print']  = '<span> <i class="fa fa-print fa-inverse"></i> </span>';
    $this->labels['pdf']    = '<span> <i class="fa fa-file-pdf-o fa-inverse"></i> </span>';
    $this->labels['word']   = '<span> <i class="fa fa-file-word-o fa-inverse"></i> </span>'; //ADD 20131009
    $this->labels['excel']  = '<span> <i class="fa fa-file-excel-o fa-inverse"></i> </span>'; //ADD 20131009
    $this->labels['csv']    = '<span> <i class="fa fa-file-excel-o fa-inverse"></i> </span>'; //ADD 20131009
    $this->labels['filter'] = '<span> <i class="fa fa-filter fa-inverse"></i> </span>';
    $this->labels['update'] = '<span> <i class="fa fa-repeat fa-inverse"></i> </span>';
    $this->labels['setup']  = '<span> <i class="fa fa-wrench fa-inverse"></i> </span>';
    $this->labels['cfg']    = '<span> <i class="fa fa-tasks fa-inverse"></i> </span>';
    //$this->labels['gallery']   = '<span> <i class="fa fa-list fa-inverse"></i> </span>';
    $this->labels['upload_files']   = '<span> <i class="fa fa-upload fa-inverse"></i> </span>';

    //$this->labels['cfg']    = t('Configuración');         
    $this->labels['cat']    = t('EDIT_CATEGORIES');         
    $this->labels['back']   = t('BACK');         
    $this->labels['add']    = '+ '.t('ADD');    // +
  
    $this->labels['page']   = t('PAGE_%s_OF_%s');
    $this->labels['item']   = t('ROWS_%s_FROM_%s_TO_%s');
  }


  //(0, $_total, $this->num_items, $this->page, $this->paginator_link
  public function __toString() {
    $page_start = max(0, ($this->page_num) ? $this->num_items * ($this->page_num - 1) : 0 );
    $num_enlaces  = ($this->num_links * 2)+1;   // enlaces
    if($this->aux_links)$num_enlaces = $num_enlaces+4;
    $num_paginas = (floor($this->total / $this->num_items));
    if($num_enlaces>$num_paginas) {
       $this->num_links = 0;
       $this->aux_links = false;
    }
    if($this->num_links)
    $rango = $this->num_links;  //floor($num_links/2);
    if (($this->rowid>0)&&(!$this->page_num)){
      $this->page_num = floor($this->rowid/$this->num_items); 
      if (fmod($this->rowid,$this->num_items)!==0) $this->page_num++;
    }
    $max_paginas = ($max = ceil($this->total / $this->num_items))? $max : 1;
    $vacio = ($this->total == 0);
    $actual = min($max_paginas, max(1, isset($this->page_num) ? $this->page_num : 1)); // Pagina actual
    if($num_links) $rango = max (0, min(10, (int)$rango));
    if ($rango){ 
      $inicio = ($actual - $rango < 1) ? 1 : $actual - $rango;
      $fin = ($actual + $rango > ($max = $max_paginas)) ? $max : $actual + $rango;
    }else{ 
      $inicio = 1; 
      $fin = ($max = $max_paginas); 
    }
    $page_from = $page_start+1;  //$inicio +1;
    $page_to   = min( $page_start+$this->num_items , $this->total );  // min($this->total,$inicio + $this->num_items);

    if ($this->label_page)  
      $r_page = $this->get_label('','nav_info',$this->label_page( $actual, $max_paginas) );

    if ($this->begin_end_links)
    $r_first = ($actual != 1) ? $this->get_link( sprintf($this->link,'1'), 'Primero', 'nav_firstlast_on', $this->labels['first'] )
                              : $this->get_label( 'Primero', 'nav_firstlast_off', $this->labels['first_off'] ) ;

    if($this->prev_next_links)
    $r_prev = ($actual != 1) ? $this->get_link(sprintf($this->link, $actual-1), 'Anterior', 'nav_prevnext_on prev_on', $this->labels['prev'] ) 
                             : $this->get_label( 'Anterior', 'nav_prevnext_off', $this->labels['prev_off'] );
    $r_links = '';
    if ($this->aux_links){
      if (/* $max_paginas>10 && */ $rango && $actual>1 && $inicio>1) $r_links .= $this->get_link( sprintf($this->link,1), '', 'nav_link', '1' ) ;
      if (/* $max_paginas>10 && */ $rango && $actual>2 && $inicio>2) $r_links .= $this->get_link( sprintf($this->link,2), '', 'nav_link', '2' );
    }

    if($this->mid_links){
      if ($this->aux_links) {if ($inicio > 3) $r_links .= $this->get_label( '', 'nav_empty', '..' ); }
                       else {if ($inicio > 1) $r_links .= $this->get_label( '', 'nav_empty', '..' ); }
    }

    if($this->page_links){
      if ($rango){
        if (($max - $actual) < $rango)  $inicio = $inicio - ($rango-($max - $actual));
        if ($actual <= $rango)          $fin = $fin + ($rango - $actual +1);
      }

      for ($i=$inicio; $i<=$fin; $i++){
        if($this->mid_links)  $r_links .= ($i != $actual) ? $this->get_link( sprintf($this->link,$i) ,'' , 'nav_link', $i ) 
                                                          : $this->get_label( 'Página actual', 'nav_active active', $i );
      } 
    }
    if($this->mid_links){
      if ($this->aux_links){
        if ($fin<$max_paginas-2 && $fin<$max) $r_links .=  $this->get_label( '', 'nav_empty', '..' );
      }else{
        if ($fin<$max_paginas && $fin<$max)   $r_links .=  $this->get_label( '', 'nav_empty', '..' );
      }
    }
    
    if ($this->aux_links){
      if (/* $max_paginas>10 && */$rango && $actual<$max_paginas-1 && $fin<$max_paginas-1)
        $r_links .= $this->get_link( sprintf($this->link,$max_paginas-1), '','nav_link' ,$max_paginas-1);
      if (/* $max_paginas>10 && */$rango && $actual<$max_paginas && $fin<$max_paginas  )
        $r_links .= $this->get_link( sprintf($this->link,$max_paginas),   '','nav_link' ,$max_paginas);
    }

    if($this->prev_next_links)
      $r_next = ($actual != $max_paginas && !$vacio) ? $this->get_link(sprintf($this->link,$actual+1), 'Siguiente', 'nav_prevnext_on next_on', $this->labels['next'] )
                                                     : $this->get_label('Siguiente','nav_prevnext_off',$this->labels['next_off']);
    
    if ($this->begin_end_links)
      $r_last = ($actual != $max_paginas && !$vacio) ? $this->get_link(sprintf($this->link,$max_paginas),'Último','nav_firstlast_on',$this->labels['last']) 
                                                     : $this->get_label('Último','nav_firstlast_off',$this->labels['last_off']);

    if ($this->label_item)
      $r_item = $this->get_label('','nav_info',$this->label_item($page_from , $page_to , $this->total) );

    $extra_buttons = '';
    
    if ($this->input_page_num) $extra_buttons .= '<input id="'.$this->parent_value.'" class="input_page_num nav_link" min="1" max="'.$max_paginas.'" placeholder="Nº página" value="">';
    if ($this->link_add)   $extra_buttons .= '<li><a id="'.$this->parent_value.'" title="Añadir" class="add btn-success nav_link">'.$this->labels['add'].'</a></li>';
    if ($this->link_upload_files)  $extra_buttons .= '<li><a  id="'.$this->parent_value.'" data-parent="'.$this->parent_value.'" title="Subir archivos" class="upload_files btn-success nav_link">'.$this->labels['upload_files'].'</a></li>';
    if ($this->link_gallery_mode) $extra_buttons .= '<li><a  id="'.$this->parent_value.'" title="Cambiar vista" class="gallery btn-success nav_link">'.$this->labels['gallery'].'</a></li>';
    if ($this->link_view)  $extra_buttons .= '<li><a title="Ver" class="view btn-success nav_link">'.$this->labels['view'].'</a></li>';
    if ($this->link_print) $extra_buttons .= '<li><a title="Imprimir" class="print btn-success nav_link">'.$this->labels['print'].'</a></li>';
    if ($this->link_pdf)   $extra_buttons .= '<li><a title="PDF" target="_blank"  class="pdf btn-success nav_link">'.$this->labels['pdf'].'</a></li>';
    if ($this->link_word)  $extra_buttons .= '<li><a title="Word" class="word btn-word btn-successs nav_link">'.$this->labels['word'].'</a></li>';      //ADD 20131009
    if ($this->link_excel) $extra_buttons .= '<li><a title="Excel" class="excel btn-excel btn-successs nav_link">'.$this->labels['excel'].'</a></li>';   //ADD 20131009
    if ($this->link_csv)   $extra_buttons .= '<li><a title="CSV" class="csv btn-csv btn-successs nav_link">'.$this->labels['csv'].'</a></li>';   //ADD 20131009
    if ($this->link_filter)$extra_buttons .= '<li><a id="'.$this->parent_value.'" title="Filtrar" class="filter btn-info nav_link">'.$this->labels['filter'].'</a></li>';
    if ($this->link_reload)$extra_buttons .= '<li><a id="'.$this->parent_value.'" title="Actualizar" class="reload btn-info nav_link">'.$this->labels['update'].'</a></li>';
    if ($this->link_cfg)   $extra_buttons .= '<li><a title="Configuración" class="cfg btn-info nav_link">'.$this->labels['cfg'].'</a></li>';
    if ($this->link_setup) $extra_buttons .= '<li><a title="Setup" class="setup btn-danger nav_link">'.$this->labels['setup'].'</a></li>';
    
    $r_aux_links = '';
    if(count($this->links)>0)
      foreach( $this->links as $val) {
        $r_aux_links .= $this->get_link($val['link'],$val['title'],$val['class'],$val['caption'],  ($val['id']) ? $val['id'] : false  ) ;
      }

    if ($this->total<=$this->num_items){
      $r_first = '';
      $r_prev= '';
      $r_next = '';
      $r_last = '';
      $r_links='';
      $r_page = '';
      $r_item = '';
    }
    
    if ($this->text_before) $r_before = $this->get_label('','nav_info', $this->text_before );
    if ($this->text_after) $r_after = $this->get_label('','nav_info', $this->text_after );
    
    //if ( ($max_paginas>1) || isset($this->link_back) || isset($this->link_add) || isset($this->link_config) )
    return sprintf($this->markup, $this->id, $this->class, $r_before.$r_aux_links.$r_page.$r_first.$r_prev.$r_links.$r_next.$r_last.$r_item.$extra_buttons.$r_after);  


  }

}

