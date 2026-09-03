<?php

// Hooks del theme hulamm.
// Este archivo se carga automaticamente desde _includes_/run.php (si existe).

Hook::add_action('on_before_show_form', function ($table, $form, $id) {
    // Ejemplo global: se ejecuta antes de renderizar cualquier formulario scaffold.
    // if ($table->tablename === 'CLI_PAGES') {
         echo '<div style="padding:8px;background:#dff0d8;border:1px solid #b2d8a8;margin-bottom:8px;">Hook global on_before_show_form activo</div>';
    // }
}, 10, 3);

Hook::add_action('on_before_show_form_CLI_PAGES', function ($table, $form, $id) {
    // Ejemplo especifico por tabla.
    // Aqui puedes modificar $form, botones, fieldsets, etc.
    echo '<div style="padding:8px;background:#d9edf7;border:1px solid #9acfea;margin-bottom:8px;">Hook de tabla CLI_PAGES activo</div>';
}, 10, 3);

Hook::add_filter('on_update', function ($result, $table, $post) {
    // Para modificar la respuesta JSON devuelta por update.
    // if ($table->tablename === 'CLI_PAGES') {
    $result['msg'] = 'Hook filter on_update_result activo. ' . ($result['msg'] ?? '');
    // }
    return $result;
}, 10, 3);

Hook::add_filter('on_update_CLI_PAGES', function ($result, $table, $post) {
    $result['msg'] = 'Hook de tabla CLI_PAGES (update) activo. ' . ($result['msg'] ?? '');
    return $result;
}, 10, 3);
