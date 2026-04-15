<div class="inner">
<?php

    if ($_ARGS[1]=='fresh'){

        include('create_tables_acl.php');
        include('create_tables_system.php');
        include('create_tables_i18n.php');
        $admin_user = 'admin';
        $admin_password =  Str::password(10, 0);

        //$salt = substr(md5(uniqid(rand(), true)), 0, 3);
        //$hash = hash('sha256', $salt . hash('sha256', $admin_password ) );
        $hash =  password_hash($admin_password, PASSWORD_BCRYPT) ; 

        $sqls[]  ="INSERT INTO ".TB_USER." (username,user_fullname,user_password,user_level,user_email,user_online,user_active,id_lang,user_verify) 
        VALUES ('$admin_user','Administrador','$hash',2000 ,'admin@{$_SERVER['HTTP_HOST']}','1','1',1,1)";

        
    }else if ($_ARGS[1]=='restore'){


    }else if ($_ARGS[1]=='wines'){

        include('create_tables_shop_wines.php');
        $sqls[] = "UPDATE `CFG_CFG` SET `V` = 'true' WHERE `CFG_CFG`.`K` = 'shop.wines'";

    }

    ?>
    <pre id="div_install" style="background-color:white;display:block;margin:15px auto;width:100%;height:350px;overflow:auto;border:1px solid #DFDFC4;font-family:Monaco;font-size:9px;">
        <?php         
        
            //include(SCRIPT_DIR_CLASSES.'/install.class.php');
            foreach ($sqls as $sql){  Install::runsql($sql);    }

        ?>
    </pre>

    <?php if ($_ARGS[1]=='fresh'){
        // unlink(SCRIPT_DIR_MODULE.'/create_tables_acl.php');
        // unlink(SCRIPT_DIR_MODULE.'/create_tables_system.php');
        // rename(SCRIPT_DIR_MODULE.'/create_tables_acl.php',SCRIPT_DIR_MODULE.'/create_tables_acl.0.php');
        // rename(SCRIPT_DIR_MODULE.'/create_tables_system.php',SCRIPT_DIR_MODULE.'/create_tables_system.0.php');
        ?>  
        <div style="text-align:center;margin:25px auto;">
            <p style="font-size:1.3em;text-align:left;" class="info">La instalación se ha completado. <?php if ($_ARGS[1]=='fresh'){?>El usuario principal es <input type="text" style="width:100px;text-align:center;"  value="<?=$admin_user?>"> y la contraseña <input type="text" style="width:100px;text-align:center;"  value="<?=$admin_password?>">. Puede inciar sesión y entrar en el Panel de control para añadir y modificar contenidos.<?php }?></p>
            <p style="text-align:center;margin:20px auto;"><a class="btn btn-success" href="login">Iniciar sesión</a> <a class="btn btn-success" href="control_panel">Panel de control</a><br /></p>
        </div>
    <?php }?>

    <script type="text/javascript">
        document.getElementById('div_install').scrollTop = 60000;
    </script>

    <?php
        
    Messages::$messages = false;
    
?>
</div>
