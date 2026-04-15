<?php

if(DB_ENGINE=='scaffold'){

'JS'         => SCRIPT_DIR_CLASSES . '/scaffold/table.class.php',
'Table'      => SCRIPT_DIR_CLASSES . '/scaffold/table.class.php',
'TableMysql' => SCRIPT_DIR_CLASSES . '/scaffold/table.mysql.class.php',
'TableSqlite'=> SCRIPT_DIR_CLASSES . '/scaffold/table.sqlite.class.php',
'TableOracle'=> SCRIPT_DIR_CLASSES . '/scaffold/table.oracle.class.php',
'Field'      => SCRIPT_DIR_CLASSES . '/scaffold/field.class.php',

'dummyField'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'fieldset'          => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'FORM'              => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formElement'       => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formElementHtml'   => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formInput'         => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formTextarea'      => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formInputProgress' => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
//'formInputCCC'      => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formSubmit'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formButton'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formHidden'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
//'NEW_formInputColor'=> SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formInputColor'    => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formInputDate'     => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formInputDateTime' => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formInputTime'     => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formSelect'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'BaseFormSelectDb'  => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formSelectDb'      => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formCheckbox'      => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formRadio'         => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formFile2'         => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
'formFile'          => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    

include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/consts.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/field.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/table.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.fieldset.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.textarea.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.html.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.hidden.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.input.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.number.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.button.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.checkbox.class.php');
include(SCRIPT_DIR_CLASSES.'/'.DB_ENGINE.'/form.element.select.class.php');


'iEvents'                => SCRIPT_DIR_CLASSES . '/scaffold/table.events.class.php',
'defaultTableEvents'     => SCRIPT_DIR_CLASSES . '/scaffold/table.events.class.php',
'defaultTableEventsTags' => SCRIPT_DIR_CLASSES . '/scaffold/table.events.tags.class.php',