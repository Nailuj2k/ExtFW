# Enhanced Select

Componente Web que moderniza la experiencia de un `<select>` clasico: busqueda en vivo, seleccion multiple, tags personalizados, navegacion por teclado y mas.

## Caracteristicas

- Soporta seleccion simple y multiple.
- Busqueda y filtrado en tiempo real.
- Navegacion por teclado (Arrow keys, Enter, Tab, Escape).
- Tags personalizados escribiendo texto.
- Boton de limpiar opciones seleccionadas.
- Modos de busqueda: contains, startsWith, exact.
- API publica y eventos personalizados.
- Accesible (atributos ARIA).
- Integracion AJAX opcional para crear opciones al vuelo.

## Instalacion

```html
<script src="enhanced-select.js"></script>
```

## Uso basico

### Seleccion simple
```html
<enhanced-select placeholder="Selecciona una opcion">
    <option value="1">Opcion 1</option>
    <option value="2">Opcion 2</option>
    <option value="3">Opcion 3</option>
</enhanced-select>
```

### Seleccion multiple
```html
<enhanced-select multiple placeholder="Selecciona varias opciones">
    <option value="js">JavaScript</option>
    <option value="py">Python</option>
    <option value="go">Go</option>
    <option value="rs">Rust</option>
</enhanced-select>
```

### Tags personalizados
```html
<enhanced-select multiple keyboard-navigation placeholder="Escribe para crear tags">
    <option value="red">Rojo</option>
    <option value="blue">Azul</option>
    <option value="green">Verde</option>
</enhanced-select>
```

## Atributos

| Atributo | Tipo | Descripcion |
|----------|------|-------------|
| `placeholder` | string | Texto a mostrar cuando no hay seleccion. |
| `disabled` | boolean | Deshabilita el componente. |
| `required` | boolean | Marca el campo como obligatorio. |
| `clearable` | boolean | Muestra un boton para limpiar la seleccion. |
| `keyboard-navigation` | boolean | Habilita navegacion por teclado. |
| `multiple` | boolean | Permite seleccion multiple. |
| `search-mode` | string | Modo de busqueda: `contains`, `startsWith`, `exact`. |
| `ajax-url-add` | string | URL para crear opciones dinamicas. Puede incluir `[TEXT]` y recibe el valor tambien en el cuerpo `POST`. |

## API publica

```javascript
const select = document.querySelector('enhanced-select');

// Obtener valores seleccionados (objetos completos)
const values = select.getValue();

// Propiedad value (compatible con <select>)
console.log(select.value); // string | string[] | 0 | -1
select.value = '2';        // Selecciona la opcion con value="2"

// Manipular opciones
select.addOption('new-value', 'Nueva opcion');
select.removeOption('value-to-remove');

// Habilitar / deshabilitar
select.enable();
select.disable();

// Limpiar seleccion
select.clearSelection();

// Configuracion actual
console.log(select.config);

// URL AJAX
select.ajax_url_add = '/api/items/add/text=[TEXT]';
```

## Eventos

```javascript
select.addEventListener('change', () => {
    console.log('La seleccion cambio');
});

select.addEventListener('select-change', (event) => {
    console.log('Valores:', event.detail.values);
    console.log('Opciones:', event.detail.selected);
});

select.addEventListener('custom-tag-created', (event) => {
    console.log('Nuevo tag:', event.detail.text, event.detail.value);
});
```

## Propiedad `value` y texto manual

- En modo simple, devuelve el `value` del `<option>` seleccionado.
- Si el campo esta vacio, devuelve `0`.
- Si el usuario escribe un texto que no coincide con ninguna opcion, devuelve `-1` hasta que se confirme o limpie.
- En modo multiple, devuelve un array con los valores seleccionados.

```javascript
const select = document.querySelector('enhanced-select');
select.value = '3';   // Selecciona la opcion con value="3"
select.value = 0;     // Limpia la seleccion
console.log(select.value); // '3', 0 o -1 segun el estado
```

## Anadir opciones mediante AJAX

Cuando se define `ajax-url-add`, el componente mostrara una opcion “Add {texto}” mientras el usuario escribe algo que no existe en la lista. Al pulsarla se ejecuta este flujo:

1. Se hace un `POST` a la URL indicada.
2. El texto va en el cuerpo (`text=valor`, codificado como `application/x-www-form-urlencoded`). Si la URL contiene `[TEXT]`, se sustituye por el valor codificado.
3. La respuesta debe devolver el identificador de la nueva opcion: texto plano (`24`) o JSON con `{ "id": 24 }`.
4. La opcion se anade y queda seleccionada automaticamente.

```html
<enhanced-select placeholder="Paises" ajax-url-add="/api/countries/add/text=[TEXT]">
    <option value="es">Espana</option>
    <option value="mx">Mexico</option>
</enhanced-select>
```

```php
<?php
header('Content-Type: application/json');
$texto = $_POST['text'] ?? '';
$id = guardarEnBD($texto); // Implementa tu logica de guardado
echo json_encode(['id' => $id]);
```

```javascript
select.addEventListener('select-change', (event) => {
    console.log('Valores actuales:', event.detail.values);
});
```

## Navegacion por teclado

| Tecla | Accion |
|-------|--------|
| `↓` | Siguiente opcion |
| `↑` | Opcion anterior |
| `Enter` | Selecciona la opcion activa o crea un tag personalizado |
| `Tab` | Crea un tag personalizado (modo multiple) |
| `Escape` | Cierra el desplegable |

## Estructura DOM

```html
<enhanced-select class="enhanced-select">
    <div class="select-wrapper">
        <div class="input-wrapper">
            <div class="selected-values">
                <div class="selected-tag">
                    Tag Text
                    <button class="remove-tag">&times;</button>
                </div>
            </div>
            <input type="text" />
        </div>
        <div class="dropdown">
            <div class="option">Opcion 1</div>
            <div class="option active">Opcion 2</div>
        </div>
        <button class="clear-button">&times;</button>
    </div>
</enhanced-select>
```

Clases principales:

- `.enhanced-select`
- `.select-wrapper`
- `.input-wrapper`
- `.selected-values`
- `.selected-tag`
- `.remove-tag`
- `.dropdown`
- `.option`
- `.option.active`
- `.option.disabled`
- `.clear-button`
- `.has-values`

## Ejemplo completo

```html
<enhanced-select 
    multiple
    clearable
    keyboard-navigation
    search-mode="startsWith"
    placeholder="Busca tecnologias..."
    ajax-url-add="/api/tags/add/text=[TEXT]">
    <option value="js" selected>JavaScript</option>
    <option value="ts">TypeScript</option>
    <option value="py">Python</option>
    <option value="java" disabled>Java</option>
</enhanced-select>
```

```javascript
const select = document.querySelector('enhanced-select');
select.addEventListener('select-change', (event) => {
    console.log('Tecnologias:', event.detail.values);
});
```

## Compatibilidad

- Navegadores modernos con soporte Web Components.
- IE 11+ requiere polyfills.

## Licencia

MIT License
