<?php

// ── FEATURE: TGRAM_TEXTS — definición de tabla para el panel de admin ────────
// Este fichero define la tabla TGRAM_TEXTS para el scaffold (Table::show_table).
// Se incluye desde admin.php en el tab "Textos".
//
// Para añadir una nueva tabla administrable al módulo Telegram:
//   1. Crea la tabla en TelegramStore::ensureTables()
//   2. Crea aquí su TABLE_TGRAM_*.php siguiendo este patrón
//   3. Añade un tab en admin.php con Table::show_table('TGRAM_*')
// ─────────────────────────────────────────────────────────────────────────────

    $tabla = new TableMysql('TGRAM_TEXTS');

    $tabla->addCols([
        $tabla->field(       'id',        'int')->len(5)->editable(false)->hide(true),
        $tabla->field(   'active',       'bool')->label('Activa')->filtrable(true),
        $tabla->field( 'priority',        'int')->len(5)->label('Prioridad')->textafter('Mayor número = se comprueba antes'),
        $tabla->field( 'keywords',  'textarea')->wysiwyg(false)->searchable(true)->label('Keywords')
                      ->textafter('Palabras clave separadas por coma. El bot responderá si el mensaje contiene CUALQUIERA de ellas (sin distinción de mayúsculas).'),
        $tabla->field( 'response',  'textarea')->wysiwyg(false)->searchable(true)->label('Respuesta')
                      ->textafter('Texto que enviará el bot cuando detecte una keyword.'),
     //   $tabla->field('created_at', 'unixtime')->readonly(true)->label('Creada'),
    ]);

    $tabla->showtitle = true;
    $tabla->title     = 'Respuestas por palabras clave';
    $tabla->page      = $page;
    $tabla->orderby   = 'priority DESC, id ASC';

    $tabla->perms['delete'] = TelegramAdministrador();
    $tabla->perms['edit']   = true;//TelegramAdministrador();
    $tabla->perms['add']    = true;//elegramAdministrador();
    $tabla->perms['setup']  = TelegramRoot();
    $tabla->perms['reload'] = true;
    $tabla->perms['filter'] = true;
    $tabla->perms['view']   = true;

// ── END FEATURE: TGRAM_TEXTS ──────────────────────────────────────────────────
