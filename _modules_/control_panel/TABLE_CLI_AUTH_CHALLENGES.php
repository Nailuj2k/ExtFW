<?php


/**
 * 


CREATE TABLE IF NOT EXISTS `CLI_AUTH_CHALLENGES` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `challenge` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` INT NOT NULL,
    `expires_at` INT NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_challenge` (`challenge`),
    KEY `idx_user_expires` (`user_id`, `expires_at`),
    KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



 * 
 * 
 */
$tabla = new TableMysql('CLI_AUTH_CHALLENGES');

$id = new Field();
$id->type      = 'int';
$id->len       = 10;
$id->fieldname = 'id';
$id->label     = 'Id';
$id->editable  = false ;
$id->sortable  = true;
$id->searchable  = true;
$tabla->addCol($id);

$user_id = new Field();
$user_id->type      = 'int';
$user_id->len       = 10;
$user_id->fieldname = 'user_id';
$user_id->label     = 'User';
$user_id->editable  = false ;
$user_id->sortable  = true;
$user_id->searchable  = true;
$tabla->addCol($user_id);

$challenge = new Field();
$challenge->type      = 'varchar';
$challenge->len       = 100;
$challenge->fieldname = 'challenge';
$challenge->label     = 'Challenge';
$challenge->editable  = false ;
$challenge->sortable  = true;
$challenge->searchable  = true;
$tabla->addCol($challenge);

$ip_address = new Field();
$ip_address->type      = 'varchar';
$ip_address->len       = 45;
$ip_address->fieldname = 'ip_address';
$ip_address->label     = 'Ip address';
$ip_address->editable  = false ;
$ip_address->sortable  = true;
$ip_address->searchable  = true;
$tabla->addCol($ip_address);

$user_agent = new Field();
$user_agent->type      = 'varchar';
$user_agent->len       = 255;
$user_agent->fieldname = 'user_agent';
$user_agent->label     = 'User agent';
$user_agent->editable  = false ;
$user_agent->sortable  = true;
$user_agent->searchable  = true;
$tabla->addCol($user_agent);

$created_at = new Field();
$created_at->type      = 'int';
$created_at->len       = 11;
$created_at->fieldname = 'created_at';
$created_at->label     = 'Created at';
$created_at->editable  = false ;
$created_at->sortable  = true;
$created_at->searchable  = true;
$tabla->addCol($created_at);

$expires_at = new Field();
$expires_at->type      = 'int';
$expires_at->len       = 11;
$expires_at->fieldname = 'expires_at';
$expires_at->label     = 'Expires at';
$expires_at->editable  = false ;
$expires_at->sortable  = true;
$expires_at->searchable  = true;
$tabla->addCol($expires_at);

$used = new Field();
$used->type      = 'int';
$used->len       = 1;
$used->fieldname = 'used';
$used->label     = 'Used';
$used->editable  = false ;
$used->sortable  = true;
$used->searchable  = true;
$tabla->addCol($used);

$tabla->name = 'CLI_AUTH_CHALLENGES';
$tabla->title = 'CLIAUTHCHALLENGES';
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 10;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->perms['delete'] = Administrador();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['filter'] = true;
$tabla->perms['view']   = true;


class CLI_AUTH_CHALLENGESEvents extends defaultTableEvents implements iEvents{
  function OnInsert($owner,&$result,&$post) { 
      $result['error'] = 0;
      $result['msg'] = '¡Esto es el evento OnInsert!';
  }
  function OnUpdate($owner,&$result,&$post) { 
      $result['error'] =0;
      $result['msg'] = '¡Esto es el evento OnUpdate! ';
  }
  function OnDelete($owner,&$result,$id)    { 
      $result['error'] =5;
      $result['msg'] = '¡Esto es el evento OnDelete!';
  }
}
$tabla->events = New CLI_AUTH_CHALLENGESEvents();



