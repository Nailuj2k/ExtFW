<?php
   die('norl!!');

      <h3>Instrucción SQL para crear la base de datos</h3>
      <pre style="font-size:12px;line-height:14px;background:#004080;color:#FFFF99;padding:20px 5px 10px 5px;border:2px solid #6B99B4;margin:10px 5px 20px 5px;">
      CREATE DATABASE `<?=$dbname?>` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
      CREATE USER <?=$dbuser?>@'%' IDENTIFIED BY '<?=$pass1?>';
      GRANT USAGE ON *.* TO <?=$dbuser?>@'%' IDENTIFIED BY '<?=$pass1?>' 
        WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 
             MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
      GRANT ALL PRIVILEGES ON <?=$dbname?>.* TO <?=$dbuser?>@'%' WITH GRANT OPTION ;
      </pre>
      <?php 
      $db_created_ok = true;
