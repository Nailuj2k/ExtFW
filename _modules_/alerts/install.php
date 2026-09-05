<?php

  //$_ACL->addPermission('alerts_view');
  //$_ACL->addPermission('alerts_add');
    $_ACL->addPermission('alerts_edit');
  //$_ACL->addPermission('alerts_delete');

  //$_ACL->addRolePerm('Administradores','alerts_view');
  //$_ACL->addRolePerm('Administradores','alerts_add');
    $_ACL->addRolePerm('Administradores','alerts_edit');
  //$_ACL->addRolePerm('Administradores','alerts_delete');


// Table::sqlExec("UPDATE CFG_ALERTS SET D4TE_END = DATE_ADD(D4TE, INTERVAL 5 DAY) WHERE D4TE_END IS NULL AND ACTIVE!='1'");