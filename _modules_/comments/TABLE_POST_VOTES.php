<?php

$tabla = new TableMysql('POST_VOTES');

$id = new Field();
$id->type      = 'int';
$id->len       = 10;
$id->fieldname = 'id';
$id->label     = 'Id';
$id->editable  = false ;
$id->sortable  = true;
$tabla->addCol($id);

$module_id = new Field();
$module_id->type      = 'int';
$module_id->len       = 10;
$module_id->fieldname = 'module_id';
$module_id->label     = 'Mod';
$module_id->editable  = Administrador() ;
$module_id->sortable  = true;
$module_id->searchable  = true;
$module_id->readonly  = true;
$tabla->addCol($module_id);

$post_id = new Field();
$post_id->type      = 'int';
$post_id->len       = 11;
$post_id->fieldname = 'post_id';
$post_id->label     = 'Post';
$post_id->editable  = Administrador() ;
$post_id->sortable  = true;
$post_id->searchable  = true;
$post_id->readonly  = true;
$tabla->addCol($post_id);

$comment_id = new Field();
$comment_id->type      = 'int';
$comment_id->len       = 11;
$comment_id->fieldname = 'comment_id';
$comment_id->label     = 'Comment';
$comment_id->editable  = Administrador() ;
$comment_id->sortable  = true;
$comment_id->searchable  = true;
$comment_id->readonly  = true;
$tabla->addCol($comment_id);

$user_id = new Field();
$user_id->type      = 'int';
$user_id->len       = 11;
$user_id->fieldname = 'user_id';
$user_id->label     = 'User';
$user_id->editable  = Administrador() ;
$user_id->sortable  = true;
$user_id->searchable  = true;
$tabla->addCol($user_id);

$vote_type = new Field();
$vote_type->type      = 'select';
$vote_type->len       = 1;
$vote_type->values    = ['0'=>'unset', '1'=>'up', '2'=>'down', '3' => 'meh', '4'=>'spam'];
$vote_type->fieldname = 'vote_type';
$vote_type->label     = 'vote';
$vote_type->editable  = Administrador() ;
$vote_type->filtrable  = true;
$vote_type->default_value = '1';
$tabla->addCol($vote_type);

$ip_address = new Field();
$ip_address->type      = 'varchar';
$ip_address->len       = 45;
$ip_address->fieldname = 'ip_address';
$ip_address->label     = 'Ip address';
$ip_address->editable  = false ;
$ip_address->sortable  = true;
$ip_address->searchable  = true;
$tabla->addCol($ip_address);

$created_at = new Field();
$created_at->type      = 'datetime';
$created_at->fieldname = 'created_at';
$created_at->label     = 'Created at';
$created_at->editable  = false ;
$created_at->sortable  = true;
$created_at->searchable  = true;
$created_at->filtrable  = true;
$created_at->readonly  = true;
$tabla->addCol($created_at);
 
$tabla->name = 'POST_VOTES';
$tabla->title = 'POSTVOTES';
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

$tabla->orderby = 'created_at DESC';