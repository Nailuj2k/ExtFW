<?php 

$tabla = new TableMysql('CFG_CFG');

$id            = new Field();
$id->fieldname = 'ID';
$id->type      = 'int';
$id->len       = 5;
$id->width     = 25;
$id->label     = 'Id';   
$id->pk     = true;   

$key = new Field();
$key->fieldname = 'K';
$key->type      = 'varchar';
$key->len       = 50;
$key->width     = 240;
$key->label     = 'Clave';   
$key->readonly  = (!Root());
$key->editable  = Root();
$key->searchable  = true;

$val = new Field();
$val->fieldname = 'V';
$val->type      = 'varchar';
$val->len       = 400;
$val->width     = 350;
$val->label     = 'Valor';   
$val->editable  = true;
$val->searchable  = true;

$description = new Field();
$description->type       = 'textarea';
$description->len        = 200;
$description->fieldname  = 'DESCRIPTION';
$description->label      = 'Descripción';   
$description->editable   = true;
$description->searchable = true;
$description->width      = 350;
$description->classname  = 'fullname';
$description->wysiwyg=false;

$tabla->name = 'CFG_cfg';
$tabla->title   = 'Configuración';
$tabla->showtitle = false;
$tabla->verbose=false;
//$tabla->cache = false;
$tabla->output='table';
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['settings']['options'],'num_rows',20);
$tabla->page = $page;

$tabla->addCol($id);
$tabla->addCol($key);
$tabla->addCol($val);
$tabla->addCol($description);
$tabla->addWhoColumns();
$tabla->addActiveCol();

$tabla->orderby = 'ID DESC';

$tabla->perms['view']   = true;
$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();  

class cfgEvents extends defaultTableEvents implements iEvents{ 
  
  function resetArrayValues(){
    $_SESSION['_CACHE']['values']['CFG_cfg'] = false; 
    $_SESSION['_CACHE']['values']['CFG_cfg_all'] = false;
  }
  
  function OnInsert($owner,&$result,&$post) { $this->resetArrayValues(); }
  
  function OnUpdate($owner,&$result,&$post) { $this->resetArrayValues(); 
     
      if($post['K']=='site.langs.enabled' && $post['V']=='true'){
          $result['msg'] = 'Recuerde que debe actualizar las tablas con campos traducibles';
      }

  }
  
  function OnDelete($owner,&$result,$id)    { $this->resetArrayValues(); }

  function OnPrint($owner, $template, $_item_tags, $_item_values){
    echo 'Accesso denegado';
  }

  function OnAfterCreate($owner){ 
    if($owner->recordCount()<1){
      /*
      $owner->sql_query("INSERT INTO {$owner->tablename} (K,V) VALUES('smtp_from_name','Nombre')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (K,V) VALUES('smtp_from_email','nombredeusuario@gmail.com')");
      
      $owner->sql_query("INSERT INTO {$owner->tablename} (K,V) VALUES('smtp_server','smtp.gmail.com')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (K,V) VALUES('smtp_user','nombredeusuario@gmail.com')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (K,V) VALUES('smtp_password','incorrecta')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (K,V,DESCRIPTION) VALUES('smtp_port','465','25 para smtp normal, 465 o 587 para gmail, 587 opara tls')");
      $owner->sql_query("INSERT INTO {$owner->tablename} (K,V,DESCRIPTION) VALUES('smtp_ssl','0','1 para gmail')");
      */
      //$owner->sql_query("INSERT INTO {$owner->tablename} (K,V) VALUES('imap_server','imap.gmail.com')");
      //$owner->sql_query("INSERT INTO {$owner->tablename} (K,V) VALUES('imap_port','993')");
      //$owner->sql_query("INSERT INTO {$owner->tablename} (K,V,DESCRIPTION) VALUES('imap_ssl','0','1 para gmail')");
      //$owner->sql_query("INSERT INTO {$owner->tablename} (K,V) VALUES('imap_password','incorrecta')");
      
    }
  }

}

$tabla->events = New cfgEvents();