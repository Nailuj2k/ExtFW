<?php

    $sqls[] = "DROP TABLE IF EXISTS ".TB_ACL_PERMISSIONS;
    $sqls[] = "DROP TABLE IF EXISTS ".TB_ACL_ROLES;
    $sqls[] = "DROP TABLE IF EXISTS ".TB_ACL_ROLE_PERMS;
    $sqls[] = "DROP TABLE IF EXISTS ".TB_ACL_USER_PERMS;
    $sqls[] = "DROP TABLE IF EXISTS ".TB_ACL_USER_ROLES;
    $sqls[] = "DROP TABLE IF EXISTS ".TB_ACL_ITEM_ROLES;
     
    $sqls[] = "CREATE TABLE ".TB_ACL_PERMISSIONS." (
    permission_id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    permission_key VARCHAR(30) NOT NULL,
    permission_name VARCHAR(30) NOT NULL, 
    PRIMARY KEY  (permission_id),
    UNIQUE KEY  (permission_key)) 
    ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"; 
    /*  
    $sqls[] = "CREATE TABLE ".TB_ACL_PERMISSIONS." (
    permission_id INTEGER PRIMARY KEY,
    permission_key VARCHAR(30) NOT NULL,
    permission_name VARCHAR(30) NOT NULL)"; 
    */
    $sqls[] = "CREATE TABLE ".TB_ACL_ROLES." (
    role_id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL,
    role_type INT(5) DEFAULT '1',
    description TEXT,
    filtrable INT(1) DEFAULT '1',
    PRIMARY KEY  (role_id), 
    UNIQUE KEY (role_name)) 
    ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"; 

    $sqls[] = "CREATE TABLE ".TB_ACL_ROLE_PERMS." (
    role_perm_id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    id_role INT(7) UNSIGNED NOT NULL,
    id_permission INT(7) UNSIGNED NOT NULL,
    role_perm_value tinyINT(1) NOT NULL default '0',
    role_perm_add_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    PRIMARY KEY  (role_perm_id),
    UNIQUE KEY (id_role,id_permission)) 
    ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"; 

    $sqls[] = "CREATE TABLE ".TB_ACL_USER_PERMS." (
    user_perm_id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    id_user INT(7) UNSIGNED NOT NULL, 
    id_permission INT(7) UNSIGNED NOT NULL,
    user_perm_value tinyINT(1) NOT NULL default '0', 
    user_perm_add_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    PRIMARY KEY  (user_perm_id), 
    UNIQUE KEY (id_user,id_permission)) 
    ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"; 

    $sqls[] = "CREATE TABLE ".TB_ACL_USER_ROLES." (
    user_role_id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    id_user INT(7) UNSIGNED NOT NULL,
    id_role INT(7) UNSIGNED NOT NULL,
    user_role_add_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (user_role_id), 
    UNIQUE KEY (id_user,id_role)) 
    ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"; 

    $sqls[] = "CREATE TABLE ".TB_ACL_ITEM_ROLES." (
    item_role_id INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    id_item INT(7) NOT NULL,
    id_role INT(7) UNSIGNED NOT NULL,
    item_role_add_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (item_role_id),
    UNIQUE KEY (id_item,id_role))
    ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"; 

    // -- Rows for table ACL_ROLES

    $sqls[] =  "INSERT INTO ACL_ROLES (role_name,filtrable) VALUES
    ('Root', 1), 
    ('Administradores', 1)";

    /**
    $sqls[] =  "INSERT INTO ACL_ROLES (role_name,filtrable) VALUES
    ('Administradores', 1), 
    ('Todos los usuarios', 1), 
    ('Autores', 1), 
    ('Editores', 1), 
    ('Administrar tienda', 1), 
    ('Forum_Admin', 1), 
    ('Forum_Moderator', 1), 
    ('Forum_User', 1), 
    ('Clientes', 1), 
    ('Comercial', 1)";
    **/

   // -- Rows for table ACL_PERMISSIONS

    $sqls[] =  "INSERT INTO ACL_PERMISSIONS (permission_key,permission_name) VALUES
    ('access_site', 'Accesso  normal'), 
    ('access_admin', 'Control de accesos'), 
    ('edit_items', 'Páginas. Editar'), 
    ('add_items', 'Páginas. Añadir'), 
    ('delete_items', 'Páginas. Eliminar'), 
    ('log_view', 'Visor de logs'), 
    ('update_system', 'Actualizar Framework'),
    ('site_edit', 'Editar código'),
    ('files_edit', 'Adjuntar archivos')
    ";
    /**
    $sqls[] =  "INSERT INTO ACL_PERMISSIONS (permission_key,permission_name) VALUES
    ('access_site', 'Accesso  normal'), 
    ('access_admin', 'Control de accesos'), 
    ('edit_items', 'Páginas. Editar'), 
    ('add_items', 'Páginas. Añadir'), 
    ('delete_items', 'Páginas. Eliminar'), 
    ('news_admin', 'Noticias administrar'), 
    ('eventos_admin', 'Eventos administrar'), 
    ('news_edit', 'Noticias editar'), 
    ('eventos_edit', 'Eventos editar'), 
    ('news_add', 'Noticias añadir'), 
    ('eventos_add', 'Eventos añadir'), 
    ('tienda_edit', 'Tienda. Editar artículos'), 
    ('tienda_add', 'Tienda. Añadir artículos'), 
    ('tienda_delete', 'Tienda. Eliminar artículos'), 
    ('forum_forum_add', 'forum_forum_add'), 
    ('forum_forum_edit', 'forum_forum_edit'), 
    ('forum_topic_add', 'forum_topic_add'), 
    ('forum_topic_edit', 'forum_topic_edit'), 
    ('forum_post_add', 'forum_post_add'), 
    ('forum_post_edit', 'forum_post_edit'),
    ('pedidos_admin', 'Gestión de Pedidos'), 
    ('productos_admin', 'Gestión de productos'), 
    ('newsletters_admin', 'Gestión de newsletters'), 
    ('sliders_admin', 'Gestión de sliders'), 
    ('log_view', 'Visor de logs'), 
    ('update_system', 'Actualizar Framework'),
    ('site_edit', 'Editar código')
    ";
    **/

    // -- Rows for table ACL_ROLE_PERMS

    $sqls[] =  "INSERT INTO ACL_ROLE_PERMS (id_role,id_permission,role_perm_value) VALUES
    (1, 1, 1), 
    (1, 2, 1), 
    (1, 3, 1), 
    (1, 4, 1), 
    (1, 5, 1), 
    (1, 6, 1), 
    (1, 7, 1), 
    (1, 8, 1), 
    (1, 9, 1)";


    // -- No hay filas en la tabla ACL_USER_PERMS

    // -- Rows for table ACL_USER_ROLES

    $sqls[] =  "INSERT INTO ACL_USER_ROLES (id_user,id_role) VALUES
    (1, 1), 
    (1, 2)";


    // -- Rows for table ACL_ITEM_ROLES
    /*
    $sqls[] =  "INSERT INTO ACL_ITEM_ROLES (id_item,id_role) VALUES
    (1, 1), 
    (2, 1), 
    (3, 1)";
    */