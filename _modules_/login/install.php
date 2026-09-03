<h1>Instalación</h1>
<?php
/********
   //$login = new Login();
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_id INT(7) NOT NULL auto_increment UNIQUE FIRST");
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_salt varchar(3)");
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_email varchar(100)");
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_verify int(1)");
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_last_login INT(16) not null");
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_ip varchar(15)");
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_active ENUM('0','1') NOT NULL");
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_online int(1) unsigned not null default '0'");
   Login::sqlQuery("ALTER TABLE usuarios ADD COLUMN user_confirm_code varchar(100)");
   
   Login::sqlQuery("ALTER TABLE usuarios CHANGE CLAVE CLAVE varchar(64) NOT NULL"); 

   //echo 'LASTINSERTID: '.Login::lastInsertId('usuarios');
  
   $u = Login::sqlQuery('SELECT * FROM usuarios');

   **/
   ?><pre><?php 
   //print_r($u);
   ?></pre><?php 