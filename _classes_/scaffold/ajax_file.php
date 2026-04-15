<?php   



        if(OUTPUT=='file' && !isset($_ARGS["filename"]) && !isset($_ARGS["ext"] ) ) {


            if(SCRIPT_DIR){
                $_ARGS = explode(  '/' , str_replace(SCRIPT_DIR.'/', '', $_SERVER['REQUEST_URI']) );
            }else{
                $_ARGS = explode(  '/' ,  $_SERVER['REQUEST_URI'] );
                for ($i = 0; $i < count($_ARGS); $i++) { $_ARGS[$i] = $_ARGS[($i+1)]; }
            }

            /*

            //   /erp/file/gcfjhgfjhfg
            //    0   1   2
            //   /file/gcfjhgfjhfg
            //    0   1


            0: erp
            1: file
            2: yMKpVP4rH4abz22ZI2ay4U0Zc8vzFSLxL6V+XkXM3nq4ka7qN-1uWiXerM3YtijU8HUM2-0HEv5baQnavZvaKWn8MIicvjedT-N74B1kcT2DI259rrs4TrN7EJiaUp4ug6jTgKZZG2I4BLy0DebvHW4Z5PLYM8IEjdHLdoz94-zdMHaKmjs8rI0CluWz7uOgirN4rqKirk37qSzUSYaz6Q==
            output: file

            */

            // Vars::debug_var($_ARGS);
            //FIX Show warning when  ARGS1 == file and/or ARGS0 = page AND module != page, then remove iif ARGS1 check below
            $_ARGS = explode('/',Crypt::md5_decrypt(Crypt::decDir($_ARGS[1]=='file'?$_ARGS[2]:$_ARGS[1]),CFG::$vars['prefix']));//$_SESSION['token']));
    
            foreach($_ARGS as $k => $v){
                if (strpos($v,'=')) {
                   $_ARGS[ substr($v,0,strpos($v,'=')) ] = substr($v,strpos($v,'=')+1); // make associative args element if format is 'name=value'
                }
            }
    
            //Vars::debug_var($_ARGS,'ARGS2');

            //die();

        }

    //}

