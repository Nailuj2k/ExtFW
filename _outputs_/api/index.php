<?php

    Header('Content-Type: application/json');
    
    // https://mycodebook.online/blogs/how-to-create-api-in-php/

    $response = array();   
    $response['status'] = 'error';
    $response['http_response_code']  = 403;
    $response['module'] = MODULE;

    if( !isset($_ARGS['api_key'])){
        
        $response['msg'] = 'Missing API key';
        
    }else{ 
        
        $api_key = Str::sanitizeName($_ARGS['api_key']);
        
        if(!isset($api_key) || trim($api_key) == '') {
            
            $response['msg'] = 'Invalid API key';
            
        }else{
            
            $user_data = Table::sqlQuery("SELECT user_id,username,user_active,user_online FROM CLI_USER WHERE api_key = '{$_ARGS['api_key']}'");
            
            if($user_data){
                
                $response['http_response_code'] = 200;
                $response['status'] = 'success';
                $response['user_data'] = $user_data;
            
                include(SCRIPT_DIR_MODULE.'/index.php');
                
            }else{

                $response['msg'] = 'User not found';

            }
        }    
    }

    http_response_code($response['http_response_code']);
    echo json_encode($response);

