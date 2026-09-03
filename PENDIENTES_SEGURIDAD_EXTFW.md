# Pendientes de seguridad de ExtFW

Este documento recoge mejoras acordadas para abordar más adelante. No describe cambios ya implementados.

## 1. Endurecer el sistema de actualizaciones remotas

### Problema actual

El descargador configura el contexto TLS con `verify_peer` y `verify_peer_name` desactivados. La conexión puede ir cifrada, pero PHP no comprueba que el certificado presentado pertenezca realmente al servidor solicitado. Un intermediario podría sustituir el paquete descargado.

También se permite indicar un host alternativo. Esta flexibilidad se quiere conservar, pero actualmente se combina con ausencia de firma del paquete y extracción directa sobre el proyecto.

La función de extracción confía en los nombres internos del ZIP. Una entrada con una ruta absoluta o con segmentos `..` podría escribir fuera del destino previsto (Zip Slip).

### Cambios pendientes

- Activar siempre la verificación TLS del certificado y del nombre del servidor.
- Conservar los hosts alternativos, pero permitirlos únicamente a usuarios Root y mediante una acción explícita y confirmada.
- Exigir HTTPS para los hosts de actualización.
- Valorar una lista configurable de hosts autorizados.
- Impedir hosts locales, direcciones loopback, redes privadas y endpoints de metadatos para evitar SSRF.
- No confiar en un hash descargado desde el mismo origen como única garantía.
- Firmar criptográficamente los paquetes de release con una clave privada y guardar en ExtFW solamente la clave pública de verificación.
- Verificar la firma antes de descomprimir o sobrescribir ningún archivo.
- Descargar y extraer inicialmente en directorios temporales.
- Inspeccionar todas las entradas del ZIP antes de extraerlas.
- Rechazar rutas absolutas, segmentos `..`, bytes nulos, nombres anómalos y enlaces simbólicos.
- Comprobar con rutas canónicas que cada archivo permanezca dentro del directorio de staging.
- Validar un manifiesto de archivos y versiones antes de instalar.
- Crear un backup recuperable antes de sustituir archivos.
- Instalar desde staging sólo después de completar todas las verificaciones.
- Registrar quién solicitó la actualización, origen, versión, firma y resultado.

### Flujo deseado

1. Un usuario Root solicita mediante POST una actualización desde un origen claramente mostrado.
2. ExtFW valida CSRF, permisos, esquema HTTPS y host.
3. Descarga el paquete a un directorio temporal con TLS verificado.
4. Verifica la firma criptográfica y el manifiesto.
5. Comprueba todas las rutas del ZIP.
6. Extrae en staging, nunca directamente sobre producción.
7. Valida el contenido extraído.
8. Crea backup.
9. Instala los archivos validados.
10. Registra el resultado y permite rollback si la instalación falla.

## 2. Separar consultas GET de operaciones que cambian el sistema

### Definición

Una operación mutante es cualquier acción que modifica estado: crear, editar o eliminar registros; escribir o borrar archivos; cambiar configuración o permisos; instalar actualizaciones; ejecutar comandos; regenerar claves; o modificar sesiones.

### Problema actual

La protección CSRF central se aplica a POST, pero algunas acciones que modifican el sistema pueden ejecutarse mediante GET. Validar que los parámetros GET tengan el formato esperado no impide CSRF: una petición maliciosa puede contener parámetros completamente válidos.

Las peticiones GET también pueden ser activadas accidentalmente por navegadores, enlaces, imágenes, redirecciones, previsualizadores, crawlers o páginas externas.

### Cambios pendientes

- Reservar GET para operaciones de lectura sin efectos laterales.
- Exigir POST para crear, modificar, borrar, instalar, actualizar, ejecutar o cambiar configuración.
- Responder `405 Method Not Allowed` cuando una operación mutante llegue mediante GET.
- Exigir token CSRF en todas las operaciones mutantes basadas en sesión.
- Validar permisos en el servidor para cada acción, sin depender de que el botón esté oculto en la interfaz.
- Validar estrictamente nombres de acción, tablas, columnas, identificadores y valores esperados.
- No fusionar parámetros de URL y cuerpo de manera que una fuente pueda alterar silenciosamente la otra.
- Migrar primero las operaciones de mayor impacto: borrado, actualizaciones, configuración, gestión de usuarios y permisos, instalación de módulos y ejecución SSH.
- Añadir pruebas que demuestren que GET no puede modificar estado y que POST sin CSRF es rechazado.

## 3. Sustituir el "recordarme" basado en contraseña

### Objetivo

Se mantiene la función "recordarme". No es necesario obligar al usuario a introducir la contraseña en cada visita. Lo que debe eliminarse es el almacenamiento de una versión reversible de la contraseña en el navegador.

### Diseño pendiente

- Al activar "recordarme", generar un selector público y un token secreto aleatorios con suficiente entropía.
- Guardar en la base de datos el selector, el hash del token, el usuario, la fecha de creación, la caducidad y metadatos básicos del dispositivo.
- Guardar en la cookie únicamente el selector y el token original, nunca la contraseña.
- Configurar la cookie como `Secure`, `HttpOnly` y `SameSite=Lax` o una política más restrictiva cuando sea compatible.
- Comparar el token mediante su hash y una función resistente a ataques temporales.
- Rotar el token después de cada uso satisfactorio para reducir la reutilización.
- Invalidar el token anterior al rotarlo y detectar posibles reutilizaciones.
- Permitir múltiples dispositivos con tokens independientes.
- Revocar los tokens al cerrar sesión, cambiar contraseña, bloquear la cuenta o solicitar "cerrar todas las sesiones".
- Establecer una caducidad limitada y limpiar periódicamente tokens vencidos.
- Ofrecer al usuario una lista de dispositivos o sesiones recordadas y la posibilidad de revocarlas.
- No registrar tokens completos ni devolverlos en mensajes de error.

### Relación con passwordless

Passwordless puede convertirse en el método de acceso predeterminado, dejando la contraseña como alternativa configurable o mecanismo de recuperación. Esto no sustituye el diseño seguro de "recordarme": ambos mecanismos son independientes.

La configuración objetivo puede ser:

- passwordless habilitado y seleccionado por defecto;
- login con contraseña opcional y desactivable;
- "recordarme" implementado mediante tokens persistentes revocables;
- recuperación de cuenta separada y reforzada.

