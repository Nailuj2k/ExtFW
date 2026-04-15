<?php 

  // menu.class.php
  class Menu{
  
    //use MysqlConnection;   

    public static $current_item;
    
    public static $debug;
    public static $menus=array();

    private static $instance_count = 0;
    private static $instances = array();
    
    public $markup = array();

    public $item_active = []; // Declaración explícita de la propiedad
    public $menu_id;
    public $nested_menus = true;
    public $nested_submenus = false;
    public $show_all_submenus = false;
    public $items = array();
    public $ancestors = array();
    public $level = 0;
    public $more_items_id = 1000;

    /*
     * constructor
     * * */
    function __construct($menuId=0,$name='main'){
      global $_ARGS;
      $nl="\n";


      self::$current_item = MODULE=='page'?$_ARGS[1]??false:($_ARGS[2]?$_ARGS[2]:($_ARGS[1]?$_ARGS[1]:$_ARGS[0]));

      self::$instance_count++;
      //self::$menus[$menuId]=$name;
      $this->markup['header'] = '<ul class="menu">'; 
      $this->markup['item_link']  = '<li class="[CLASSES]" [ARIA]><a href="[URL]">[CAPTION]</a>[CHILDS]</li>';
      $this->markup['item_sep']   = '<li class="[CLASSES]" [ARIA]><span>[CAPTION]</span>[CHILDS]</li>';
      $this->markup['footer']     = '</ul>';
      $this->markup['separator']  = '';
      $this->markup['header_sub'] = '<ul class="submenu">';
      $this->markup['item_sub']   = '<li class="[CLASSES]" [ARIA]><a href="[URL]">[CAPTION]</a>[CHILDS]</li>';
      $this->markup['footer_sub'] = '</ul>';
     

      //OLD $this->item_active['name'] = self::$current_item; //$getItem;
      $this->item_active = ['name' => self::$current_item];

      $this->menu_id             = $menuId;             // 
      $this->nested_menus        = true;
      $this->nested_submenus     = false;
      $this->show_all_submenus   = false;
      $this->items               = array();    // array of items
      $this->ancestors           = array();
      $this->level               = 0;          // prevent too many levels
      $this->more_items_id       = 1000;
      //     $this->get_items();                      // populate array of items with only one sql command

      /*

      $this->markup['header'] = '<ul>'.$nl
                                  . '<li id="menu-mobile" class="NOnav-item">'.$nl
                                  . '<span id="link-menu" class="link"><i class="fa fa chevron-down"></i></span>'.$nl
                                  . '<div id="hmb"><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div></div>'.$nl
                                  . '</li>'.$nl; 
      $this->markup['item_link']  = '<li class="nav-item [CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link" href="[URL]">[CAPTION]</a></li>'.$nl;
      $this->markup['item_sep']   = '<li class="nav-item [CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link nolink">[CAPTION]</a></li>'.$nl;
      $this->markup['separator']  = '';
      $this->markup['footer']     = '</ul>';
      $this->markup['header_sub'] = '<ul class="sub-menu shadow" id="menu-[NAME]">'.$nl;
      $this->markup['item_sub']   = '<li class="[CLASSES]"><a id="link-[NAME]" data-id="[ID]" class="link" href="[URL]">[CAPTION]</a></li>'.$nl;
      $this->markup['footer_sub'] = '</ul>';


      $this->markup['header'] = ''; 
      $this->markup['item_link']  = '<a href="[URL]">[CAPTION]</a>';
      $this->markup['item_sep']   = '';
      $this->markup['footer']     = '';
      $this->markup['separator']  = '<br />';
      $this->markup['header_sub'] = '';
      $this->markup['item_sub']   = '';
      $this->markup['footer_sub'] = '';

      $this->markup['header'] = ''; 
      $this->markup['item_link']  = '<a href="[URL]">[CAPTION]</a>';
      $this->markup['item_sep']   = '';
      $this->markup['footer']     = '';
      $this->markup['separator']  = ' | ';
      $this->markup['header_sub'] = '';
      $this->markup['item_sub']   = '';
      $this->markup['footer_sub'] = '';
        */
        /*
        foreach($this->items as $k=>$v) {
            
            if ($v['parent']==$parent){ 

                $has_childs = $k<100 ? $this->get_children($k) : false;
                $classes = '';
                $classes .= ($this->item_active['name']==$v['name']) ? ' active' : '' ;
                
                if($has_childs)
                    foreach ($has_childs as $child){
                       if($this->item_active['name']==$child['name'] && $child['parent']==$k) $classes .= ' selected';
                    }
                
                if ($parent==0){
                  echo  '<li class="nav-item '.$classes.'">'
                     //  .'<a id="link-'.$v['name'].'" data-id="'.$v['name'].'" '.($v['url']?'class="link" href="'.$v['url'].'"':'class="link nolink"').'>'.$v['caption'].'</a>'.$nl;
                       .($v['url'] ? '<a id="link-'.$v['name'].'" data-id="'.$v['name'].'"  class="link" href="'.$v['url'].'">'.$v['caption'].'</a>'
                                   : '<span id="link-'.$v['name'].'" data-id="'.$v['name'].'"  class="link nolink">'.$v['caption'].'</span>'                  ).$nl;
                }else{
                  echo  '<li class="'.$classes.'">'
                       .'<a id="link-'.$v['name'].'" data-id="'.$v['name'].'" class="link" href="'.$v['url'].'">'.$v['caption'].'</a>'.$nl;
                }

                if($k<100)
                if ($has_childs){
                  $this->print_menu($k,$v['name']);
                }
                              
                echo '</li>'.$nl;   //NO

            }
        }
      */
      self::$instances[$menuId]=$this;

    }
    
    function __destruct() {
      self::$instance_count--;
      unset(self::$instances[$this->menu_id]);
    }

    public static function getInstanceCount() {
      return self::$instance_count;
    }

    public static function getInstance($id) {
      return self::$instances[$id];
    }
    
    function getSqlItems($menu_id){

        if ( CFG::$vars['site']['langs']['enabled']!==true ||  /*!$_SESSION['lang'] ||*/ $_SESSION['lang']=='es'){ //CFG::$vars['default_lang']){
                $field_url = 't.item_url AS url';   
                $field_caption = 't.item_caption AS caption';   
        }else{
                $field_url = "COALESCE(NULLIF(t.item_url_".$_SESSION['lang'].",''), t.item_url) AS url";
                $field_caption = "COALESCE(NULLIF(t.item_caption_".$_SESSION['lang'].",''), t.item_caption) AS caption";
        }

      $sql  = "SELECT t.item_id AS id,t.item_name AS name,$field_caption,t.item_parent AS parent,t.item_level,$field_url ";  //,t.id_module AS module
      $sql .= "FROM ".TB_ITEM." t ";
      $sql .= "WHERE t.id_menu={$menu_id} ";
      $sql .= "AND ( "; 
      $sql .= "  t.item_level<=100 "; 
   // $sql .= "  t.item_id NOT IN (SELECT DISTINCT(id_item) FROM ACL_ITEM_ROLES) ";
 

      if (isset($_SESSION['userid']) && $_SESSION['userid']>0) {
        $sql .= "  OR "; 
        $sql .= " t.item_id in(SELECT id_item FROM ACL_ITEM_ROLES "
              . " WHERE id_item=t.item_id AND id_role IN (SELECT id_role FROM ACL_USER_ROLES WHERE id_user=".$_SESSION['userid']."))"; 
      }

      $sql .= " ) ";
      $sql .= "AND t.item_active='1' ";
      $sql .= "AND t.item_visible='1' ";
      $sql .= "ORDER BY t.item_order ";
      return $sql;

    }
    
    /*
     * function: get all items
     * returns: array
     * * */
    function get_items(){
     // $useritemACL = new ACL($_SESSION['userid'],$_SESSION['filaitem']['item_id']); 
     // $userroles = $useritemACL->getUserRoles();
      //$itemroles = implode(",", $useritemACL->getItemRoles());
      $sql = $this->getSqlItems($this->menu_id);

      $rows = Table::sqlQuery($sql);
      if($rows){
        foreach($rows as $row){      
          $this->items[$row['id']] = $row;
          $this->items[$row['id']]['classes']='';
          $this->items[$row['id']]['ancestors']=false;
          $this->items[$row['id']]['caption']=t($row['caption']);
          /**/
          //$this->items[$row['id']]['url'] = Vars::mkUrl( $this->items[$row['id']]['url']);
          /******************************************************************************************************************
          if(CFG::$vars['site']['langs']['suffix'])  
              if (CFG::$vars['enable_langs']||CFG::$vars['site']['langs']['enabled'])
                  if(in_array(MODULE,['news','page']))               //FIX ¿remove line?
                      if ($_SESSION['lang']!==CFG::$vars['default_lang']) //CFG::$vars['default_lang']){
                          $this->items[$row['id']]['url'] .= '/'.$_SESSION['lang'];
          *****/
        //if(($row['name']==$this->item_active['name']) || ('./'.$this->item_active['name'].'/'.$_GET['op']==$row['url']) ){

        ///////////////////////////////////if($row['name']=='products') $this->items[$row['id']]['url']=MODULE_SHOP;

          if($row['name']==$this->item_active['name'] /***/|| $row['url']==$this->item_active['name']/***/){ 
            $this->item_active = $this->items[$row['id']] ;
          }
        }
        $rows = false;
        if(!$this->item_active) {throw new Exception('No item active.');}
        if(isset($this->item_active['id']))
            $this->item_active['ancestors'] = $this->get_ancestors($this->item_active['id']);
      }
      // Vars::debug_var('self::$current_item: '.self::$current_item);
      // Vars::debug_var('item_active[id]: '.$this->item_active['id']);
      // // Vars::debug_var($this->items[27]);
      // Vars::debug_var($this->item_active,'item_active');

    }
    /*
    function get_itemsXX(){
        global $items_translated;
     // $useritemACL = new ACL($_SESSION['userid'],$_SESSION['filaitem']['item_id']); 
     // $userroles = $useritemACL->getUserRoles();
      //$itemroles = implode(",", $useritemACL->getItemRoles());


      if ( CFG::$vars['enable_langs'] &&  $_SESSION['lang']!=CFG::$vars['default_lang']){
                $field_url = "COALESCE(NULLIF(t.item_url_".$_SESSION['lang'].",''), t.item_url) AS url";
                $field_caption = "COALESCE(NULLIF(t.item_caption_".$_SESSION['lang'].",''), t.item_caption) AS caption";
      }else{
                $field_url = 't.item_url AS url';   
                $field_caption = 't.item_caption AS caption';   
      }

      $sql  = "SELECT t.item_id AS id,t.item_name AS name,$field_caption,t.id_module AS module,t.item_parent AS parent,t.item_level,$field_url ";
      $sql .= "FROM ".TB_ITEM." t ";
      $sql .= "WHERE t.id_menu={$this->menu_id} ";
      $sql .= "AND ( "; 
      $sql .= "  t.item_level<=100 "; 

      if (isset($_SESSION['userid']) && $_SESSION['userid']>0) {
        $sql .= "  OR "; 
        $sql .= " t.item_id in(SELECT id_item FROM ACL_ITEM_ROLES "
              . " WHERE id_item=t.item_id AND id_role IN (SELECT id_role FROM ACL_USER_ROLES WHERE id_user=".$_SESSION['userid']."))"; 
      }

      $sql .= " ) ";
      $sql .= "AND t.item_active='1' ";
      $sql .= "AND t.item_visible='1' ";
      $sql .= "ORDER BY t.item_order ";

      $rows = Table::sqlQuery($sql);
      if ( CFG::$vars['enable_langs'] && is_array($items_translated[$_SESSION['lang']]) && count($items_translated[$_SESSION['lang']])>0 ) 
         $__items = array_merge(array_keys($items_translated[$_SESSION['lang']]),array_values($items_translated[$_SESSION['lang']]));
      else
         $__items = ['shop','page','news'];

      $_add_suffix = CFG::$vars['enable_langs'] && CFG::$vars['site']['langs']['suffix'] && CFG::$vars['default_lang']!=$_SESSION['lang'] && in_array(MODULE,$__items);

      foreach($rows as $row){      
        $this->items[$row['id']] = $row;
        $this->items[$row['id']]['ancestors']=false;
        $this->items[$row['id']]['caption']=t($row['caption']);
        if ($_add_suffix) 
            $this->items[$row['id']]['url'] .= '/'.$_SESSION['lang']; //str_replace('[LANG]','/'.$_SESSION['lang'],$this->items[$row['id']]['url'] );
      //if(($row['name']==$this->item_active['name']) || ('./'.$this->item_active['name'].'/'.$_GET['op']==$row['url']) ){

      ///////////////////////////////////if($row['name']=='products') $this->items[$row['id']]['url']=MODULE_SHOP;

        if($row['name']==$this->item_active['name']){ 
          $this->item_active = $this->items[$row['id']] ;
        }
      }
      $rows = false;
      if(!$this->item_active) {throw new Exception('No item active.');}
      $this->item_active['ancestors'] = $this->get_ancestors($this->item_active['id']);
    }
    */
    public function add_items($items){
       foreach($items as $item){     
           $item['id'] = ++$this->more_items_id;
           if(empty($item['name'])) $item['name']=Str::sanitizeName($item['caption']);
           $this->items[$item['id']] = $item;
       }
    }
    
    
    /*
     * function: items
     * returns: array of items
     * * */
    function items() {
      return $this->items;
    }    

    /*
     * function: get_item
     * returns: item (array)
     * * */
    function get_item($id){
      return $this->items[$id];
    }

    /*
     * function: get parent
     * returns: parent id (integer)
     * * */
    function get_parent($id){
      return $this->items[$id]['parent'];
      //return $this->get_item( $this->items[$id]['parent'] );
    }

    /*
     * function: is this node at the root of the tree?
     * returns: boolean
     * * */
    function is_root_node($id){
      return ($this->get_parent($id) == 0); 
    }

    /*
     * function: get label for $id
     * returns: string
     * * */
    function get_label($id) {
      return $this->items[$id]['caption'];
    }

    /*
     * function: get label for $id
     * returns: string
     * * */
    function get_name($id) {
      return $this->items[$id]['name'];
    }

    /*
     * function: get link for $id
     * returns: string
     * * */
    function get_link($id){
      return ($this->get_module($id)==MODULE_SEPARATOR) 
           ? Vars::mkUrl($this->items[$id]['url']) 
           : (  ($this->get_module($id)==MODULE_URL)
                ? $this->items[$id]['url']
                : Vars::mkUrl($this->items[$id]['name'])  );
    }


    /*
     * function: get module
     * returns: integer
     * * */
    function get_module($id){
      return $this->items[$id]['module'];
    }

    /*
     * function: get next level of menu tree
     * returns: array af items
     * * */
    function get_children($id){
      $children = array();
      reset($this->items);
      //while (list($k, $v) = each($this->items)) {
      //  if ($v['parent']==$id) $children[$k]=$v;
      //}   
      foreach ($this->items as $k=>$v){
        if ($v['parent']==$id) $children[$k]=$v;
      }

      return $children;   
    }

    /*
     * function: test whether this id is a branch or leaf
     * returns: true if have childs 
     * * */
    function get_type($id){
      return is_array( $this->get_children($id) );
    }

    /*
     * function: get_root_id
     * returns: 0st level id 
     * * */
    function get_root_id($id){
      if (++$this->level > 650) {throw new Exception('+++Too many levels: '.$this->level);}
      $root_id = $this->get_parent($id); 
      if ($root_id==0) 
        return $id;
      else 
        return $this->get_root_id($root_id);  
    }

    /*
     * function: is_ancestor
     * returns: true if $item_name ancestor of $id 
     * * */
    function is_ancestor($id,$item_name){
      if($this->items[$id]['ancestors']){
        foreach($this->items[$id]['ancestors'] as $k=>$v) {
          if ($v['name']==$item_name) return true;
        } 
      }
      return false;
    }

    /*
     * function: return a list of this node's parents
     * by travelling upwards all the way to the root of the tree
     * returns: array of items
     * * */
    function get_ancestors($id,$count=0){
      $parent = $this->get_parent($id);
      if(!$count) $this->ancestors = array();
      if($parent){
         $this->ancestors[]  = $this->get_item($parent);
         $this->get_ancestors($parent,$count+1);
         $this->items[$id]['ancestors']=array_reverse($this->ancestors);
         //return array_reverse($this->ancestors);
      }
    }
    
    /*
     * function: print typical breadcrumb
     * * */
    /*
    public function breadcrumb(){
      $sep = '<span class="breadcrumb-separator"></span>';
      echo '<ul>';
      echo '<li><a href="'.Vars::mkUrl('').'" title="Home"><i class="fa fa-home"></i></a></li>';
      if($this->items[$this->item_active['id']]['ancestors']){
        foreach($this->items[$this->item_active['id']]['ancestors'] as $k=>$v) {
          echo '<li>'.$sep.'<a href="'.$this->get_link($v['id']).'">'.$v['caption'].'</a></li>';
        } 
      }
      echo '<li>'.$sep.$this->item_active['caption'].'</li>';
      echo '</ul>';
    }     
    */

    public function crumbs(){
      //echo '<pre>'.print_r($this->item_active,true).'</pre>';
      $c = array();

      if(isset($this->item_active['id'])&&$this->items[$this->item_active['id']]['ancestors']){
        foreach($this->items[$this->item_active['id']]['ancestors'] as $k=>$v) {
          $c[$v['name']] = [$v['caption'],$v['name']];
        } 
      }
        
      $c[$this->item_active['name']] = [$this->item_active['caption']??$this->item_active['name']]; //false];
      return $c;
    }     
    

    /*
     * function: return an array of ancestors names
     * returns: array of item_names
     * * */
    public function get_ancestors_names($id){
      $result = array();
      if($this->items[$id]['ancestors']){
        foreach($this->items[$id]['ancestors'] as $k=>$v) {
          $result[] = $v['name'];
        } 
      }
      return $result;
    }     
    

    function print_menu($parent = 0,$selected=false,$print=true){  // $mode = [label|id]
       
        if(count($this->items)<1) $this->get_items();                      // populate array of items with only one sql command
 
        $ret = $parent==0 
            ? $this->markup['header']
            : str_replace('[NAME]',($selected?$selected:$parent),$this->markup['header_sub']);
        $n = 0;
        foreach($this->items as $k=>$v) {
            
            if ($v['parent']==$parent){ 

                $classes = $this->item_active['name']==$v['name'] ? ' active' : '' ;
 
                if($v['classes']) $classes .= ' '.$v['classes'];

                //$has_childs = $this->nested_menus && /* $k<100 ? */ $this->get_children($k) /* : false */;
                //if($has_childs){

                $has_childs = $this->nested_menus ? $this->get_children($k) : array();
                if($has_childs && count($has_childs) > 0){                    
                    $classes .= ' has-childs aria';    
                    $aria = 'aria-haspopup="true" aria-expanded="false"';
                    foreach ($has_childs as $child){
                       if($this->item_active['name']==$child['name'] && $child['parent']==$k) $classes .= ' selected';
                    }
                    $childs = $this->print_menu($k,$v['name'],false);
                 
                }else { 
                    $childs ='';
                    $aria = '';
                }

                if($n>0) $ret .= $this->markup['separator'];

                if ($parent==0){
                    if($v['url'])
                        $ret .=  str_replace(array('[CLASSES]','[NAME]','[ID]','[URL]','[CAPTION]','[CHILDS]','[ARIA]'),array($classes,$v['name'],$v['name'],$v['url'],$v['caption'],$childs,$aria),$this->markup['item_link']);
                    else
                        $ret .= str_replace(array('[CLASSES]','[NAME]','[ID]','[CAPTION]','[CHILDS]','[ARIA]'),array($classes,$v['name'],$v['name'],$v['caption'],$childs,$aria),$this->markup['item_sep']);
                }else{
                    if($v['url'])
                       $ret .= str_replace(array('[CLASSES]','[NAME]','[ID]','[URL]','[CAPTION]','[CHILDS]','[ARIA]'),array($classes,$v['name'],$v['name'],$v['url'],$v['caption'],$childs,$aria),$this->markup['item_sub']);
                    else
                       $ret .= str_replace(array('[CLASSES]','[NAME]','[ID]','[CAPTION]','[CHILDS]','[ARIA]'),array($classes,$v['name'],$v['name'],$v['caption'],$childs,$aria),$this->markup['item_sep']);
                }
            }
            $n++;
        }
        
        $ret .= $parent==0 ? $this->markup['footer'] : $this->markup['footer_sub']; 
        
        if ($print) echo $ret; else return $ret;

    }

  }
