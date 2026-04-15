<?php

        function editable($id=false){
            global $_ACL;
            if (!$_SESSION['userid'] /*|| !$id*/) return false;
            if ($_ACL->HasPermission('edit_items')) return true;
            //Vars::debug_var(ITEM_NAME,'ITEM_NAME');    
            //                    Vars::debug_var("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_name= '".ITEM_NAME."'",'SQL');     

            if($id)
            $item = Table::getFieldsValues("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_url = (SELECT item_name FROM ".TB_PAGES." WHERE item_id='".$id."')");
            else
            $item = Table::getFieldsValues("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_name= '".ITEM_NAME."'");

            //Vars::debug_var($item,'$item');     
            $parents = array();
            $module_id = $item['item_id'];
            $parent_id = $item['item_parent'];
            $ir = $_ACL->getItemRoles($module_id);
            $ur = $_ACL->getUserRoles();
            if(is_array($ir)&&is_array($ur)){
                //Vars::debug_var($module_id,'$module_id');     
                //Vars::debug_var($parent_id,'$parent_id');     
                $laps = 0;
                if($parent_id>0) {
                    $parents[] = $parent_id;
                    while ($parent_id>0){
                        if(++$laps>10) break;  // prevent recursion
                        //Vars::debug_var("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_id = '".$parent_id."'",'SQL');     
                        $item = Table::getFieldsValues("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_id = '".$parent_id."'");
                        $module_id = $item['item_id']; 
                        $parent_id = $item['item_parent'];
                        if($parent_id>0) { 
                            $parents[] = $parent_id;
                            $ir = array_merge($ir,$_ACL->getItemRoles($module_id));
                        }
                    }
                }
                //Vars::debug_var($parents,'$parents');     
                //Vars::debug_var(implode(',',array_values($ur)),'USERROLES');     
                //Vars::debug_var(implode(',',array_values($ir)),'ITEMROLES');     
                $uir = array_intersect(array_values($ir),array_values($ur));       
                //Vars::debug_var(implode(',',$uir),'ITEMUSERROLES');        
                $ed = count($uir)>0;
            }
            return $ed; 
        }
