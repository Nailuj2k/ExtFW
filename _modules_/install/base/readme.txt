

Requisitos
------------------------------------------------------------------------------------

- Apache 2.* ó Nginx
- PHP  (recomendable 7.3 o superior)
- SQLite, MySQL o MariaDb


Instalación
------------------------------------------------------------------------------------

1. Subir install.php al raiz 
2. Cargar url/install.php
3. Rellenar formulario y darle a Guardar



------------------------------------------------------- NOTAS ----------------------


Nginx
------------------------------------------------------------------------------------
En NGINX no se usa .htaccess. En su lugar debe modificar el archivo de configuración
de Nginx, que será algo como /etc/nginx/sites-available/<nombre del dominio> ó 
si no ha configurado dominios: /etc/nginx/sites-available/default para que contenga
unas líneas como éstas:

    location / {
        try_files $uri $uri/ @ci_index;
    }
    location @ci_index{
        rewrite ^(.*) /index.php?$1 last;
    }


BBDD
------------------------------------------------------------------------------------
Se recomienda una bd MySQL o MariaDb, pues con SQLite hay algunas limitaciones, aunque 
para un "uso normal" es mas que suficiente. 

Cada carpeta dentro de _modules_ puede ser un módulo o apliacione web, que puede
conectarse a Oracle o SQL Server si se dispone del cliente corresponndiente.