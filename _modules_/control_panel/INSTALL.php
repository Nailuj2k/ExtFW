<?php

        function runsql($sql){
            echo 'Ejecutando <span style="color:#3399CC;">'.mb_strimwidth($sql, 0, 100, "...").' ....</span>';
            $res=Table::sqlExec($sql);
            echo $res ? '  <span style="color:green"><b>OK</b></span><br />' : '  <span style="color:red;"><b>ERROR './*Table::lastError().*/'</b></span><br>';  
            // if($res) return Table::lastInsertId();
        }



/******

DELETE FROM CLI_ORDER_LINES WHERE ORDER_LINE_ID>1;
DELETE FROM CLI_ORDERS WHERE ORDER_ID > 1000;

*******/

/*
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_acl_item_roles TO '.TB_ACL_ITEM_ROLES);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_acl_permissions TO '.TB_ACL_PERMISSIONS);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_acl_roles TO '.TB_ACL_ROLES);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_acl_role_perms TO '.TB_ACL_ROLE_PERMS);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_acl_user_perms TO '.TB_ACL_USER_PERMS);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_acl_user_roles TO '.TB_ACL_USER_ROLES);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_cc TO '.TB_CC);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_item TO '.TB_ITEM);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_lang TO '.TB_LANG);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_str TO '.TB_STR);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_user TO '.TB_USER);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_user_contacts TO '.TB_USER_CONTACTS);
Table::sqlExec('RENAME TABLE '.$cfg['prefix'].'_user_files TO '.TB_USER_FILES);
*/

?><pre><?php 


