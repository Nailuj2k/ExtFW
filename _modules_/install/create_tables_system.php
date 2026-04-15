<?php

$sqls[] = "DROP TABLE IF EXISTS ".TB_USER;

$sqls[] = "CREATE TABLE `".TB_USER."` (
   `user_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
   `id_lang` INT(5) UNSIGNED NOT NULL DEFAULT '1',
   `username` VARCHAR(50),
   `user_password` VARCHAR(64),
   `user_level` INT(8) UNSIGNED NOT NULL DEFAULT '100',
   `user_date_created` INT(8),
   `user_last_login` INT(16),
   `user_email` VARCHAR(150),
   `user_url` VARCHAR(50),
   `user_fullname` VARCHAR(150),
   `user_ip` VARCHAR(15),
   `user_salt` VARCHAR(3),
   `user_active` INT(1),
   `user_verify` INT(1),
   `user_online` INT(1),
   `user_score` INT(10),
   `user_url_avatar` VARCHAR(200),
   `user_signature` VARCHAR(45),
   `user_notes` TEXT,
   `user_confirm_code` VARCHAR(100),
   `user_notify` INT(1) DEFAULT '1',
   `api_key` VARCHAR(50),
   `id_pais` INT(5) DEFAULT '724',
   `id_provincia` INT(5),
   `id_municipio` INT(5),
   `id_localidad` INT(5),
   `user_card_id` VARCHAR(20),
   `user_lpd_data` INT(1),
   `user_lpd_publi` INT(1),
   `LAT` VARCHAR(15),
   `LON` VARCHAR(15),
   `WEB` VARCHAR(100),
   AUTH_ID VARCHAR(50),
   AUTH_PROVIDER VARCHAR(15),
   AUTH_PICTURE VARCHAR(150),
   CREATED_BY INT(5),
   CREATION_DATE DATETIME,
   LAST_UPDATED_BY INT(5),
   LAST_UPDATE_DATE DATETIME,
   PRIMARY KEY (`user_id`),
   UNIQUE KEY (`username`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
   
   // `user_deleted` INT(1) UNSIGNED NOT NULL DEFAULT '0',
   // `user_points` decimal(5,2) DEFAULT '0.00',
   
   $sqls[] = "DROP TABLE IF EXISTS ".TB_ITEM;
   
   $sqls[] = "CREATE TABLE ".TB_ITEM." (
   `item_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
   `id_menu` INT(5),
   `item_parent` INT(7),
   `item_level` INT(8) DEFAULT '100',
   `item_order` INT(5),
   `item_name` VARCHAR(150),
   `item_title` VARCHAR(150),
   `item_caption` VARCHAR(50),
   `item_active` INT(1) DEFAULT '1',
   `item_visible` INT(1) DEFAULT '1',
   `item_menuid` INT(5),
   `item_url` VARCHAR(150),
   `FILE_NAME` VARCHAR(200),
   `item_public` INT(1),
   PRIMARY KEY (`item_id`), 
   UNIQUE KEY (`item_name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Rows for table CLI_ITEM

$sqls[] =  "INSERT INTO ".TB_ITEM." (id_menu,item_parent,item_level,item_order,item_name,item_title,item_caption,item_active,item_visible,item_menuid,item_url,item_public) VALUES
   (  1, 0, 100,  5, 'news',                   'News',                   'NEWS'          ,  1, 0,  1, 'news'                       , 1), 
   (  1, 0, 100,  5, 'docs',                   'Docs',                   'DOCS'          ,  1, 0,  1, 'docs'                       , 1), 
   (  1, 0, 100,  5, 'contact',                'Formulario de contacto', 'CONTACT'       ,  1, 1,  1, 'contact'                    , 1), 
   (  3, 0, 100, 12, 'aviso-legal',            'Aviso legal'           , 'LEGAL_WARNING' ,  1, 1,  1, 'page/aviso-legal'           , 0), 
   (  2, 0, 100, 11, 'politica-de-privacidad', 'Política de privacidad', 'PRIVACY_POLICY',  1, 1,  1, 'page/politica-de-privacidad', 0)";
 //(  1, 0, 100, 20, 'shop',                   'Tienda',                 'SHOP'          ,  0, 0,  1, 'shop'                       , 1),

// -- Create table CFG_LANG

$sqls[] = "DROP TABLE IF EXISTS `CFG_LANG`";

$sqls[] = "CREATE TABLE `CFG_LANG` (
   `lang_id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
   `lang_name` VARCHAR(15),
   `lang_cc` VARCHAR(5),
   `lang_urlflag` VARCHAR(50),
   `lang_language_string` VARCHAR(50),
   `lang_active` INT(1),
   `ACTIVE` INT(1) DEFAULT '1',
   PRIMARY KEY (`lang_id`), 
   UNIQUE KEY (`lang_cc`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Rows for table CFG_LANG

$sqls[] =  "INSERT INTO CFG_LANG (lang_name,lang_cc,lang_urlflag,lang_language_string,lang_active,ACTIVE) VALUES
   ('Castellano', 'es', '_images_/flags/24/espana.png', NULL, 1, 1), 
   ('Frances', 'fr', '_images_/flags/24/francia.png', NULL, 0, 0), 
   ('Alemán', 'de', '_images_/flags/24/alemania.png', NULL, 0, 0), 
   ('Italiano', 'it', '_images_/flags/24/italia.png', NULL, 0, 0), 
   ('Inglés', 'en', '_images_/flags/24/inglaterra.png', NULL, 1, 1)";

/**** 
$sqls[] = "DROP TABLE IF EXISTS `CFG_STR`";

$sqls[] = "CREATE TABLE `CFG_STR` (
   `str_id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
   `str_string` VARCHAR(200),
   `str_ok` INT(1),
   PRIMARY KEY (`str_id`), 
   UNIQUE KEY (`str_string`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Rows for table CFG_STR

//FIX remove CONTACT_FORM_HELP (107)

$sqls[] =  "INSERT INTO CFG_STR (str_id,str_string,str_ok) VALUES
    (1, 'YES', NULL), 
    (2, 'NO', NULL), 
    (3, 'ACCEPT', NULL), 
    (4, 'IGNORE', NULL), 
    (5, 'SEND', NULL), 
    (6, 'LOGIN', NULL), 
    (7, 'LOGOUT', NULL), 
    (8, 'EXIT_SESSION', NULL), 
    (9, 'LOGIN_OR_REGISTER', NULL), 
    (10, 'NEW_CUSTOMERS', NULL), 
    (11, 'NEW_CUSTOMERS_HELP', NULL), 
    (12, 'REGISTERED_CUSTOMERS', NULL), 
    (13, 'LOGIN_HELP', NULL), 
    (14, 'LOGIN_HELP_LDAP', NULL), 
    (15, 'LOGGED_IN_AS', NULL), 
    (16, 'EMAIL', NULL), 
    (17, 'CARD_ID', NULL), 
    (18, 'ACCEPT_PRIVACY_POLICES', NULL), 
    (19, 'EMAIL_OR_USERNAME', NULL), 
    (20, 'FULL_NAME', NULL), 
    (21, 'USERNAME', NULL), 
    (22, 'PASSWORD', NULL), 
    (23, 'SIGN_IN', NULL), 
    (24, 'REMEMBER_ME', NULL), 
    (25, 'REGISTER', NULL), 
    (26, 'LOST_PASSWORD', NULL), 
    (27, 'CHANGE_PASSWORD', NULL), 
    (28, 'OLD_PASSWORD', NULL), 
    (29, 'NEW_PASSWORD', NULL), 
    (30, 'CONFIRM_PASSWORD', NULL), 
    (31, 'EMPTY_FOR_CHANGE_YOUR_OWN_PASSWORD', NULL), 
    (32, 'REGISTER_WELCOME_MAIL_SUBJECT', NULL), 
    (33, 'REGISTER_ACTIVATION_SUBJECT', NULL), 
    (34, 'REGISTER_ACTIVATION_MSG', NULL), 
    (35, 'NOT_VERIFIED', NULL), 
    (36, 'VERIFIED_OK_MSG', NULL), 
    (37, 'INVALID_CODE', NULL), 
    (38, 'REGISTER_OK_MSG', NULL), 
    (39, 'REGISTER_KO_MSG', NULL), 
    (40, 'PROFILE', NULL), 
    (41, 'GROUPS', NULL), 
    (42, 'MEMBER_OF_%s_GROUPS', NULL), 
    (43, 'NOTES', NULL), 
    (44, 'BYE_%s', 0), 
    (45, 'HELLO_%s', NULL), 
    (46, 'EDIT_USER', NULL), 
    (47, 'MY_ORDERS', NULL), 
    (48, 'MY_ACCOUNT', NULL), 
    (49, 'USER_AREA', NULL), 
    (50, 'BY_DEFAULT', NULL), 
    (51, 'CARD_ID_ALREADY_EXISTS', NULL), 
    (52, 'CARD_ID_INVALID', NULL), 
    (53, 'USERNAME_OR_MAIL_ALREADY_EXISTS', NULL), 
    (54, 'EMAIL_INVALID', 0), 
    (55, 'NEUTRAL', NULL), 
    (56, 'VERY_WEAK', NULL), 
    (57, 'WEAK', NULL), 
    (58, 'MEDIUM', NULL), 
    (59, 'STRONG', NULL), 
    (60, 'VERY_STRONG', NULL), 
    (61, 'GENERATE_RANDOM_PASSWORD', NULL), 
    (62, 'USERNAME_OR_PASSWORD_INCORRECT', NULL), 
    (63, 'USERNAME_OR_PASSWORD_MISSING', NULL), 
    (64, 'INCORRECT_PASSWORD', NULL), 
    (65, 'LOGIN_ERROR', NULL), 
    (66, 'NOT_IMPLEMENTED', NULL), 
    (67, 'REGISTER_DISABLED', NULL), 
    (68, 'LOST_PASSWORD_DISABLED', NULL), 
    (69, 'MISSING_DATA', NULL), 
    (70, 'PASSWORDS_NOT_MATCH', NULL), 
    (71, 'USERNAME_INVALID', NULL), 
    (72, 'WELCOME_%s', NULL), 
    (73, 'ERROR_CHAGING_PASSWORD', NULL), 
    (74, 'PASSWORD_CHANGED', NULL), 
    (75, 'USER_AUTH_ERROR', NULL), 
    (76, 'FILE_%s_NOT_EXISTS_OR_ERROR_IN_MODULE_%s', NULL), 
    (77, 'HOME', NULL), 
    (79, 'TYPES', NULL), 
    (80, 'CONTACT', NULL), 
    (90, 'EDIT_CATEGORIES', NULL), 
    (91, 'BACK', NULL), 
    (92, 'ADD', NULL), 
    (93, 'PAGE_%s_OF_%s', 0), 
    (94, 'ROWS_%s_FROM_%s_TO_%s', NULL), 
    (96, 'ITEM', NULL), 
    (97, 'DELETE', NULL), 
    (103, 'CREDIT_CARD', NULL), 
    (107, 'CONTACT_FORM_HELP', NULL),  
    (108, 'NAME', NULL), 
    (109, 'YOUR_NAME', NULL), 
    (110, 'YOUR_EMAIL', NULL), 
    (111, 'MESSAGE', NULL), 
    (112, 'SUBMIT', NULL), 
    (113, 'CONTACT_FORM', NULL), 
    (123, 'ACCESS_DENIED', NULL), 
    (130, 'SEARCH', NULL), 
    (144, 'CANCEL', NULL), 
    (146, 'COPY_TO_CLIPBOARD', NULL), 
    (147, 'YOU_MUST_ACCEPT_CONDITIONS_BEFORE_CONTINUE', NULL), 
    (148, 'I_AGREE', 0), 
    (149, 'PHONE', NULL), 
    (150, 'ADDRESS', NULL), 
    (151, 'ADDRESS_PLACEHOLDER', NULL), 
    (152, 'COUNTRY', NULL), 
    (153, 'STATE', NULL), 
    (154, 'STATE_PLACEHOLDER', NULL), 
    (155, 'CITY', NULL), 
    (156, 'CITY_PLACEHOLDER', NULL), 
    (157, 'COUNTY', NULL), 
    (158, 'COUNTY_PLACEHOLDER', NULL), 
    (159, 'ZIP', NULL), 
    (160, 'CREATE_ACCOUNT_AND_STORE_DATA', NULL), 
    (161, 'MUST_ACCEPT_CONDITIONS', NULL), 
    (165, 'VIEW', NULL), 
    (166, 'PAGE_SAVED_SUCCESSFULLY', NULL), 
    (167, 'I_ACCEPT_THECNICAL_COOKIES', NULL), 
    (168, 'I_ACCEPT_TERMS_AND_USE_CONDITIONS', NULL), 
    (169, 'I_ACCEPT_PRIVACY_POLICIES', NULL), 
    (173, 'REGISTER_WITH_US', NULL), 
    (176, 'CONDITIONS', NULL), 
    (180, 'PRIVACY_POLICY', NULL), 
    (181, 'LEGAL_WARNING', NULL), 
    (182, 'YOUR_DATA_SAFE', NULL), 
    (183, 'DATA_PROTECTION', NULL), 
    (184, 'ENERGY_AUDIT', NULL), 
    (188, 'ALL_RIGHTS_RESERVED', NULL), 
    (192, 'NAME_INVALID', NULL), 
    (194, 'PHONE_INVALID', NULL), 
    (195, 'ZIP_INVALID', NULL), 
    (203, 'PAGE', 0), 
    (204, 'OF', 0), 
    (209, 'SEARCH_TERM', NULL), 
    (213, 'MESSAGES', NULL), 
    (214, 'I_ACCEPT_ANALYSIS_COOKIES', 0), 
    (215, 'I_ACCEPT_FUNCTIONAL_COOKIES', NULL), 
    (216, 'I_ACCEPT_PUBLICITY_COOKIES', NULL), 
    (217, 'NOTES_FOR_DELIVERY', NULL), 
    (219, 'SELECT_PLEASE', NULL), 
    (221, 'NEW_PASSWORD_SENT', 0), 
    (222, 'REMINDER_PASSWORD_REQUEST_SENT', 1), 
    (223, 'SUCCESFULLY', NULL), 
    (224, 'RESET_PASSWORD_REQUEST_FAILED', 0), 
    (225, 'PASSWORD_REMINDER_SUBJECT', 1), 
    (226, 'PASSWORD_REMINDER_MSG', 1), 
    (227, 'ADDRESS_INVALID', NULL), 
    (228, 'NEW_PASSWORD_MAIL_SUBJECT', NULL),
    (229, 'LOGIN_WITH_GOOGLE', NULL), 
    (230, 'REGISTER_WITH_GOOGLE', NULL),
    (231, 'COMMENT', NULL)";



// -- Create table CFG_CC

$sqls[] = "DROP TABLE IF EXISTS `CFG_CC`";

$sqls[] = "CREATE TABLE `CFG_CC` (
    `cc_id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_str` INT(5) NOT NULL,
    `id_lang` INT(5) NOT NULL,
    `cc_string` TEXT,
    `cc_ok` INT(1),
     PRIMARY KEY (`cc_id`), 
     UNIQUE KEY (id_str,id_lang)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";


// -- Rows for table CFG_CC

$sqls[] =  "INSERT INTO CFG_CC (cc_id,id_str,id_lang,cc_string) VALUES
    (1, 1, 5, 'Yes'), 
    (2, 2, 5, 'No'), 
    (3, 3, 5, 'Accept'), 
    (4, 4, 5, 'Ignore'), 
    (5, 5, 5, 'Send'), 
    (6, 6, 5, 'Login'), 
    (7, 7, 5, 'Logout'), 
    (8, 8, 5, 'Close session'), 
    (9, 9, 5, 'Log in or create an account'), 
    (10, 10, 5, 'New Users'), 
    (11, 11, 5, 'To register you will need to provide your name, an email account and a password.'), 
    (12, 12, 5, 'Registered Users'), 
    (13, 13, 5, 'Log in with your username and password. Otherwise you can register with our <a class=\"btn-register-form\"  href=\"login/register/en\">register form</a>.'), 
    (14, 14, 5, 'Log in with your LDAP username and password. Otherwise you can contact your support service'), 
    (15, 15, 5, 'Logged in as'), 
    (16, 16, 5, 'Email'), 
    (17, 17, 5, 'Card id'), 
    (18, 18, 5, 'I have read and accept <br />the <a href=\"page/politica-de-privacidad\">Privacy Policies</a><br /><br />'), 
    (19, 19, 5, 'Email or Username'), 
    (20, 20, 5, 'Full name'), 
    (21, 21, 5, 'Username'), 
    (22, 22, 5, 'Password'), 
    (23, 23, 5, 'Sign in'), 
    (24, 24, 5, 'Remember me'), 
    (25, 25, 5, 'Create an account'), 
    (26, 26, 5, 'Lost password'), 
    (27, 27, 5, 'Change password'), 
    (28, 28, 5, 'Old password'), 
    (29, 29, 5, 'New password'), 
    (30, 30, 5, 'Confirm password'), 
    (31, 31, 5, 'Empty for change your own password'), 
    (32, 32, 5, 'User register'), 
    (33, 33, 5, 'User register'), 
    (34, 34, 5, 'Thank you for registering with us. You can activate your account by clicking on this link'), 
    (35, 35, 5, 'Not verified. Check your email'), 
    (36, 36, 5, 'Your account has been verified and we have sent you a welcome email with your login details. <br /> You can now log in with your email and password.'), 
    (37, 37, 5, 'This code is no longer valid.'), 
    (38, 38, 5, 'An e-mail has been sent to you, Please follow the embedded link to activate your account.\nHaven`t received the email? Check your spam folder. If the problem persists contact us.'), 
    (39, 39, 5, 'It was not possible to complete the registration. <br /> Contact us through our <a href=\"contact\"> Contact form </a>.'), 
    (40, 40, 5, 'Profile'), 
    (41, 41, 5, 'Groups'), 
    (42, 42, 5, 'Member of %s groups'), 
    (43, 43, 5, 'Notes'), 
    (44, 44, 5, 'Bye, %s'), 
    (45, 45, 5, 'Hello %s'), 
    (46, 46, 5, 'Edit my profile'), 
    (47, 47, 5, 'My orders'), 
    (48, 48, 5, 'My account'), 
    (49, 49, 5, 'User area'), 
    (50, 50, 5, 'By default'), 
    (51, 51, 5, 'Card id already exists'), 
    (52, 52, 5, 'Card id not valid'), 
    (53, 53, 5, 'Username or email already exists'), 
    (54, 54, 5, 'Email not valid'), 
    (55, 55, 5, 'Min. 8 chars'), 
    (56, 56, 5, 'Very weak'), 
    (57, 57, 5, 'Weak'), 
    (58, 58, 5, 'Medium'), 
    (59, 59, 5, 'Strong'), 
    (60, 60, 5, 'Very strong'), 
    (61, 61, 5, 'Generate random password'), 
    (62, 62, 5, 'Username or password incorrect'), 
    (63, 63, 5, 'Username or password missing'), 
    (64, 64, 5, 'Password incorrect'), 
    (65, 65, 5, 'Login error'), 
    (66, 66, 5, 'Not implemented'), 
    (67, 67, 5, 'Aut reg disabled. Call yout support service'), 
    (68, 68, 5, 'Password reset disabled. Call your support service'), 
    (69, 69, 5, 'Missing data'), 
    (70, 70, 5, 'Passwords does not match'), 
    (71, 71, 5, 'Username is not valid'), 
    (72, 72, 5, 'Welcome, %s'), 
    (73, 73, 5, 'Error changing password'), 
    (74, 74, 5, 'Password changed'), 
    (75, 75, 5, 'User auth error'), 
    (76, 76, 5, 'No existe el archivo %s/init.php o el módulo %s no está bien configurado.'), 
    (77, 77, 5, 'Home'), 
    (79, 79, 5, 'Types'), 
    (80, 80, 5, 'Contact'), 
    (90, 1, 1, 'Si'), 
    (91, 2, 1, 'No'), 
    (92, 3, 1, 'Aceptar'), 
    (93, 4, 1, 'Ignorar'), 
    (94, 5, 1, 'Enviar'), 
    (95, 6, 1, 'Inicio de sesión'), 
    (96, 7, 1, 'Salir'), 
    (97, 8, 1, 'Cerrar sesión'), 
    (98, 9, 1, 'Identificarse o crear una cuenta'), 
    (99, 10, 1, 'Nuevos usuarios'), 
    (100, 11, 1, 'Para registrarse necesitará proporcionar su nombre, una cuenta de correo electrónico y una contraseña.'), 
    (101, 12, 1, 'Usuarios registrados'), 
    (102, 13, 1, 'Inicie sesión con su nombre de usuario y contraseña. O regístrese usando nuestro <a class=\"btn-register-form\" href=\"login/register\">formulario de registro</a>.'), 
    (103, 14, 1, 'Inicie sesión con su nombre de usuario y contraseña de LDAP. Si no dispone de credenciales puede ponerse en contacto con el Servicio de Informática'), 
    (104, 15, 1, 'Conectado como'), 
    (105, 16, 1, 'Correo electrónico'), 
    (106, 17, 1, 'DNI / NIE / NIF'), 
    (107, 18, 1, 'He leído y acepto <br />las <a href=\"page/datos-seguros\">Políticas de Privacidad</a><br /><br />'), 
    (108, 19, 1, 'Email o Usuario'), 
    (109, 20, 1, 'Nombre y apellidos'), 
    (110, 21, 1, 'Usuario'), 
    (111, 22, 1, 'Contraseña'), 
    (112, 23, 1, 'Acceder'), 
    (113, 24, 1, 'Recordarme'), 
    (114, 25, 1, 'Crear cuenta'), 
    (115, 26, 1, 'Recordar contraseña'), 
    (116, 27, 1, 'Cambiar contraseña'), 
    (117, 28, 1, 'Contraseña actual'), 
    (118, 29, 1, 'Contraseña nueva'), 
    (119, 30, 1, 'Confirmar contraseña'), 
    (120, 31, 1, 'En blanco para cambiar su propia contraseña'), 
    (121, 32, 1, 'Registro de usuario'), 
    (122, 33, 1, 'Registro de usuario'), 
    (123, 34, 1, 'Gracias por registrarse con nosotros. Puede activar su cuenta pulsando en este enlace'), 
    (124, 35, 1, 'Cuenta pendiente de verificar. Consulte su email'), 
    (125, 36, 1, 'Su cuenta ha sido verificada y le hemos enviado un correo electrónico de bienvenida con sus datos de acceso.<br />Ya puede iniciar sesión con su e-mail y su contraseña.'), 
    (126, 37, 1, 'Este código ya no es válido'), 
    (127, 38, 1, 'Gracias por registrarse con nosotros, le hemos enviado un email de confirmación. Si no lo encuentra mire en su carpeta Spam. Si el problema persiste contacte con nosotros.'), 
    (128, 39, 1, 'No ha sido posible completar el registro.<br />Contáctenos mediante nuestro <a href=\"contact\">Formulario de contacto</a>.'), 
    (129, 40, 1, 'Perfil'), 
    (130, 41, 1, 'Grupos'), 
    (131, 42, 1, 'Miembro de %s grupos'), 
    (132, 43, 1, 'Observaciones'), 
    (133, 44, 1, 'Hasta luego, %s'), 
    (134, 45, 1, 'Hola %s'), 
    (135, 46, 1, 'Modificar mis datos'), 
    (136, 47, 1, 'Mis pedidos'), 
    (137, 48, 1, 'Mi cuenta'), 
    (138, 49, 1, 'Mi cuenta'), 
    (139, 50, 1, 'Por defecto'), 
    (140, 51, 1, 'Ya existe alguien con ese DNI o NIF'), 
    (141, 52, 1, 'DNI ó NIF no válido'), 
    (142, 53, 1, 'Ya existe alguien con ese nombre de usuario o email'), 
    (143, 54, 1, 'Email no válido'), 
    (144, 55, 1, 'Mín. 8 carácteres'), 
    (145, 56, 1, 'Muy débil'), 
    (146, 57, 1, 'Débil'), 
    (147, 58, 1, 'Normal'), 
    (148, 59, 1, 'Fuerte'), 
    (149, 60, 1, 'Muy fuerte'), 
    (150, 61, 1, 'Obtener contraseña aleatoria'), 
    (151, 62, 1, 'Nombre de usuario o contraseña incorrectos'), 
    (152, 63, 1, 'Falta el nombre de usuario o la contraseña'), 
    (153, 64, 1, 'Contraseña incorrecta'), 
    (154, 65, 1, 'Error de autentificación'), 
    (155, 66, 1, 'No implementado'), 
    (156, 67, 1, 'El registro automático está deshabilitado temporalmente. Contacte con su servicio de informática'), 
    (157, 68, 1, 'Para recuperar su contraseña póngase en contacton su servicio de informática'), 
    (158, 69, 1, 'Faltan datos'), 
    (159, 70, 1, 'Las contraseñas no coinciden'), 
    (160, 71, 1, 'Nombre de usuario no válido'), 
    (161, 72, 1, 'Bienvenido, %s'), 
    (162, 73, 1, 'Error al cambiar la contraseña'), 
    (163, 74, 1, 'Contraseña cambiada'), 
    (164, 75, 1, 'Error al autentificar el usuario'), 
    (165, 76, 1, 'No existe el archivo %s/init.php o el módulo %s no está bien configurado.'), 
    (166, 77, 1, 'Inicio'), 
    (168, 79, 1, 'Tipos'), 
    (169, 80, 1, 'Contacto'), 
    (183, 97, 1, 'Borrar'), 
    (194, 97, 5, 'Delete'), 
    (195, 96, 1, 'Artículo'), 
    (196, 96, 5, 'Item'), 
    (197, 94, 1, 'Filas %s a %s de %s'), 
    (198, 94, 5, 'Rows %s to %s of %s'), 
    (199, 93, 1, 'Página %s de %s'), 
    (200, 93, 5, 'Page %s de %s'), 
    (201, 92, 1, 'Añadir'), 
    (202, 92, 5, 'Add'), 
    (203, 91, 1, 'Volver'), 
    (204, 91, 5, 'Back'), 
    (205, 90, 1, 'Editar categorías'), 
    (206, 90, 5, 'Edit categories'), 
    (213, 107, 1, 'Si tienes alguna duda puedes ponerte en contacto con nosotros rellenando este formulario. Estaremos encantados de atenderte')";

//FIX remove CONTACT_FORM_HELP (107)

$sqls[] =  "INSERT INTO CFG_CC (cc_id,id_str,id_lang,cc_string) VALUES 
    (214, 107, 5, 'If you have any questions, you can contact us by filling out this form. We will be happy to assist you.'), 
    (215, 108, 1, 'Nombre'), 
    (216, 108, 5, 'Name'), 
    (217, 109, 1, 'Su nombre'), 
    (218, 109, 5, 'Your name'), 
    (219, 110, 1, 'Correo electrónico'), 
    (220, 110, 5, 'Email'), 
    (221, 111, 1, 'Mensaje'), 
    (222, 111, 5, 'Message'), 
    (223, 112, 1, 'Enviar'), 
    (224, 112, 5, 'Submit'), 
    (225, 113, 1, 'Formulario de contacto'), 
    (226, 113, 5, 'Contact form'), 
    (235, 123, 1, 'Acceso denegado'), 
    (236, 123, 5, 'Access denied'), 
    (247, 130, 1, 'Buscar'), 
    (248, 130, 5, 'Search'), 
    (285, 146, 1, 'Copiar al portapapeles'), 
    (286, 146, 5, 'Copy to clipboard'), 
    (289, 148, 1, 'Acepto'), 
    (290, 148, 5, 'I agree'), 
    (293, 144, 1, 'Cancelar'), 
    (294, 144, 5, 'Cancel'), 
    (295, 151, 1, 'Calle, nº, etc'), 
    (296, 151, 5, 'Street, number, door, etc.'), 
    (297, 154, 1, 'Provincia'), 
    (298, 154, 5, 'Region (State / Province)'), 
    (299, 156, 1, 'Municipio'), 
    (300, 156, 5, 'Locality (City / Municipality)'), 
    (301, 158, 1, 'Localidad'), 
    (302, 158, 5, 'Sub-Locality (County / other sub-division of a locality)'), 
    (303, 159, 1, 'CP'), 
    (304, 159, 5, 'Zip'), 
    (305, 160, 1, 'Crear una cuenta de usuario y guardar mis datos. Le enviaremos un email para activar su cuenta y completar su registro.'), 
    (306, 160, 5, 'Create account and store data. We will send you an email with your access data.'), 
    (307, 149, 1, 'Teléfono'), 
    (308, 149, 5, 'Phone'), 
    (309, 150, 1, 'Dirección'), 
    (310, 150, 5, 'Address'), 
    (311, 152, 1, 'País'), 
    (312, 152, 5, 'Country'), 
    (313, 153, 1, 'Provincia'), 
    (314, 153, 5, 'State'), 
    (315, 155, 1, 'Municipio'), 
    (316, 155, 5, 'City'), 
    (317, 157, 1, 'Localidad'), 
    (318, 157, 5, 'County'), 
    (323, 165, 1, 'Ver'), 
    (324, 165, 5, 'View'), 
    (327, 161, 1, 'Debe aceptar los términos de uso, la política de privacidad y de cookies para entrar.'), 
    (328, 161, 5, 'You must accept the terms and privacy policy and thecnical cookies to enter.'), 
    (329, 167, 1, 'Acepto el uso de cookies técnicas'), 
    (330, 167, 5, 'I accept thecnical cookies'), 
    (331, 168, 1, 'Acepto los términos y condiciones de uso'), 
    (332, 168, 5, 'I accept terms and use conditions'), 
    (333, 169, 1, 'Acepto las políticas de privacidad'), 
    (334, 169, 5, 'I accept privacy policies'), 
    (341, 173, 1, 'Regístrate'), 
    (342, 173, 5, 'Register'), 
    (349, 176, 1, 'Condiciones generales'), 
    (350, 176, 5, 'Conditions'), 
    (351, 184, 1, 'Auditoría energética'), 
    (352, 184, 5, 'Energy audit'), 
    (353, 183, 1, 'Protección de datos'), 
    (354, 183, 5, 'Data protection'), 
    (355, 182, 1, 'Sus datos seguros'), 
    (356, 182, 5, 'Your data safe'), 
    (357, 181, 1, 'Aviso legal'), 
    (358, 181, 5, 'Legal warning'), 
    (359, 180, 1, 'Política de privacidad'), 
    (360, 180, 5, 'Privacy policy'), 
    (371, 188, 1, 'Todos los derechos reservados.'), 
    (372, 188, 5, 'All rights reserved'), 
    (379, 192, 1, 'Nombre no válido'), 
    (380, 192, 5, 'Name invalid'), 
    (383, 194, 1, 'Teléfono no válido'), 
    (384, 194, 5, 'Phone invalid'), 
    (385, 195, 1, 'CP no válido'), 
    (386, 195, 5, 'Zip invalid'), 
    (401, 203, 1, 'Página'), 
    (402, 203, 5, 'Page'), 
    (403, 204, 1, 'de'), 
    (404, 204, 5, 'of'), 
    (413, 209, 1, 'Búsqueda'), 
    (414, 209, 5, 'Search term'), 
    (421, 213, 1, 'Mensajes'), 
    (422, 213, 5, 'Messages'),
    (423, 214, 1, 'Acepto el uso de cookies de análisis'), 
    (424, 215, 1, 'Acepto el uso de cookies funcionales'), 
    (425, 216, 1, 'Acepto el uso de cookies de publicidad'), 
    (426, 217, 1, 'Nota para el transportista, horario, etc.'), 
    (427, 217, 5, 'Notes for delivery'), 
    (430, 219, 1, 'Seleccione'), 
    (431, 219, 5, 'Select please'),
    (435, 215, 5, 'I accept functional cookies'), 
    (436, 216, 5, 'I accept publicity cookies'), 
    (437, 221, 1, 'Nueva contraseña enviada. Consulte su correo electrónico'), 
    (438, 221, 5, 'New password sent. Check your email.'), 
    (439, 222, 1, 'Solicitud de recordatorio de contraseña enviada. Consulte su correo electrónico.'), 
    (440, 222, 5, 'Reminder password request sent. Check your email.'), 
    (441, 166, 1, 'Página guardada'), 
    (442, 166, 5, 'Page saved succesfully'), 
    (443, 223, 1, 'Ok'), 
    (444, 223, 5, 'Ok'), 
    (445, 224, 1, 'El código ya no es válido. '), 
    (446, 224, 5, 'That code is no longer valid'), 
    (447, 225, 1, 'Recordatorio de contraseña'), 
    (448, 225, 5, 'Password reminder'), 
    (449, 226, 1, 'Use el siguiente enlace para recibir una nueva contraseña:'), 
    (450, 226, 5, 'Use this link for receive a new password:'), 
    (451, 227, 1, 'Falta la dirección o contiene caracteres no válidos, o es demasiado larga. Máx 100 caracteres.'), 
    (452, 227, 5, 'Address is not valid (Máx 100 chars.)'), 
    (453, 228, 1, 'Nueva contraseña'), 
    (454, 228, 5, 'New password'),
    (455, 229, 1, 'Iniciar sesión con Google'), 
    (456, 230, 1, 'Registrarse con Google'),
    (457, 231, 1, 'Comentario'),
    (458, 231, 5, 'Comment')";
*/
// -- Create table CFG_TPL

$sqls[] = "DROP TABLE IF EXISTS `CFG_TPL`";

$sqls[] = "CREATE TABLE `CFG_TPL` (
    `ID` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `NAME` VARCHAR(100),
    `TEXT` TEXT,
    `TEXT_en` TEXT,
    `ACTIVE` INT(1) DEFAULT 1,
    `CREATED_BY` INT(5),
    `CREATION_DATE` DATETIME,
    `LAST_UPDATED_BY` INT(5),
    `LAST_UPDATE_DATE` DATETIME DEFAULT current_timestamp(),
    `DESCRIPTION` TEXT,
    `CODE` INT(1) DEFAULT 1,
    `TRANSLATABLE` INT(1),
     PRIMARY KEY (`ID`), 
     UNIQUE KEY (`NAME`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Rows for table CFG_TPL

//$sqls[] =  "TRUNCATE TABLE CFG_TPL";
$sqls[] =  "INSERT INTO CFG_TPL (NAME,TEXT,TEXT_en,ACTIVE,CREATED_BY,CREATION_DATE,LAST_UPDATED_BY,LAST_UPDATE_DATE,DESCRIPTION,CODE,TRANSLATABLE) VALUES
    ('templates.email.body', '<p style=\"padding: 20px; margin: 0;\">\n[BODY]\n</p>\n\n\n', '', 1, 1, '2020-06-23 02:16:00', 1, '2021-03-18 21:57:48', 'Plantilla para emails enviados desde la web.', 1, 0), 
    ('cookies.text', '<p>&Eacute;sta p&aacute;gina utiliza cookies propias y almacenamiento local para facilitar el uso de nuestra web. Si continua navegando, supone la aceptación de nuestra pol&iacute;tica de cookies.&nbsp;</p>', '<p>This page uses its own cookies and local storage to facilitate the use of our website. If you continue browsing, you accept our cookie policy. Keep in mind that if you deactivate them, you will not be able to use our store.</p>', 1, 1, '2019-12-16 09:23:05', 2, '2021-03-13 16:20:51', '', 0, 1), 
    ('contact_form.response', '<h2>Mensaje enviado</h2><p>Muchas gracias por ponerse en contacto con nosotros.</p>', '<p> </p><h2>Message sent</h2><p>Very thanks for contact with us.</p><p> </p>', 1, 1, '2020-06-28 02:22:11', 1, '2020-07-13 23:49:44', '', 0, 1), 
    ('templates.email.register', '<table>\n<tbody>\n<tr>\n<td style=\"padding: 2px 20px;\">\n<h3>Estimado/a [FULL_NAME],</h3>\n<p>Bienvenido a [SITE_NAME].</p>\n<p>Para acceder a nuestra web haga click en Iniciar sesi&oacute;n o Mi cuenta, y a continuaci&oacute;n, introduzca su direcci&oacute;n de correo electr&oacute;nico y su contrase&ntilde;a.</p>\n</td>\n</tr>\n<tr>\n<td style=\"padding: 2px 20px;\">\n<div>Utilice los siguientes datos para iniciar sesi&oacute;n:<br />\n<table style=\"width: 98%; background-color: #ececec; border: 1px solid #cecece;\">\n<tbody>\n<tr>\n<td>\n<div><strong>E-mail</strong></div>\n</td>\n<td>\n<div>[EMAIL]</div>\n</td>\n</tr>\n<tr>\n<td>\n<div><strong>Contrase&ntilde;a</strong></div>\n</td>\n<td>\n<div>[PASSWORD]</div>\n</td>\n</tr>\n</tbody>\n</table>\n</div>\n</td>\n</tr>\n<tr>\n<td style=\"padding: 2px 20px;\">\n<p>Si tiene alguna pregunta acerca de su cuenta o cualquier otro asunto, no dude en comunicarse con nosotros mediante nuestro formulario de contacto.</p>\n</td>\n</tr>\n</tbody>\n</table>', '<p></p><table><tbody><tr><td style=\"padding:2px 20px;\"><h3>Dear [FULL_NAME],</h3><p>Welcome to [SITE_NAME].</p><p>To access our site click on Login or My account, and then enter your email address and password.</p></td></tr><tr><td style=\"padding:2px 20px;\"><div>Use the following data to log in:<br>                                                                                                                                                                                                                                                                                                                                                                                                        <table style=\"width:98%;background-color:#ececec;border:1px solid #cecece;\"><tbody><tr><td><div><strong>E-mail</strong></div></td><td><div>               [EMAIL]            </div></td></tr><tr><td><div><strong>Password</strong></div></td><td><div>                [PASSWORD]            </div></td></tr></tbody></table></div></td></tr><tr><td style=\"padding:2px 20px;\"><p>By registering as a user and logging into your account you can change your password.</p><p>If you have any questions about your account or any other matter, do not hesitate to contact us at our e-mail [SITE_EMAIL] or by phone: +34 000 000 000</p></td></tr></tbody></table><p></p>', 1, 1, '2020-07-13 19:40:08', 1, '2021-03-18 22:26:36', 'Email que se envía al usuario cuando se registra', 0, 1), 
    ('templates.email.footer', '<p style=\"background-color: #007bab; padding: 20px; margin: 0px; color: #ffffff !important; text-align: right;\"><span style=\"font-size: 14pt;\"><strong style=\"color: #ffffff !important;\">[SITE_NAME]</strong></span><br /><span style=\"font-size: 14pt; color: #ffffff !important;\">[SITE_EMAIL]</span></p>', '<p style=\"background-color: rgb(226, 226, 226);padding:20px;margin:0;\">Thank you, © <strong>[SITE_NAME]</strong><br><br><strong>Customer Service Dept.</strong><br>E-mail: [SITE_EMAIL]<br>Tlf: +34 000 000 000<br>[SITE_ADDRESS]<br>  [SITE_EMAIL]<br><br></p>', 1, 1, '2020-07-13 21:00:35', 1, '2021-03-18 22:08:50', '', 0, 1), 
    ('templates.email.header', ' <div style=\"background-color:#fafafa;\"><img style=\"margin:20px;\" src=\"[SITE_URL]/media/images/logo_email.png?ver=1.0\" ></div>', '', 1, 1, '2020-07-13 21:55:33', 1, '2020-09-05 01:37:08', '', 1, 0), 
    ('templates.email.style', 'padding:0px;font-family:Roboto,Montserrat,Arial;font-size:0.85em;font-weight:300;margin:20px auto 0 auto;border:1px solid silver;max-width:600px;background-color:white;', '', 1, 1, '2020-07-13 22:09:36', 1, '2020-09-03 00:00:40', '', 1, 0), 
    ('site.address', '<b>[SITE_NAME]</b><br />[SITE_ADDRESS]<br/>\n    <br/>\n    T.: +34 000 000 000<br/>\n    F.: +34 000 000 000<br/>\n    <br/>\n    [SITE_EMAIL]<br/>', '<b>[SITE_NAME]</b><br />[SITE_ADDRESS]<br/>\n    <br/>\n    T.: +34 000 000 000<br/>\n    F.: +34 000 000 000<br/>\n    <br/>\n    [SITE_EMAIL]<br/>', 1, 1, '2020-08-31 14:01:17', 1, '2021-03-18 22:38:26', '', 1, 1), 
    ('templates.email.new_password', '<table>\n<tbody>\n<tr>\n<td style=\"padding: 2px 20px;\">\n<h3>Estimado/a [FULL_NAME],</h3>\n</td>\n</tr>\n<tr>\n<td style=\"padding: 2px 20px;\">\n<div>Su contrase&ntilde;a ha sido cambiada:<br />\n<table style=\"width: 98%; background-color: #ececec; border: 1px solid #cecece;\">\n<tbody>\n<tr>\n<td>\n<div><strong>E-mail</strong></div>\n</td>\n<td>\n<div>[EMAIL]</div>\n</td>\n</tr>\n<tr>\n<td>\n<div><strong>Nueva contrase&ntilde;a</strong></div>\n</td>\n<td>\n<div>[PASSWORD]</div>\n</td>\n</tr>\n</tbody>\n</table>\n</div>\n</td>\n</tr>\n<tr>\n<td style=\"padding: 2px 20px;\">\n<div>​</div>\n<p>Si usted tiene alguna pregunta acerca de su cuenta o cualquier otro asunto, no dude en comunicarse con nosotros en nuestro e-mail [SITE_EMAIL] o por tel&eacute;fono: [SITE_PHONE]</p>\n</td>\n</tr>\n</tbody>\n</table>', NULL, 1, 1, '2021-04-20 18:10:20', 1, '2021-04-21 17:48:07', '', 0, 1), 
    ('templates.page.default', '<h3>[TITLE]</h3>\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', NULL, 1, 1, '2021-02-11 13:32:33', 1, '2021-03-18 22:38:34', '', 1, 0),
    ('templates.pdf.header', '<style>

@font-face {font-family: \"Montserrat ExtraBold\"; src:local(\"Montserrat ExtraBold\"), local(\"Montserrat-ExtraBold\"), url(./_fonts_/Montserrat-ExtraBold.ttf)  format(\"truetype\");}
@font-face {font-family: \"Montserrat Bold\";      src:local(\"Montserrat Bold\"),      local(\"Montserrat-Bold\"),      url(./_fonts_/Montserrat-Bold.ttf)       format(\"truetype\");}
@font-face {font-family: \"Montserrat Medium\";      src:local(\"Montserrat Medium\"),      local(\"Montserrat-Medium\"),      url(./_fonts_/Montserrat-Medium.ttf)       format(\"truetype\");}
@font-face {font-family: \"Montserrat Light\";      src:local(\"Montserrat Light\"),      local(\"Montserrat-Light\"),      url(./_fonts_/Montserrat-Light.ttf)       format(\"truetype\");}
body { background-color:#fffffe;   margin:20px; padding:0;}
* {font-size:14px;}
h1,h2,h3,h4{font-family: \"Montserrat Bold\";color:#000000;}
h1{font-size:2em;}
h2{font-size:1.6em;}
h3{font-size:1.4em;}
div,li,p,span{margin:0;}
table,ul,ol,p,th,td{}
#watermark {position: fixed;bottom:0px;left:0px;top:0px;right:0px;z-index:-1000;}
h3{color:#b60055;margin:15px 0 0 40px;line-height:0.9em;}
h4{margin:15px 0 0 40px;}
em,i{font-family: \"Montserrat Light\",\"Open Sans\", sans-serif;}
ul li{}
table{border-collapse:collapse;/*width:100%;*/}
table tr th{border-bottom:2px solid black;font-family: \"Montserrat Medium\";font-weight:normal;}
table tr td{border-bottom:1px solid #444;font-family: \"Montserrat Light\"; }
table.item tr:first-child td{border-top:1px solid #444; }
table tr th,
table tr td{margin:0;padding:3px 5px;color:#000000;font-size:12px !important;}
td.key{font-family: \"Montserrat Bold\",\"Open Sans\", sans-serif;color:#bfbfbf;}
table.header{width:100%;}
table.header tr td{border-bottom:none;}
table.detail{margin:0 0 0 0; width:100%;}
.line-address{line-height:1.2em;display:block;margin:0;font-size:1em !important;color:#444;}
.line-address.bold{font-size:1.15em !important;font-family: \"Montserrat Bold\",\"Open Sans\", sans-serif;display:block;line-height:1em;}
.line-address.extrabold{font-size:1.3em !important;font-family: \"Montserrat ExtraBold\",\"Open Sans\", sans-serif;display:block;line-height:1.2em;}

#tags{display:none;}</style>
<table class=\"header\" style=\"margin-top:20px;width:100%\">
      <tbody><tr>
      <td align=\"left\" style=\"width:30%;vertical-align:middle;\">
          <img src=\"./media/images/logo.png\" style=\"height:55px;width:auto;\">
      </td>
      <td align=\"center\" style=\"width:40%;\" class=\"address\"><b>[SITE_NAME]</b><br>[SITE_ADDRESS]</td>
      <td align=\"right\" style=\"width:30%;vertical-align:middle;\">
          <img src=\"./media/images/logo.png\" style=\"height:55px;width:auto;\">
     </td>
     </tr>
     </tbody></table>', NULL, 1, 1, '2025-11-21 22:51:33', 1, '2025-11-21 23:19:23', '', 1, 0)";


//FIX ADD contact_form.help

// -- Create table CFG_CFG

$sqls[] = "DROP TABLE IF EXISTS `CFG_CFG`";

$sqls[] = "CREATE TABLE `CFG_CFG` (
    `ID` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `K` VARCHAR(50),
    `V` VARCHAR(400),
    `DESCRIPTION` TEXT,
    `CREATED_BY` INT(5),
    `CREATION_DATE` DATETIME,
    `LAST_UPDATED_BY` INT(5),
    `LAST_UPDATE_DATE` DATETIME,
    `ACTIVE` INT(1) DEFAULT '1',
     PRIMARY KEY (`ID`), 
     UNIQUE KEY (`K`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Rows for table CFG_CFG

$sqls[] =  "INSERT INTO CFG_CFG (K,V,DESCRIPTION,ACTIVE) VALUES
    ('repo.url', 'https://software.extralab.net', '', 1), 
    ('script.version'  , '0.0.0', '',  1),
    ('script.updates'         , 'true' , 'Habilita links para actualizaciones',  1), 
 
    ('smtp.from_name', 'Site name', '',  1), 
    ('smtp.from_email', '[SITE_EMAIL]', '',  1), 
    ('smtp.server', 'smtp.gmail.com', '',  1), 
    ('smtp.user', 'nombredeusuario@gmail.com', '',  1), 
    ('smtp.password', 'incorrecta', '',  1), 
    ('smtp.port', '465', '25 para smtp normal, 465 o 587 para gmail, 587 para tls',  1), 
    ('smtp.ssl', 'false', 'true para gmail',  1), 
    ('smtp.transport', 'internal', 'php, phpmailer, etc',  1),
 
    ('oauth.google.enabled', 'false', '',  1), 
    ('oauth.google.id', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com', '',  1), 
    ('oauth.google.secret', 'cxxxxxxxxxxxxxxxxxxxxx', 'Configurar en\nhttps://console.developers.google.com',  1), 
    ('oauth.google.popup', 'false', '', 1),
 
    ('captcha.enabled', 'true', '',  1), 
    ('captcha.google_v3.public', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'Clave pública de Google rCaptcha',  1), 
    ('captcha.google_v3.secret', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'Clave secreta de Google rCaptcha',  1), 
    ('captcha.google_v3.enabled', 'false', 'Opciones: none,internal,google_v2,google_v3\nurl: https://www.google.com/recaptcha/admin/site/348637300/settings',  1), 
    ('shop.enabled', 'false', '',  1), 
    ('module_security', 'false', 'false, phpids, ...',  1), 
    ('login.username.required', 'false', '',  1), 
    ('login.card_id.required', 'false', 'Pide identificador DNI / NIE / NIF ó Card id en extranjeros,  para el registro',  1), 
    ('login.password.strength', '3', 'Fuerza de las contraseñas. Se recomienda mínimo 4, que obliga a usar mayúsculas, minúsculas, números y algún caracter especial($,&,etc)',  1), 
    ('login.register_disabled', 'false', '', 1),
    ('login.nostr.enabled', 'true', '', 1),
    ('login.passwordless.enabled', 'true', '', 1),
    ('login.default_method', 'passwordless', 'passwordless, password, nostr, ldap', 1),
    
    ('site.name', '[SITE_NAME]', '', 1), 
    ('site.phone', '+34 000 000 000', '',  1), 
    ('site.email', '[SITE_EMAIL]', '',  1), 
    ('site.keywords', '[SITE_KEYWORDS]', 'Atributo \"keywords\" (palabras clave) para SEO',  1), 
    ('site.description', '[SITE_DESCRIPTION]', 'Atributo \"description\" de la web, para SEO',  1), 
    ('site.title', '[SITE_DESCRIPTION]', 'Atributo \"title\" de la web, para SEO',  1), 
    ('site.tags.enabled', 'true', '',  1), 
    ('site.langs.enabled', 'false', 'Activa o desactiva el soporte multilenguaje', 1), 
    ('site.langs.suffix', 'true', 'Añadir  el código de idioma en las urls excepto si se trata del idioma por omisión', 1), 
    ('site.langs.shortcuts', 'false', 'Permite diferentes urls para una misma sección.\nDe momento no sólo si hay DOS idiomas activados.',1), 
    ('site.from_email', '[SITE_EMAIL]', 'Email usado como remitente en los mensajes enviados.\nSe pueden añadir ajustes module.MODULO.reply_to_email  y reply_to_name',  1), 
    ('site.from_name', 'No Reply - [SITE_NAME]', 'Nombre asociado a la cuenta usada para enviar emails',  1), 
    ('site.cookies.accept', 'true', 'No muestra aviso de cookies', 1), 
    ('site.all_cookies.check', 'false', 'Muestra checkbox para cookies de publicidad, privacidad, etc',  1), 
    ('site.lpd_accept.required', 'false', 'Muestra avisos y textos de LPD en modo \"paranoico\"',  1), 
    ('site.lastupdate', 'dd96b188', '',  1), 
    ('site.background.sql', 'SELECT FILE_NAME FROM DOC_FILES WHERE ID_PROVIDER=1 AND ACTIVE=1 ORDER BY RAND() LIMIT 1', '', 1), 
    ('site.categories.enabled', 'true', '', 1), 
    ('site.debug.email', 'debug.email@example.com', 'Se enviará un mensaje el email indicado con los los datos recibidos mediante POST y GET. Activar UNICAMENTE para tareas de depuración, por ejemplo, cuando haya que localizar un fallo, etc.',  1),
    
    ('etherpad.apikey', '', 'Api key para servidor Etherpad',  1), 
    ('etherpad.server', '', 'Etherpad es un servidor de documentos colaborativos.\n\n',  1), 
    ('etherpad.id', '', 'Identificador de cliente', 1), 
    
    ('tracking.google_analytic_id', '', 'Identificador de tracking de Google Analitic',  1), 
    
    ('users.options.num_rows', '20', 'Otras opciones: users, roles, perms, pages,  settings, templates, locations, langs,extra_fields, log_events, projects, publications \n',  1), 
    ('users.field.country', 'false', '',  1), 
    ('users.field.state', 'false', '',  1), 
    ('users.field.city', 'false', '', 1), 
    ('users.field.county', 'false', 'Si true se usa tabla de localidades, si false localidad es un campo texto', 1), 

    ('images.max_image_w', '1200', 'Reducir imágenes subidas a ésta anchura, en píxeles, si  son mas grandes',  1), 
    ('images.max_image_h', '1200', 'Reducir imágenes subidas a ésta altura, en píxeles, si  son mas grandes',  1), 
    ('images.max_thumbnail_w', '250', 'Anchura máxima de la miniaturas.',  1), 
    ('images.max_thumbnail_h', '250', 'Altura máxima de las miniaturas',  1), 
    ('images.keep_originals', 'true', 'Si false, elimina las imágenes subidas cuando hayan sido reducidas. Se recomienda false si hay poco espacio.',  1), 
    ('images.webp', 'false', 'Guardar versión webp de imágenes png y jpeg.\nRequiere PHP 7.2 o superior. ', 1), 
    ('images.quality', '90', '',  1),

    ('widget.drawing'         , 'false', '', 1),
    ('widget.news'            , 'false', '', 1),
    ('widget.alerts'          , 'false', '', 1),
    ('widget.links'           , 'false', '', 1), 
    ('widget.page'            , 'true',  '', 1), 
    ('widget.snowflake'       , 'false', '', 1), 
    ('widget.mapa-web'        , 'false', '', 1),
    ('widget.clock'           , 'false', '', 1),
    ('widget.404'             , 'true',  '', 1),
    ('widget.shoutbox'        , 'false', '', 1),
    
    ('options.submit_sitemap' , 'false', 'Si true envía sitemap.xml y robots.txt automáticamente (cuando se actualiza) a los buscadores',  1), 
    ('options.use_cdn', 'true', 'Usa CDNs para librerías comunes (JQuery, Bootstrap, FontAwesome, etc)',  1), 
    ('options.cdn_url', 'cdn.extralab.net', 'URL del CDN a utilizar',  1), 
    ('options.csp_headers.connect_src','', 'sis-t.redsys.es:25443  para pasarela redsys', 1),
    ('options.csp_headers.form_action_src','', 'sis-t.redsys.es:25443  para pasarela redsys', 1),
    ('options.csp_headers.style_src', '', 'unpkg.com', 1),
    ('options.csp_headers.script_src', '', '', 1),
    ('options.csp_headers.default_src', '', '', 1),
    ('options.csp_headers.frame_src', '', 'btcpay server url', 1),
    
    ('options.highlight_code', 'true', '', 1),    
    ('options.highlight_engine', 'prism', 'prism,highlightjs', 1),
    
    ('plugins.messages', 'none', 'ohsnap by default',  1), 
    ('plugins.epub', 'false', '', 1), 
    ('plugins.rating.enabled', 'false', '', 1), 
    ('plugins.comments.enabled', 'true', '', 1), 
    ('plugins.comments.enable_meh', 'false', '', 1),
    
    ('plugins.wysiwyg', 'extfw', '',  1), 
    ('plugins.tip_ln', 'false', '', 1),
    
    ('modules.contact.email', '[SITE_EMAIL]', 'Email donde se recibirán los envíos desde el formulario de contacto',  1), 
    ('modules.contact.name', '[SITE_DESCRIPTION]', '',  1), 
    ('modules.contact.subject', 'Formulario de contacto', '',  1), 
    ('modules.contact.bum', 'true', '',  1), 
    ('modules.banners.enabled', 'false', '',  1),
    ('modules.news.selected_langs', 'false', 'Muestra las noticias sólo si tienen título en el idioma seleccionado', 1), 
    ('modules.news.log', 'false', '', 1), 
    ('modules.docs.log', 'false', '', 1), 
    ('modules.slider.enabled', 'false', '',  1), 
    ('modules.areas.enabled', 'false', '',  1), 
    ('modules.qrcodes.enabled', 'false', '',  1),   
    ('modules.newsletter.inline_images', '', '', 1)"; 

    /*
    ('tinymce.apikey', '', 'Get in https://www.tiny.cloud/auth/signup',  1), 

    ('server.ssh.host', 'ssh_host', '', 0), 
    ('server.ssh.port', '22', '', 0), 
    ('server.ssh.username', 'ssh_user, '', 0), 
    ('server.ssh.password', 'ssh_passw', '', 0'), 

    ('repo.host', 'example.com', '', 0), 
    ('repo.username', 'ftpusser', '', 0), 
    ('repo.password', 'ftppassw', '', 0), 
    ('repo.dir', 'public_html/software/', '', 0), 

    ('options.change_underscores', 'true', '', 1), 
    ('socket.server', 'socket.example.com', '', 1), 
    ('socket.apikey', '1234567890', '', 1), 
    ('erp.enabled', 'true', '', 1), 
    ('options.forms_css_style', 'basic', '', 1), 
    ('modules.areas.enabled', 'true', '', 1), 
    ('modules.qrcodes.enabled', 'true', '', 1), 

   

    *******/
// -- Create table CFG_EXTRA_FIELDS

$sqls[] = "DROP TABLE IF EXISTS `CFG_EXTRA_FIELDS`";

$sqls[] = "CREATE TABLE `CFG_EXTRA_FIELDS` (
    `FIELD_ID` INT(5)UNSIGNED NOT NULL AUTO_INCREMENT,
    `T4BLE_NAME` VARCHAR(20) DEFAULT 'CLI_USER',
    `FIELD_NAME` VARCHAR(40),
    `FIELD_TYPE` VARCHAR(10) DEFAULT 'int',
    `FIELD_LEN` VARCHAR(40) DEFAULT '10',
    `FIELD_LABEL` VARCHAR(40),
    `WYSIWYG` INT(1),
    `FIELDSET` VARCHAR(40),
    `LOOKUP_FIELD_KEY` VARCHAR(40),
    `LOOKUP_FIELD_NAME` VARCHAR(40),
    `LOOKUP_FIELD_TABLE` VARCHAR(40),
    `FIELD_DEFAULT_VALUE` VARCHAR(40),
    `ALLOW_NULL` INT(1),
    `EDITABLE` VARCHAR(40) DEFAULT 'true',
    `HIDE` INT(1),
    `SEARCHABLE` INT(1),
    `FILTRABLE` INT(1),
    `UPLOADDIR` VARCHAR(100) DEFAULT '[SCRIPT_DIR_MEDIA]/[TABLENAME]/uploads',
    `EXTENSIONS` VARCHAR(100) DEFAULT '.pgn,.jpg,.pdf',
    `MASK` VARCHAR(50) DEFAULT '[ID]_[LANG]',
    `PLACEHOLDER` VARCHAR(100),
    `TEXTAFTER` VARCHAR(100),
    `ACTIVE` INT(1) DEFAULT '1',
     PRIMARY KEY (`FIELD_ID`), 
     UNIQUE KEY (T4BLE_NAME,FIELD_NAME)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Create table CLI_PAGES

$sqls[] = "DROP TABLE IF EXISTS `CLI_PAGES`";

$sqls[] = "CREATE TABLE `CLI_PAGES` (
`item_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`id_user` INT(7) NOT NULL DEFAULT '1',
`item_parent` INT(7),
`item_level` INT(8),
`item_order` INT(5),
`item_name` VARCHAR(150),
`item_title` VARCHAR(150),
`item_text` TEXT,
`item_active` INT(1) DEFAULT '1',
`item_visible` INT(1) DEFAULT '1',
`item_date_created` INT(8),
`item_date_modified` INT(8),
`inline_edit` INT(1) DEFAULT '1',
`FILE_NAME` VARCHAR(200),
`HTML` INT(1) DEFAULT '1',
`item_code` TEXT,
`item_code_css` TEXT,
`item_code_js` TEXT,
`item_code_php` TEXT,
`TRANSLATABLE` INT(1),
`KEYWORDS` TEXT,
`DESCRIPTION` TEXT,
`GALLERY` INT(1) DEFAULT '0',
`FILES` INT(1) DEFAULT '0',
`DOCS` INT(1) DEFAULT '0',
`ALLOW_COMMENTS` int(1) DEFAULT 1,
`ALLOW_RATING` int(1) DEFAULT 1,
 PRIMARY KEY (`item_id`), 
 UNIQUE KEY (`item_name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";// $sqls[] = 'DROP TABLE IF EXISTS CLI_PAGES';

//`item_comments_enabled` INT(1),
//`item_rating_enabled` INT(1),
//`item_votes` INT(6),
//`item_points` decimal(5,2) DEFAULT '0.00',
//`item_rating` decimal(5,2) DEFAULT '0.00',
//`item_reads` INT(6),
//`item_fullscreen` INT(1),
//`item_caption` VARCHAR(50),
//`item_caption_en` VARCHAR(50),
/**
ALTER TABLE CLI_PAGES DROP COLUMN item_comments_enabled;
ALTER TABLE CLI_PAGES DROP COLUMN item_rating_enabled;
ALTER TABLE CLI_PAGES DROP COLUMN item_votes;
ALTER TABLE CLI_PAGES DROP COLUMN item_points;
ALTER TABLE CLI_PAGES DROP COLUMN item_rating;
ALTER TABLE CLI_PAGES DROP COLUMN item_reads;
ALTER TABLE CLI_PAGES DROP COLUMN item_fullscreen;
ALTER TABLE CLI_PAGES DROP COLUMN item_caption_en;
 * 
 */

// -- Rows for table CLI_PAGES
$pages = array();
$pages[] =  ['politica-de-cookies'   , 'Politica de cookies'   , '<h3>Politica de cookies</h3>\n<p>En cumplimiento con lo dispuesto en el artículo 22.2 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y de Comercio Electrónico, esta página web le informa, en esta sección, sobre la política de recogida y tratamiento de cookies.</p>\n\n<h4>¿Qué son las cookies?</h4>\n<p>Una cookie es un fichero que se descarga en su ordenador al acceder a determinadas páginas web. Las cookies permiten a una página web, entre otras cosas, almacenar y recuperar información sobre los hábitos de navegación de un usuario o de su equipo y, dependiendo de la información que contengan y de la forma en que utilice su equipo, pueden utilizarse para reconocer al usuario.</p>\n\n<h4></h4><h4>¿Qué tipos de cookies existen?\n</h4><p><b>Cookies de análisis: </b>Son aquéllas que bien tratadas por nosotros o por terceros, nos permiten cuantificar el número de usuarios y así realizar la medición y análisis estadístico de la utilización que hacen los usuarios del servicio ofertado. Para ello se analiza su navegación en nuestra página web con el fin de mejorar la oferta de productos o servicios que le ofrecemos.<br></p>\n\n<p><b>Cookies técnicas:</b> Son aquellas que permiten al usuario la navegación a través del área restringida y la utilización de sus diferentes funciones, como por ejemplo, llevar a cambio el proceso de compra de un artículo.</p>\n\n<p><b>Cookies de personalización: </b>Son aquellas que permiten al usuario acceder al servicio con algunas características de carácter general predefinidas en función de una serie de criterios en el terminal del usuario como por ejemplo serian el idioma o el tipo de navegador a través del cual se conecta al servicio.</p>\n\n<p><b>Cookies publicitarias:</b> Son aquéllas que, bien tratadas por esta web o por terceros, permiten gestionar de la forma más eficaz posible la oferta de los espacios publicitarios que hay en la página web, adecuando el contenido del anuncio al contenido del servicio solicitado o al uso que realice de nuestra página web. Para ello podemos analizar sus hábitos de navegación en Internet y podemos mostrarle publicidad relacionada con su perfil de navegación.</p>\n\n<p><b>Cookies de publicidad comportamental: </b>Son aquellas que permiten la gestión, de la forma más eficaz posible, de los espacios publicitarios que, en su caso, el editor haya incluido en una página web, aplicación o plataforma desde la que presta el servicio solicitado. Este tipo de cookies almacenan información del comportamiento de los visitantes obtenida a través de la observación continuada de sus hábitos de navegación, lo que permite desarrollar un perfil específico para mostrar avisos publicitarios en función del mismo.</p><h4>¿Qué tipos de cookies utiliza ésta página web?</h4><p>Esta página web sólo utiliza cookies técnicas.<br></p>\n\n<h4>Desactivar las cookies</h4>\n<p>Puede usted permitir, bloquear o eliminar las cookies instaladas en su equipo mediante la configuración de las opciones del navegador instalado en su ordenador.</p>\n\n<p>En la mayoría de los navegadores web se ofrece la posibilidad de permitir, bloquear o eliminar las cookies instaladas en su equipo.</p>\n\n<p>A continuación puede acceder a la configuración de los navegadores webs más frecuentes para aceptar, instalar o desactivar las cookies:</p>\n\n<p><a href=\"https://support.google.com/chrome/answer/95647?hl=es\" target=\"_blank\" rel=\"noopener noreferrer\">Configurar cookies en Google Chrome</a></p>\n<p><a href=\"http://windows.microsoft.com/es-es/windows7/how-to-manage-cookies-in-internet-explorer-9\" target=\"_blank\" rel=\"noopener noreferrer\">Configurar cookies en Microsoft Internet Explorer</a></p>\n<p><a href=\"https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias?redirectlocale=es&amp;redirectslug=habilitar-y-deshabilitar-cookies-que-los-sitios-we\" target=\"_blank\" rel=\"noopener noreferrer\">Configurar cookies en Mozilla Firefox</a></p>\n<p><a href=\"https://support.apple.com/es-es/HT201265\" target=\"_blank\" rel=\"noopener noreferrer\">Configurar cookies en Safari (Apple)</a></p>\n\n\n<h4>Cookies de terceros</h4>\n<p>Esta página web NO utiliza servicios de terceros excepto las necesarias para&nbsp; Google Recaptcha.</p>\n\n<h4>Advertencia sobre eliminar cookies</h4>\n<p>Usted puede eliminar y bloquear todas las cookies de este sitio, y éste seguirá funcionando perfectamente, excepto que no \"recordaremos\" su nombre de usuario entre sesiones.</p>\n\n<p>Si tiene cualquier duda acerca de nuestra política de cookies, puede contactar con esta página web a través de nuestro <a href=\"contact\">Formulario de contacto</a>.</p>'];
$pages[] =  ['politica-de-privacidad', 'Politica de privacidad', '<h1>Política de privacidad</h1>\n\n<p>La dirección de nuestra web es: [SITE_URL].</p>\n\n<h2>Qué datos personales recogemos y por qué los recogemos</h2>\n\n<h3>Medios</h3>\n<p>Si subes imágenes a la web, deberías evitar subir imágenes con datos de ubicación (GPS EXIF) incluidos. Los visitantes de la web pueden descargar y extraer cualquier dato de ubicación de las imágenes de la web.</p>\n\n<h3>Cookies</h3>\n<p>Si tienes una cuenta y te conectas a este sitio, y seleccionas «Recuérdarme», instalaremos un par de cookies para guardar tu información de acceso encriptada. Si no seleccionas «Recuérdarme» no guardaremos nada.</p>\n\n<h3>Con quién compartimos tus datos</h3>\n<p>No compartimos tus datos con nadie, ni siquiera los guardamos nosotros.</p>\n\n<h3>Cuánto tiempo conservamos tus datos</h3>\n<p>Si dejas un comentario, el comentario y sus metadatos se conservan indefinidamente. Esto es para que podamos reconocer y aprobar comentarios sucesivos automáticamente, en lugar de mantenerlos en una cola de moderación.</p>\n<p>De los usuarios que se registran en nuestra web (si los hay), también almacenamos la información personal que proporcionan en su perfil de usuario. Todos los usuarios pueden ver, editar o eliminar su información personal en cualquier momento . Los administradores de la web también pueden ver y editar esa información.</p>\n\n<h3>Qué derechos tienes sobre tus datos</h3>\n<p>Si tienes una cuenta o has dejado comentarios en esta web, puedes solicitar recibir un archivo de exportación de los datos personales que tenemos sobre ti, incluyendo cualquier dato que nos hayas proporcionado. También puedes solicitar que eliminemos cualquier dato personal que tengamos sobre ti.&nbsp;</p>\n\n\n<h3>Dónde enviamos tus datos</h3>\n<p>A ningún sitio.&nbsp;</p>\n'];
$pages[] =  ['aviso-legal'           , 'Aviso legal'           , '<h1>Aviso Legal</h1><p>Este sitio web tiene carácter informativo y su contenido está destinado a facilitar información general sobre los servicios o actividades ofrecidos. El titular del sitio se reserva el derecho de modificar, suspender o cancelar cualquier aspecto del mismo sin previo aviso.</p><p>El acceso y uso de esta web implica la aceptación plena de las condiciones aquí expuestas. Queda prohibido el uso indebido de los contenidos, así como su reproducción, distribución o modificación sin autorización expresa del titular.</p><p>El titular no se hace responsable de los daños o perjuicios derivados del uso de la información aquí publicada, ni garantiza la ausencia de virus u otros elementos que puedan alterar los sistemas informáticos. Los enlaces a sitios externos no implican responsabilidad sobre sus contenidos.</p><p>Este aviso legal se rige por la legislación española. Para cualquier controversia relacionada con este sitio web, las partes se someten a los juzgados y tribunales correspondientes según la normativa aplicable.</p><p>Si necesitas más detalles, puede consultar nuestra <a href="/page/politica-de-privacidad"><strong>Política de Privacidad</strong></a> y <a href="/page/politica-de-cookies"><strong>Cookies</strong></a> (en caso de que las utilice).</p>'];
$pages[] =  ['home'                  , 'Home'                  , '<h3>Home</h3><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>...</p>'];

$sqls[] =  "INSERT INTO CLI_PAGES (id_user,item_level,item_order,item_name,item_title,item_TEXT,item_active,item_visible) VALUES
    (1, 100, 0, '".$pages[0][0]."', '".$pages[0][1]."', '".$pages[0][2]."',  1, 1), 
    (1, 100, 0, '".$pages[1][0]."', '".$pages[1][1]."', '".$pages[1][2]."',  1, 1),
    (1, 100, 0, '".$pages[2][0]."', '".$pages[2][1]."', '".$pages[2][2]."',  1, 1),
    (1, 100, 0, '".$pages[3][0]."', '".$pages[3][1]."', '".$pages[3][2]."',  1, 1)";


// -- Create table CLI_PAGES_FILES

$sqls[] = "DROP TABLE IF EXISTS `CLI_PAGES_FILES`";

$sqls[] = "CREATE TABLE `CLI_PAGES_FILES` (
    ID INT(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    id_item INT(7),
    FILE_NAME VARCHAR(200),
    file_date INT(8),
    NAME VARCHAR(100),
    ITEM_ORDER INT(5),
    ITEM_ID INT(7),
    ID_PROVIDER INT(5) DEFAULT '1',
    LINK VARCHAR(200),
    DESCRIPTION VARCHAR(200),
    ACTIVE INT(1) DEFAULT '1',
    CREATED_BY INT(5),
    CREATION_DATE DATETIME,
    LAST_UPDATED_BY INT(5),
    LAST_UPDATE_DATE DATETIME,
    MINI INT(1) DEFAULT '0',
    MAIN INT(1) DEFAULT '0',
    NAME_en VARCHAR(100),
    DESCRIPTION_en VARCHAR(200),
    `ID_CATEGORIE` INT(5) DEFAULT '1',
    `ISBN10` VARCHAR(10),
    `ISBN13` VARCHAR(13),
    DOWNLOAD_COUNT INT(7) DEFAULT '0',
    PRIMARY KEY (`ID`), 
    UNIQUE KEY (id_item,FILE_NAME)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Create table CLI_TAGS

$sqls[] = "DROP TABLE IF EXISTS `CLI_TAGS`";

$sqls[] = "CREATE TABLE `CLI_TAGS` (
    `ID` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `CAPTION` VARCHAR(50),
    `NAME` VARCHAR(100),
    `HEADER_FILE_NAME` VARCHAR(200),
    `FILE_NAME` VARCHAR(200),
    `COLOR` VARCHAR(12),
    `DESCRIPTION` TEXT,
    `ID_ORDER` INT(8),
    `ACTIVE` INT(1) DEFAULT '1',
    `CREATED_BY` INT(5),
    `CREATION_DATE` DATETIME,
    `LAST_UPDATED_BY` INT(5),
    `LAST_UPDATE_DATE` DATETIME,
    `ID_PARENT` INT(5),
    PRIMARY KEY (`ID`), 
    UNIQUE KEY (NAME)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Rows for table CLI_TAGS

$sqls[] =  "INSERT INTO CLI_TAGS (CAPTION,NAME,HEADER_FILE_NAME,FILE_NAME,COLOR,DESCRIPTION,ID_ORDER,ACTIVE) VALUES
('Etiqueta 1', 'tag_1', '', '', '#33b075', '', 1, 1 ), 
('Etiqueta 2', 'tag_2', '', '', '#ff0000', '', 2, 1)";

$sqls[] = "DROP TABLE IF EXISTS LOG_EVENTS";
$sqls[] = "CREATE TABLE LOG_EVENTS(
    ID INT(10) UNSIGNED  NOT NULL AUTO_INCREMENT,
    EVENT_DATE DATETIME,
    ID_USER INT(9),
    TYPE VARCHAR(20) DEFAULT '1',
    EMAIL VARCHAR(100),
    SUBJECT VARCHAR(100),
    MESSAGE TEXT,
    PRIMARY KEY (`ID`),
    UNIQUE KEY (ID,SUBJECT)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";


//New column: ALTER TABLE LOG_EVENTS ADD COLUMN EVENT_DATE DATETIME DEFAULT current_timestamp();
//New column: ALTER TABLE LOG_EVENTS ADD COLUMN ID_USER INT(9);

/*
Columna: EVENT_DATE datetimeFAIL: (datetime DEFAULT current_timestamp()) != (datetime)
Update: ALTER TABLE LOG_EVENTS CHANGE EVENT_DATE EVENT_DATE datetime DEFAULT current_timestamp();

Columna: ID_USER int(9)FAIL: (varchar(50)) != (int(9))
Update: ALTER TABLE LOG_EVENTS CHANGE ID_USER ID_USER VARCHAR(50);

Columna: EVENT_DATE datetime
Columna: ID_USER varchar(50)
Columna: TYPE varchar(20)

*/



// -- Create table CLI_CATEGORIES
$sqls[] = "DROP TABLE IF EXISTS `CLI_CATEGORIES`";
$sqls[] = "CREATE TABLE `CLI_CATEGORIES` (
    `CATEGORIE_ID` INT(5) UNSIGNED  NOT NULL AUTO_INCREMENT,
    `NAME` varchar(100),
    `FILE_NAME` varchar(200),
    `FILE_NAME_BIG` varchar(200),
    `DESCRIPTION` text,
    `CAT_ORDER` int(8),
    `ACTIVE` int(1) DEFAULT '1',
    `CREATED_BY` int(5),
    `CREATION_DATE` datetime,
    `LAST_UPDATED_BY` int(5),
    `LAST_UPDATE_DATE` datetime,
    ID_PARENT INT(5),
    VISIBLE int(1) DEFAULT '1',
    PRIMARY KEY (`CATEGORIE_ID`),
    UNIQUE KEY (ID_PARENT,NAME)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Rows for table CLI_CATEGORIES
$sqls[] =  "INSERT INTO CLI_CATEGORIES (NAME,FILE_NAME,FILE_NAME_BIG,DESCRIPTION,CAT_ORDER,ACTIVE,CREATED_BY,CREATION_DATE,LAST_UPDATED_BY,LAST_UPDATE_DATE) VALUES
    ('Categoría 1', '', '', '', 0, 1, 1, '2019-12-02 22:35:24', 1, '2019-12-02 22:35:24'),
    ('Categoría 2', '', '', '', 0, 1, 1, '2019-12-02 22:35:24', 1, '2019-12-02 22:35:24')";

$sqls[] = "DROP TABLE IF EXISTS `POST_RATINGS`";

$sqls[] = "CREATE TABLE `POST_RATINGS` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_id` INT(10) DEFAULT NULL,
  `post_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `rating` INT(1) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`user_id`,`post_id`,`module_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqls[] = "DROP TABLE IF EXISTS `POST_VOTES`";

$sqls[] = "CREATE TABLE `POST_VOTES` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_id` INT(10) DEFAULT NULL,
  `post_id` INT(11) DEFAULT NULL,
  `comment_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `vote_type` INT(1) DEFAULT 1,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqls[] = "DROP TABLE IF EXISTS `POST_COMMENTS`";

$sqls[] = "CREATE TABLE `POST_COMMENTS` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_id` INT(10) DEFAULT NULL,
  `post_id` INT(11) DEFAULT NULL,
  `parent_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `comment_text` text DEFAULT NULL,
  `status` INT(1) DEFAULT 1,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `approved_by` INT(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `votes_up` INT(11) DEFAULT NULL,
  `votes_down` INT(11) DEFAULT NULL,
  `votes_meh` INT(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// -- Create table POST_KARMA_DAILY

$sqls[] = "DROP TABLE IF EXISTS `POST_KARMA_DAILY`";

$sqls[] = "CREATE TABLE `POST_KARMA_DAILY` (
    `user_id` INT(10) UNSIGNED NOT NULL,
    `date_key` CHAR(10) NOT NULL,
    `spent` INT(10) NOT NULL DEFAULT '0',
    `loss` INT(10) NOT NULL DEFAULT '0',
    UNIQUE KEY (`user_id`,`date_key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
   
            // CLI_USER_TRANSACTIONS
            // from_user = payerId (cliente que pagó)
            // to_user = authorId (autor que recibe el pago)    
            // type 1 = ingreso por artículo vendido
            // amount_sats = cantidad en sats
            // invoice_id = id de la invoice en BTCPay
            // article_id = id del artículo vendido
            // created_at = NOW()

$sqls[] = "DROP TABLE IF EXISTS `CLI_USER_TRANSACTIONS`";
$sqls[] = "CREATE TABLE `CLI_USER_TRANSACTIONS` (
  `ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_user` INT(10) NOT NULL,
  `to_user` INT(10) NOT NULL,
  `transaction_type` INT(1) NOT NULL,
  `amount_sats` INT(10) NOT NULL,
  `invoice_id` VARCHAR(100) NOT NULL,
  `article_id` INT(10) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";




/************

-- Una vez instalado, te aconsejo que  
   1. mires bien cada pestaña de control_panel
   2. mires muy bien el codigo de los modulos docs y news y page

-- "page" es, digamos, el modulo por omision para crear páginas estáticas

-- Tablas principales
   CLI_ITEM  para los menus
   CLI_PAGES  para contenidos estaticos
   Una fila de CLI_ITEM define un item de menu, cada item de menu puede tener una url q se abrirá al pincharle.
   Esa url puede ser una url normal, absoluta con su http...  o relativa a un "módulo"

-- Un "modulo" es cualquier carpeta que haya dentro de _modules_
   asi, el módulo page o el modulo news existen porque existen las carpetas _modules_/page y _modules_/news

-- Para cargar un modulo en la web no hace falta que exista un menu o una fila en CLI_ITEM, 
   simplemente creas una carpeta _modules_/tusmuelas y ya podrás llamara a loquesea.com/tusmuelas

   contenido minimo de un modulo: un index.php y un init.php (nada mas)

   tienes _modules_/_template_  como plantilla

   puedes crear un modulo nuevo llamando a loqsea.com/control_panel/new/nombrepalnuevomodulo
   el nuevo modulo será una copia del modulo _template_

-- ¿Q pasa si llamas loquesea.com/cualquiercosa y no existe el modulo "cualquiercosa" ?

   pues suponiendo que el modulo "page" sea el modulo por omisión (by default) te dará la oportunidad
   de crear la pagina "cualquiercosa" (OJO:será una entrada llamada "cualquiercosa" dentro del modulo page)

-- ¿Qué es el 'módulo por omisión' ?

   Cuando se llama a una url del tipo loqsea.com/blablabla
   si existe el módulo 'blablabla' se cargara _modules_/blablabla
   si NO existe al módulo 'blablabla' se cargara _modulo_por_omision_/blablabla

   si, por ejemplo, el modulo por o omision es "news" se cargara la noticia cuyo 'name' sea 'blablabla'
   si hubiera una noticia blablabla y el modulo por omision no fuera news habria que cargar
   loqsea.com/news/blablabla para ver la noticia. Es decir, en el modulo omision  podemos omitir el
   nombre del modulo en la url
 

*************/