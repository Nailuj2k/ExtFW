<?php


    function editable(){
        global $_ACL;
        return $_ACL->hasPermission(MODULE.'_admin');
    }