if (Administrador()) {

    if ($_ARGS[2]=='root'){

        $_ACL->addRole('Root');
        $_ACL->updateUserRole(1,'Root',true);

       //   $str_sql_add_user_role = "INSERT INTO ".TB_ACL_USER_ROLES." (id_user,id_role) VALUES( 1 , 11)";

    }else  if ($_ARGS[2]=='refactor'){

        $changes = ACL::migrateVarName(false);
        echo '<h3>Refactor changes:</h3>';
        echo '<pre>';
        print_r($changes);
        echo '</pre>';
        
    }else  if ($_ARGS[2]=='rmbak'){

        include(SCRIPT_DIR_MODULE . '/functions.php');
        rmdirr('_bak_',true);

    }else  if ($_ARGS[2]=='county'){

       // Update: ALTER TABLE CLI_ORDERS CHANGE INVOICE_ID_COUNTY INVOICE_ID_COUNTY VARCHAR(50);

        // SELECT a.ID_COUNTY ,l.localidad_name FROM CLI_USER_ADDRESSES a,CFG_LOCALIDAD l WHERE a.ID_COUNTY=l.localidad_id
        runsql( "ALTER TABLE CLI_ORDERS CHANGE ID_COUNTY ID_COUNTY VARCHAR(50)" );
        runsql( "ALTER TABLE CLI_ORDERS CHANGE INVOICE_ID_COUNTY INVOICE_ID_COUNTY VARCHAR(50)" );
        runsql( "ALTER TABLE CLI_USER_ADDRESSES CHANGE ID_COUNTY ID_COUNTY VARCHAR(50)" );
        runsql( "UPDATE CLI_USER_ADDRESSES a,CFG_LOCALIDAD l SET a.ID_COUNTY = l.localidad_name WHERE l.localidad_id=a.ID_COUNTY" );
        runsql( "UPDATE CLI_ORDERS a,CFG_LOCALIDAD l SET a.ID_COUNTY = l.localidad_name WHERE l.localidad_id=a.ID_COUNTY" );
        runsql( "UPDATE CLI_ORDERS a,CFG_LOCALIDAD l SET a.INVOICE_ID_COUNTY = l.localidad_name WHERE l.localidad_id=a.INVOICE_ID_COUNTY" );
       //UPDATE CLI_USER_ADDRESSES a,CLI_USER_ADDRESSES2 l SET a.ID_COUNTY = l.ID_COUNTY WHERE l.USER_ADDRESS_ID=a.USER_ADDRESS_ID

    }else  if ($_ARGS[2]=='areas'){

        $_ACL->addPermission('areas_view','',true);       //$_ACL->userHasRoleName('Area_Admin');
        $_ACL->addPermission('areas_edit','',true);
        $_ACL->addPermission('areas_add','',true);
        $_ACL->addPermission('areas_delete','',true);
        $_ACL->addRolePerm('Administradores','areas_view',true);
        $_ACL->addRolePerm('Administradores','areas_edit',true);
        $_ACL->addRolePerm('Administradores','areas_add',true);
        $_ACL->addRolePerm('Administradores','areas_delete',true);

    }else  if ($_ARGS[2]=='customers'){

        //    Table::show_table('CLI_PRODUCTS_TAGS');     

        /*    
        $_ACL->addRole('Clientes');

        $_ACL->addPermission('banners_add');
        $_ACL->addPermission('banners_edit');
        $_ACL->addPermission('banners_delete');

        $_ACL->addRolePerm('Clientes','banners_add');
        $_ACL->addRolePerm('Clientes','banners_edit');
        $_ACL->addRolePerm('Clientes','banners_delete');
        */
        // userUpdate();

        /* 
        $sqls = array();

        if (CFG::$vars['erp']['enabled']===true){ 

            $sqls[] = "DROP VIEW IF EXISTS CLI_CUSTOMERS";
 
        }else if ( CFG::$vars['shop']['enabled']===true){

            //$sqls[] = "DROP TABLE CLI_TAGS";
            $sqls[] = "DROP VIEW IF EXISTS CLI_CUSTOMERS";

            $sqls[] = "CREATE VIEW CLI_CUSTOMERS AS  
             SELECT * 
               FROM ".TB_USER."
                 WHERE user_id IN (SELECT id_user 
                                   FROM ".TB_ACL_USER_ROLES." 
                                   WHERE id_role
                                   IN (SELECT role_id 
                                          FROM ".TB_ACL_ROLES." WHERE role_name IN ('Clientes')
                                      )
                                  )";

        }else{
            ?><p>Setting 'erp.enabled' or 'shop.enabled' must be set for this operation.</p><?php
        }

        foreach ($sqls as $sql){  
            runsql($sql);  
            //Vars::debug_var($sql);  
        }
        */
        /******
        $n = 10;
        while ($n<65){
        $n++;
        $sqls[] = 'INSERT INTO ACL_USER_ROLES (id_user,id_role) VALUES ('.$n.',9)';
        }
        *********/


       // }else  if ($_ARGS[2]=='page_files'){

      //            $sql_sqls[]="ALTER TABLE CLI_PAGES_FILES CHANGE file_id ID int(10) NOT NULL AUTO_INCREMENT";
      //            $sql_sqls[]="ALTER TABLE CLI_PAGES_FILES CHANGE file_name FILE_NAME varchar(200)";

    }else  if ($_ARGS[2]=='countries'){

        /*******/
        // -- Create table CFG_PAIS

        $sqls[] = "DROP TABLE IF EXISTS `CFG_PAIS`";

        $sqls[] = "CREATE TABLE `CFG_PAIS` (
        `pais_id` int(3) NOT NULL auto_increment,
        `pais_name` varchar(100),
        ACTIVE int(1),
        PRIMARY KEY (`pais_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        // -- Rows for table CFG_PAIS

        $sqls[] =  "INSERT INTO CFG_PAIS (pais_id,pais_name) VALUES
        (4, 'Afganistan'), 
        (8, 'Albania'), 
        (276, 'Alemania'), 
        (20, 'Andorra'), 
        (24, 'Angola'), 
        (660, 'Anguilla'), 
        (807, 'Ant.rep.yugoslavia De Macedonia'), 
        (28, 'Antigua Y Barbuda'), 
        (530, 'Antillas Neerlandesas'), 
        (682, 'Arabia Saudita'), 
        (12, 'Argelia'), 
        (32, 'Argentina'), 
        (533, 'Aruba'), 
        (36, 'Australia'), 
        (40, 'Austria'), 
        (31, 'Azerbaijan'), 
        (44, 'Bahamas'), 
        (48, 'Bahrein'), 
        (50, 'Bangladesh'), 
        (52, 'Barbados'), 
        (56, 'Belgica'), 
        (84, 'Belice'), 
        (204, 'Benin'), 
        (60, 'Bermudas'), 
        (64, 'Bhutan'), 
        (112, 'Biolorrusia'), 
        (68, 'Bolivia'), 
        (70, 'Bosnia Herzegovina'), 
        (72, 'Botsuana'), 
        (76, 'Brasil'), 
        (96, 'Brunei'), 
        (100, 'Bulgaria'), 
        (854, 'Burkinafaso'), 
        (108, 'Burundi'), 
        (116, 'Camboya (kampuchea)'), 
        (120, 'Camerun'), 
        (124, 'Canada'), 
        (148, 'Chad'), 
        (152, 'Chile'), 
        (156, 'China'), 
        (196, 'Chipre'), 
        (336, 'Ciudad Del Vaticano'), 
        (170, 'Colombia'), 
        (174, 'Comoras'), 
        (178, 'Congo'), 
        (408, 'Corea Del Norte'), 
        (410, 'Corea Del Sur'), 
        (384, 'Costa De Marfil'), 
        (188, 'Costa Rica'), 
        (191, 'Croacia'), 
        (192, 'Cuba'), 
        (208, 'Dinamarca'), 
        (212, 'Dominica'), 
        (218, 'Ecuador'), 
        (818, 'Egipto'), 
        (222, 'El Salvador'), 
        (784, 'Emiratos Arabes Unidos'), 
        (232, 'Eritrea'), 
        (705, 'Eslovenia'), 
        (724, 'España'), 
        (840, 'Estados Unidos De America'), 
        (233, 'Estonia'), 
        (231, 'Etiopia'), 
        (242, 'Fiji(fidji)'), 
        (608, 'Filipinas'), 
        (246, 'Finlandia'), 
        (250, 'Francia'), 
        (266, 'Gabon'), 
        (270, 'Gambia'), 
        (268, 'Georgia'), 
        (288, 'Ghana'), 
        (292, 'Gibraltar'), 
        (308, 'Granada'), 
        (300, 'Grecia'), 
        (304, 'Groenlandia'), 
        (312, 'Guadalupe'), 
        (316, 'Guam'), 
        (320, 'Guatemala'), 
        (254, 'Guayana Francesa'), 
        (324, 'Guinea'), 
        (226, 'Guinea Ecuatorial'), 
        (624, 'Guineabisseau'), 
        (328, 'Guyana'), 
        (332, 'Haiti'), 
        (340, 'Honduras'), 
        (344, 'Hong Kong'), 
        (348, 'Hungria'), 
        (356, 'India'), 
        (360, 'Indonesia'), 
        (364, 'Iran'), 
        (368, 'Iraq'), 
        (372, 'Irlanda'), 
        (74, 'Isla Bouvet'), 
        (833, 'Isla De Man'), 
        (239, 'Isla Georgias Y Sandwich Del Sur'), 
        (480, 'Isla Mauricio'), 
        (574, 'Isla Norfolk'), 
        (690, 'Isla Seychelles'), 
        (772, 'Isla Tokelau'), 
        (352, 'Islandia'), 
        (248, 'Islas Aland'), 
        (136, 'Islas Caiman'), 
        (166, 'Islas Cocos (cocos Islands)'), 
        (184, 'Islas Cook'), 
        (234, 'Islas De Feroe'), 
        (162, 'Islas De Navidad'), 
        (334, 'Islas Heard Y Mcdonald'), 
        (238, 'Islas Malvinas'), 
        (580, 'Islas Marianas Del Norte'), 
        (584, 'Islas Marshall'), 
        (796, 'Islas Turcas Y Caicos'), 
        (581, 'Islas Ultramarinas De Usa'), 
        (92, 'Islas Virgenes Britanicas'), 
        (850, 'Islas Virgenes De Los Estados Unidos'), 
        (376, 'Israel'), 
        (380, 'Italia'), 
        (388, 'Jamaica'), 
        (392, 'Japon'), 
        (832, 'Jersey'), 
        (400, 'Jordania'), 
        (398, 'Kazajstan'), 
        (404, 'Kenia'), 
        (417, 'Kirguizistan'), 
        (296, 'Kiribati'), 
        (414, 'Kuwait'), 
        (418, 'Laos'), 
        (426, 'Lesoto'), 
        (428, 'Letonia'), 
        (422, 'Libano'), 
        (430, 'Liberia'), 
        (434, 'Libia'), 
        (438, 'Liechtenstein'), 
        (440, 'Lituania'), 
        (442, 'Luxemburgo'), 
        (446, 'Macao'), 
        (450, 'Madagascar'), 
        (458, 'Malasia Orientaloccidental'), 
        (454, 'Malawi'), 
        (462, 'Maldivas (islas)'), 
        (466, 'Mali'), 
        (470, 'Malta'), 
        (504, 'Marruecos'), 
        (474, 'Martinica'), 
        (478, 'Mauritania'), 
        (175, 'Mayote'), 
        (484, 'Mexico'), 
        (583, 'Micronesia (federacion Estados)'), 
        (498, 'Moldavia'), 
        (492, 'Monaco'), 
        (496, 'Mongolia'), 
        (500, 'Montserrat'), 
        (508, 'Mozambique'), 
        (104, 'Myanmar (birmania)'), 
        (516, 'Namibia'), 
        (520, 'Nauru'), 
        (524, 'Nepal'), 
        (558, 'Nicaragua'), 
        (562, 'Niger'), 
        (566, 'Nigeria'), 
        (570, 'Niue'), 
        (578, 'Noruega'), 
        (540, 'Nueva Caledonia'), 
        (554, 'Nueva Zelanda'), 
        (512, 'Oman'), 
        (528, 'Pais Bajos (holanda)'), 
        (586, 'Pakistan'), 
        (585, 'Palau'), 
        (591, 'Panama'), 
        (598, 'Papuanueva Guinea'), 
        (600, 'Paraguay'), 
        (604, 'Peru'), 
        (612, 'Pitcairn Islas'), 
        (258, 'Polinesia Francesa'), 
        (616, 'Polonia'), 
        (620, 'Portugal'), 
        (630, 'Puerto Rico'), 
        (634, 'Qatar'), 
        (826, 'Reino Unido'), 
        (891, 'Rep.fed.yugoeslavia Serbia Montenegro'), 
        (132, 'Republica Cabo Verde'), 
        (140, 'Republica Centro Africana'), 
        (203, 'Republica Checa'), 
        (214, 'Republica Dominicana'), 
        (703, 'Republica Eslovaca'), 
        (646, 'Ruanda'), 
        (642, 'Rumania'), 
        (643, 'Rusia'), 
        (90, 'Salomon Islas'), 
        (882, 'Samoa'), 
        (16, 'Samoa Americana'), 
        (659, 'San Cristobal Y Nevis'), 
        (674, 'San Marino'), 
        (666, 'San Pedro Y Miquelon'), 
        (670, 'San Vicente Y Las Granadinas'), 
        (654, 'Santa Helena Y Ascencion'), 
        (662, 'Santa Lucia'), 
        (678, 'Santo Tome Y Principe'), 
        (686, 'Senegal'), 
        (694, 'Sierra Leona'), 
        (702, 'Singapur'), 
        (760, 'Siria'), 
        (706, 'Somalia'), 
        (144, 'Sri Lanka (ceilan)'), 
        (748, 'Suazilandia'), 
        (710, 'Sudafrica'), 
        (736, 'Sudan'), 
        (752, 'Suecia'), 
        (756, 'Suiza'), 
        (740, 'Surinan'), 
        (764, 'Tailandia'), 
        (158, 'Taiwan'), 
        (834, 'Tanzania'), 
        (762, 'Tayikistan'), 
        (86, 'Territorio Britanico Oceano Indico'), 
        (768, 'Togo'), 
        (776, 'Tonga'), 
        (780, 'Trinidad Y Tobago'), 
        (788, 'Tunez'), 
        (795, 'Turkmenistan'), 
        (792, 'Turquia'), 
        (798, 'Tuvalu (islas Ellice)'), 
        (804, 'Ucrania'), 
        (800, 'Uganda'), 
        (858, 'Uruguay'), 
        (860, 'Uzbekistan'), 
        (548, 'Vanuatu'), 
        (862, 'Venezuela'), 
        (704, 'Vietnam'), 
        (887, 'Yemen'), 
        (262, 'Yibuti'), 
        (180, 'Zaire'), 
        (894, 'Zambia'), 
        (716, 'Zimbabue')";


        // -- Create table CFG_PROVINCIA

        $sqls[] = "DROP TABLE IF EXISTS `CFG_PROVINCIA`";

        $sqls[] = "CREATE TABLE `CFG_PROVINCIA` (
        `provincia_id` int(2) unsigned NOT NULL auto_increment,
        `id_pais` int(2) DEFAULT '724',
        `provincia_name` varchar(150),
        PRIMARY KEY (`provincia_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        // -- Rows for table CFG_PROVINCIA

        $sqls[] =  "INSERT INTO CFG_PROVINCIA (provincia_id,id_pais,provincia_name) VALUES
        (1, 724, 'Alava'), 
        (2, 724, 'Albacete'), 
        (3, 724, 'Alicante'), 
        (4, 724, 'Almería'), 
        (33, 724, 'Asturias'), 
        (5, 724, 'Avila'), 
        (6, 724, 'Badajoz'), 
        (7, 724, 'Baleares'), 
        (8, 724, 'Barcelona'), 
        (9, 724, 'Burgos'), 
        (10, 724, 'Cáceres'), 
        (11, 724, 'Cádiz'), 
        (39, 724, 'Cantabria'), 
        (12, 724, 'Castellón'), 
        (51, 724, 'Ceuta'), 
        (13, 724, 'Ciudad Real'), 
        (14, 724, 'Córdoba'), 
        (15, 724, 'Coruña, La'), 
        (16, 724, 'Cuenca'), 
        (17, 724, 'Gerona'), 
        (18, 724, 'Granada'), 
        (19, 724, 'Guadalajara'), 
        (20, 724, 'Guipuzcoa'), 
        (21, 724, 'Huelva'), 
        (22, 724, 'Huesca'), 
        (23, 724, 'Jaen'), 
        (24, 724, 'León'), 
        (25, 724, 'Lérida'), 
        (27, 724, 'Lugo'), 
        (28, 724, 'Madrid'), 
        (29, 724, 'Málaga'), 
        (52, 724, 'Melilla'), 
        (30, 724, 'Murcia'), 
        (31, 724, 'Navarra'), 
        (32, 724, 'Orense'), 
        (34, 724, 'Palencia'), 
        (35, 724, 'Palmas (Las)'), 
        (36, 724, 'Pontevedra'), 
        (26, 724, 'Rioja (La)'), 
        (37, 724, 'Salamanca'), 
        (38, 724, 'S.C.Tenerife'), 
        (40, 724, 'Segovia'), 
        (41, 724, 'Sevilla'), 
        (42, 724, 'Soria'), 
        (43, 724, 'Tarragona'), 
        (44, 724, 'Teruel'), 
        (45, 724, 'Toledo'), 
        (46, 724, 'Valencia'), 
        (47, 724, 'Valladolid'), 
        (48, 724, 'Vizcaya'), 
        (49, 724, 'Zamora'), 
        (50, 724, 'Zaragoza'), 
        (53, 840, 'California')";

        /*********/
        /*
        $n = 10;
        while ($n<65){
        $n++;
        $sqls[] = 'INSERT INTO ACL_USER_ROLES (id_user,id_role) VALUES ('.$n.',9)';
        }
        */
        foreach ($sqls as $sql){  
            runsql($sql);  
            //Vars::debug_var($sql);  
        }


    }else  if ($_ARGS[2]=='pages'){

        runsql("ALTER TABLE CLI_PAGES_FILES CHANGE file_id ID int(10) NOT NULL AUTO_INCREMENT");
        runsql("ALTER TABLE CLI_PAGES_FILES CHANGE file_name FILE_NAME varchar(200)");

    }else  if ($_ARGS[2]=='clean'){

        $sqls = ['ALTER TABLE CLI_ITEM DROP COLUMN item_comments_enabled'
                ,'ALTER TABLE CLI_ITEM DROP COLUMN item_rating_enabled'
                ,'ALTER TABLE CLI_ITEM DROP COLUMN item_votes'
                ,'ALTER TABLE CLI_ITEM DROP COLUMN item_points'
                ,'ALTER TABLE CLI_ITEM DROP COLUMN item_rating'
                ,'ALTER TABLE CLI_ITEM DROP COLUMN item_reads'
                ,'ALTER TABLE CLI_ITEM DROP COLUMN item_menuid'
                ,'ALTER TABLE CLI_ITEM DROP COLUMN inline_edit'
                ,'ALTER TABLE CLI_ITEM DROP COLUMN id_user'
                ,'ALTER TABLE CLI_USER DROP COLUMN user_deleted'
                ,'ALTER TABLE CLI_USER DROP COLUMN user_points'
                ];

        foreach ($sqls as $sql){  runsql($sql);    }


    }else{

            ?>
            <pre>
               Options:

                   /root        # create Root role
                   /pages       # rename & update some fields in pages module
                   /customers   # drop && create customers view or table
                   /countries   # drop && create countries & states tables 
                   /areas       # experimental option
                   /rmbak       # delete _bak_ dir
                   /refactor    # rename JUX to ExtFW

            </pre>
            <?php

        }

}else{
    ?><p>Access Denied</p><?php
}

?></pre><p><a class="btn" href="control_panel">Volver</a></p>