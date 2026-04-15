<div class="inner">

    <h1 style="display:none;"><?=ucwords(MODULE)?></h1>

    <?php   

        if($_ARGS[1]=='test'){

             include(SCRIPT_DIR_MODULE.'/test_acl.php');
        
        } else if($_ARGS[1]=='doc'){

             include(SCRIPT_DIR_MODULE.'/doc.php');
        
        } else if($_ARGS[1]=='cheatsheet'){

             include(SCRIPT_DIR_MODULE.'/html.php');
        
        }else{

            // Initialize Scaffold engine
            Table::init();

            if(Administrador()){?>

                <div id="tabs-AREAS" data-simpletabs>
                    <ul>
                        <li><a href="#tab-AREAS-apps">Aplicaciones y permisos</a></li>
                        <li><a href="#tab-AREAS-areas">Áreas</a></li>
                    </ul>
                
                    <div id="tab-AREAS-apps" style="border-top:1px solid #aaa;">
                        <div class="info" style="margin:0px 0px 5px 0px;"><p><b>Aplicaciones y permisos.</b><br />En esta pantalla se definen los permisos disponibles para cada aplicación.</p></div>  
                        <?php                 
                            Table::show_table('CFG_APPS'); 
                            Table::show_table('CFG_APPS_PERMS'); 
                        ?> 
                    </div>
        
            <?php }?>

                    <div id="tab-AREAS-areas"><div class="info" style="margin:0px 0px 10px 0px;"><p><b>Áreas, usuarios y grupos.</b><br />Para cada área pueden definirse varios grupos y/o usuarios. El administrador de cada área podrá agrupar los usuarios en grupos. Para cada área se podrán definir aplicaciones con permisos particularizados.</div><!--
                    --><div id="div-AREAS_USR_APP_GRP"><!--
                        
                        --><div id="div-AREAS">
                            <?php  Table::show_table('CFG_AREAS'); ?>
                        </div><!--
                        --><div id="tabs-AREA_USR_APP_GRP" style="overflow:hidden;" data-simpletabs>
                            <ul>
                            <li><a id="a_tab_AREA_USRS" href="#tab-AREA_USRS">Usuarios<i></i></a></li>
                            <li><a id="a_tab_AREA_GRPS" href="#tab-AREA_GRPS">Grupos<i></i></a></li>
                            <li><a id="a_tab_AREA_APPS" href="#tab-AREA_APPS">Aplicaciones<i></i></a></li>
                            </ul><!--
                        --><div id="tab-AREA_USRS"><?php  Table::show_table('CFG_AREAS_USERS');   ?></div><!--
                        --><div id="tab-AREA_GRPS"><?php  Table::show_table('CFG_AREAS_GROUPS');Table::show_table('CFG_AREAS_GROUPS_USERS'); ?></div><!--
                        --><div id="tab-AREA_APPS" style="overflow:hidden;">
                            <?php  
                                Table::show_table('CFG_AREAS_APPS');  
                                Table::show_table('CFG_AREAS_APPS_GROUPS'); 
                                Table::show_table('CFG_AREAS_APPS_GROUPS_PERMS');
                            ?><!--
                        --></div><!--
                        --></div><!--
                    --></div><!--
                --></div>

            <?php if(Administrador()){?>
                </div>
            <?php }
        }



        //FIX: En CFG_AREAS_APPS_GROUPS_PERMS al editar una fila el select con los permisos
        // no contiene los correspondientes a la app seleccionada en CFG_AREAS_APPS > CFG_AREAS_APPS_GROUPS
    ?>
</div>
