<?php

class Tabs{

    const TAB_HEADER = '<div class="form-tabs" id="ftabs-%s" data-simpletabs><ul>%s</ul>';
    const TAB_TAB_TAB = '<li><a href="#ftab-%s" id="tab-id-%s">%s</a></li>';
    const TAB_TAB_BEGIN = '<div id="ftab-%s">';
    const TAB_TAB_END = '</div>';
    const TAB_FOOTER = '</div>'; //<script type="text/javascript">$(function(){$("#ftabs-%s").tabs();});</script>';
    
    private $id;
    private $tabs_buttons = array();

    public function __construct($id){
       $this->id = $id; 
    }

    public function addTab($caption,$id){
        if(!$id)   $id   = $this->id.'-'.Str::sanitizeName($caption);
        $this->tabs_buttons[] = sprintf(self::TAB_TAB_TAB,$id,$id,$caption);
    }

    public function begin(){
        echo sprintf(self::TAB_HEADER,$this->id ,implode('',$this->tabs_buttons));  //#ftabs-code-tabs
    }

    public function beginTab($id){
        echo sprintf(self::TAB_TAB_BEGIN,$id);
    }

    public function endTab(){
        echo self::TAB_TAB_END;
    }

    public function end(){
        echo sprintf(self::TAB_FOOTER,$this->id);
    }  

}



/**
 * 
 *   Ejemplo de uso:
 *   
 *   $tab_user = new Tabs('tab_user');
 *   
 *   $tab_user->addTab('Archivos','tab_user_files');
 *   $tab_user->addTab('Direcciones','tab_user_addresses');                         
 *   $tab_user->addTab('Dispositivos','tab_user_keys');
 *   
 *   $tab_user->begin();
 *   
 *   $tab_user->beginTab('tab_user_files');
 *       echo 'tab content';
 *       Table::show_table('CLI_USER_FILES'); 
 *   $tab_user->endTab();
 *    
 *   $tab_user->beginTab('tab_user_addresses');
 *      echo 'tab content';
 *      Table::show_table('CLI_USER_ADDRESSES');      
 *   $tab_user->endTab();
 *
 *   $tab_user->beginTab('tab_user_keys');
 *      echo 'tab content';
 *      Table::show_table('CLI_USER_KEYS');      
 *   $tab_user->endTab();
 *
 *   $tab_user->end();
 * 
 * 
 * 
 * 
 */