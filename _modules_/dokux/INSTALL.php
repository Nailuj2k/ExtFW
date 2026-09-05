<?php

if ($_ACL->userHasRoleName('Administradores') ) { // AreasACL: usar isAreaAdmin('area') o userInAreaGroup('area','Administradores')

    AreasACL::addApp(APP_NAME,APP_NAME);

    AreasACL::addAppPermission(APP_NAME,'Ver', 'doku_view', 'Puede ver documentos del área o áreas a las que pertenece', 1, 1);
    AreasACL::addAppPermission(APP_NAME,'Añadir', 'doku_add', 'Puede añadir documentos al área o áreas a las que pertenece', 0, 1);
    AreasACL::addAppPermission(APP_NAME,'Administrar', 'doku_admin', 'Puede modificar y eliminar archivos ya procesados', 0, 1);    

}else{

    echo '<h3>Access denied</h3>';

}