///////////////////////  args createpath

    $debug = isset($_ARGS["debug"]);

    if( isset($_ARGS["filename"]) && 
        isset($_ARGS["ext"] ) ) {
            
        $filename  = $_ARGS["filename"];
        $ext       = $_ARGS["ext"];
        $tablename = $_ARGS["tb"];
        $counter   = $_ARGS["counter"];
        $id        = $_ARGS["id"];
        $name      = $_ARGS['name']?$_ARGS['name']:$filename ;
        $mode      = $_ARGS['mode']=='inline'?$_ARGS['mode']:'';

        if(CFG::$vars['modules'][$_ARGS[0]]['log']){
            $type = 'GET '.$ext; //'print/download';
            $_id = $_SESSION['username'] ?? '0';
            $_email = $_SESSION['user_email'] ?? '0';
            $_subject = MODULE.' GET FILE '.$name; //.$filename.'.'.$ext;
            $_text = $filename.'.'.$ext."\n".Str::escape(print_r($_ARGS,true));
            $log_sql = "INSERT INTO ".TB_LOG." (TYPE,ID_USER,EMAIL,SUBJECT,MESSAGE) VALUES('{$type}','{$_id}','{$_email}','{$_subject}','{$_text}')";
            Table::sqlExec($log_sql,false);
        } 

        if($debug) Vars::debug_var($_ARGS);

        if($_ARGS['path']) {
            $path = str_replace('+','/',$_ARGS['path']);
            if($debug) $path_old =   str_replace('media/'.$tablename.'/files','media/files',$path);
        }else if($_ARGS['uploaddir']) {
            $path = $_ARGS['uploaddir'];
            if($debug) $path_old =   'media/files';
        }
            
        if(!file_exists($path))  SYS::mkdirr($path);

        $filepath     = $path . '/' . $filename . '.' . $ext;

        if($debug){
            $filepath_old = $path_old . '/' . $filename . '.' . $ext;
            if(in_array($ext,['jpg','jpeg','png','webp','gif'])) {
                $big_filepath_old = $path_old . '/' . BIG_PREFIX . $filename . '.' . $ext;
                $big_filepath     = $path     . '/' . BIG_PREFIX . $filename . '.' . $ext;
                $tn_filepath_old  = $path_old . '/' . BIG_PREFIX . $filename . '.' . $ext;
                $tn_filepath      = $path     . '/' . BIG_PREFIX . $filename . '.' . $ext;
            }
            echo $path.' '.(is_writable($path)?'IS':'NOT').' writable <br />';
            if(file_exists($filepath_old))    {
                echo '<br />EXISTS ['.$filepath_old.']<br />';
                if(!file_exists($filepath))    {
                    echo 'copy to: ['.$filepath.']<br />';
                    copy($filepath_old,$filepath);
                }
            }else{
                echo '<br />NOT EXISTS '.$filepath_old;
            }           
            if ( file_exists($filepath)&&file_exists($filepath_old) )    {
                echo '<br />DELETING OLD file: '.$filepath_old.'<br />';
                echo $filepath_old.(is_writable($filepath_old)?'IS':'NOT').' writable <br />';
                /////////////////// unlink($filepath_old);
            }          
            if(file_exists($big_filepath_old)) rename($big_filepath_old,$big_filepath);
            if(file_exists($tn_filepath_old))  rename($tn_filepath_old,$tn_filepath);
        }

        if (preg_match("/\./", $filename)) {   //filename contains a dot :(
            if(file_exists($filepath))  {
                rename($filepath,$path . '/' .Str::sanitizeName($filename.'.'.$ext,true));
                $filename = Str::sanitizeName($filename,true);
                $filepath     = $path . '/' . $filename . '.' . $ext;
                if($tablename=='CLI_PAGES_FILES' && $id>0 &&in_array($ext,['pdf','doc','docx','xls','xlsx','epub']))
                    Table::sqlExec("UPDATE {$tablename} SET FILE_NAME = '".Str::sanitizeName($filename.'.'.$ext,true)."' WHERE ID = $id");
            }

            //TableMysql::sqlExec("UPDATE {$tablename} SET {$counter} = {$counter}+1 WHERE ID = $id");
        }

        if(in_array($ext,['jpg','jpeg','png','webp','gif'])) {

            $big_filepath     = $path     . '/' . BIG_PREFIX . $filename . '.' . $ext;
            if (file_exists($big_filepath)) $filepath=$big_filepath;

        }

        if(file_exists($filepath)) {

            $mime_type = SYS::get_mime_type($ext);
               
            if ($tablename&&$counter&&$id) {
                if($debug) Vars::debug_var("UPDATE {$tablename} SET {$counter} = {$counter}+1 WHERE ID = $id");
                Table::sqlExec("UPDATE {$tablename} SET {$counter} = {$counter}+1 WHERE ID = $id");
            }

            session_write_close ( ); 
            if($debug){ 
                echo '<pre>';
                echo 'FILE EXISTS: '.$filepath.'<br />';
                echo 'Content-Disposition: inline; filename="'.$name.'.'.$ext.'"'.'</br>';
                echo '</pre>';
            }else{
                header('Content-Type: '.$mime_type);
                if($mode=='inline'){
                    header('Content-Disposition: inline; filename="'.$name.'.'.$ext.'"');
                }else{
                    header('Content-Description: File Transfer');
                    header('Content-Disposition: attachment; filename="'.$name.'.'.$ext.'"');
                }
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');

                //header('Content-Length: ' . filesize($filepath));

                //flush(); // Flush system output buffer

                if ($_ARGS['serialized']==1) {
                    $data = unserialize(file_get_contents($filepath));

                    //header('Content-Length: ' . filesize($filepath));

                    if(is_array($data)){
                       foreach($data as $line){
                           echo $line;
                       }
                    }else{
                       print_r($data);
                    }
                }else if ($_ARGS['serialized']==2) {
                    // echo '<h3>JSON Cache file</h3>';
                    $data = unserialize(file_get_contents($filepath));
                    Vars::debug_var($data,$filepath);
                }else{
                    header('Content-Length: ' . filesize($filepath));
                    flush(); // Flush system output buffer
                    readfile($filepath);
                }

                

            }

            // die();
                
        } else {

            //Vars::debug_var($_ARGS);

            if ($_ARGS['create']==1){

                /*
                $filename  = $_ARGS["filename"];
                $ext       = $_ARGS["ext"];
                $tablename = $_ARGS["tb"];
                $counter   = $_ARGS["counter"];
                $id        = $_ARGS["id"];
                $name      = $_ARGS['name']?$_ARGS['name']:$filename ;
                $mode      = $_ARGS['mode']=='inline'?$_ARGS['mode']:'';


                $pdf_module_savedir = PRIVATE_DIR.'/customers_quotes/';
                $pdf_savedir        = $pdf_module_savedir.$row['CUSTOMER_ID'].'/'; //.$id.'/';
                $pdf_filename       = 'Presupuesto '.$row['QUOTE_ID'].'.pdf';
                $pdf_savefilename   = Str::sanitizeName($pdf_filename,true);  
                $filename           = Str::get_file_name($pdf_savefilename);  
                $type               = Str::get_file_extension($pdf_savefilename); 
                // url = 'erp/module=erp/op=pdf/table=CLI_QUOTES/id='.$row['QUOTE_ID'].'/pdf';
                */
                //include(SCRIPT_DIR_CLASSES.'/pdf.class.php');
                PDF::$html_pdf_page_num = $html_pdf_page_num?$html_pdf_page_num:false;  //FIX FIX
                PDF::$pdf_filename     = $filename . '.' . $ext; //'Presupuesto '.$row['QUOTE_ID'].'.pdf';
                PDF::$pdf_savedir     = $path;
                PDF::$pdf_savefilename = Str::sanitizeName($filename.'.'.$ext,true);//$filepath; //Str::sanitizeName($pdf_filename,true); 

                /***/      
                $_ARGS['module'] = 'erp';  //$_ARGS[0];//'erp';
                $_ARGS['table']  = $_ARGS["tb"];
                $_ARGS['op'] = 'pdf';
                //$_ARGS['id'] = $id;
                //include(SCRIPT_DIR_MODULES.'/erp/init.php');


                $_TB_FILE_ = SCRIPT_DIR_MODULES.'/'.$_ARGS[0].($_TABLES_DIR_ ?? '').'/TABLE_'.$_ARGS["tb"].'.php';

                include($_TB_FILE_);

                ob_start();
                $tabla->getPrint($_ARGS['id'],'pdf');
                /***/
                //Header('Location: '.$_ARGS[0].'/module='.$_ARGS[0].'/op=pdf/table='.$_ARGS["tb"].'/id='.$_ARGS['id'].'/pdf');

                /**/
                /***
                Vars::debug_var( PDF::$pdf_filename);
                Vars::debug_var( PDF::$pdf_savedir);
                Vars::debug_var( PDF::$pdf_savefilename);
                **/

                //include(SCRIPT_DIR_MODULE.'/index.php');
                $html = ob_get_clean();

               //$filepath     = $path . '/' . $filename . '.' . $ext;



                //  public static $html_pdf_page_num = true;
                //  public static $html_pdf_num_pages = 0;
                //  public static $html_pdf_footer = '</html>';
                //  public static $html_pdf_detail = '<p>line</p>';
                //  public static $pdf_paper_format = 'a4';
                //  public static $pdf_orientation  = 'portrait';

                PDF::html2pdf($html);

                /**/





            }else{

                //if($debug) 
                echo '<p style="font-family:Montserrat,-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen,Ubuntu,\'Droid Sans\',\'Helvetica Neue\',Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';">No existe el archivo <b>'.$name.'.'.$ext.'</b></b>'; //$filepath;
                //else
                //    header('Location: /404');
            }
        }

    } else {

        header('Location: /404');

    }
/*
}else{

    header('Location: /404');

}
*/