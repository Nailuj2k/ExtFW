<?php
/* Auto created */

$tabla = new TableMysql('POST_COMMENTS');

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

$parent_id = new Field();
$parent_id->type      = 'int';
$parent_id->len       = 11;
$parent_id->fieldname = 'parent_id';
$parent_id->label     = 'Parent';
$parent_id->editable  = Administrador() ;
$parent_id->sortable  = true;
$parent_id->searchable  = true;
$parent_id->readonly  = true;
$tabla->addCol($parent_id);

$user_id = new Field();
$user_id->type      = 'int';
$user_id->len       = 11;
$user_id->fieldname = 'user_id';
$user_id->label     = 'User';
$user_id->editable  = Administrador() ;
$user_id->sortable  = true;
$user_id->searchable  = true;
$tabla->addCol($user_id);

$user_name = new Field();
$user_name->type      = 'varchar';
$user_name->len       = 100;
$user_name->fieldname = 'user_name';
$user_name->label     = 'User name';
$user_name->editable  = true ;
$user_name->sortable  = true;
$user_name->searchable  = true;
$tabla->addCol($user_name);

$user_email = new Field();
$user_email->type      = 'varchar';
$user_email->len       = 255;
$user_email->fieldname = 'user_email';
$user_email->label     = 'User email';
$user_email->editable  = true ;
$user_email->sortable  = true;
$user_email->searchable  = true;
$tabla->addCol($user_email);

$comment_text = new Field();
$comment_text->type      = 'textarea';
$comment_text->fieldname = 'comment_text';
$comment_text->label     = 'Comment text';
$comment_text->editable  = true ;
$comment_text->wysiwyg   = false;
$comment_text->searchable  = true;
$comment_text->hide  = true;
$tabla->addCol($comment_text);

$status = new Field();
$status->type      = 'select';
$status->len       = 1;
$status->values    = ['1'=>'Pending', '2'=>'Approved', '3' => 'Rejected', '4'=>'Spam'];
$status->fieldname = 'status';
$status->label     = 'Status';
$status->editable  = Administrador() ;
$status->filtrable  = true;
$status->default_value = '1';
$tabla->addCol($status);

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

$approved_by = new Field();
$approved_by->type      = 'int';
$approved_by->len       = 11;
$approved_by->fieldname = 'approved_by';
$approved_by->label     = 'Aprobado por';
$approved_by->editable  = Administrador() ;
$approved_by->sortable  = true;
$approved_by->searchable  = true;
$tabla->addCol($approved_by);

$approved_at = new Field();
$approved_at->type      = 'datetime';
$approved_at->fieldname = 'approved_at';
$approved_at->label     = 'Aprobado en';
$approved_at->editable  = false ;
$approved_at->sortable  = true;
$approved_at->searchable  = true;
$approved_at->filtrable  = true;
$approved_at->readonly  = true;
$tabla->addCol($approved_at);

$vote_up = new Field();
$vote_up->type      = 'int';
$vote_up->len       = 11;
$vote_up->fieldname = 'votes_up';
$vote_up->label     = 'Up';
$vote_up->editable  = Administrador() ;
$vote_up->sortable  = true;
$vote_up->searchable  = true;
$tabla->addCol($vote_up);

$vote_down = new Field();
$vote_down->type      = 'int';
$vote_down->len       = 11;
$vote_down->fieldname = 'votes_down';
$vote_down->label     = 'Down';
$vote_down->editable  = Administrador() ;
$vote_down->sortable  = true;
$vote_down->searchable  = true;
$tabla->addCol($vote_down);

$vote_meh = new Field();
$vote_meh->type      = 'int';
$vote_meh->len       = 11;
$vote_meh->fieldname = 'votes_meh';
$vote_meh->label     = 'Se la suda';
$vote_meh->editable  = Administrador() ;
$vote_meh->sortable  = true;
$vote_meh->searchable  = true;
$tabla->addCol($vote_meh);

$tabla->name = 'POST_COMMENTS';
$tabla->title = 'POSTCOMMENTS';
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
/*
class POST_COMMENTSEvents extends defaultTableEvents implements iEvents{
  function OnInsert($owner,&$result,&$post) { 
      //$result['error'] = 5;
      //$result['msg'] = '¡Esto es el evento OnInsert!';
  }
  function OnUpdate($owner,&$result,&$post) { 
      //$result['error'] =5;
      //$result['msg'] = '¡Esto es el evento OnUpdate! ';
  }
  function OnDelete($owner,&$result,$id)    { 
      //$result['error'] =5;
      //$result['msg'] = '¡Esto es el evento OnDelete!';
  }
}
$tabla->events = New POST_COMMENTSEvents();



*/