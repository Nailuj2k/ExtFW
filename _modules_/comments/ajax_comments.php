<?php 
 
    if (isset($_ARGS['op'])){
            
        if       ($_ARGS['op'] == 'render_comments'){
            
            // TEST /comments/ajax/op=render_comments/module_id=1/post_id=9/container_id=comments_container/test=1

            $post = $_ARGS['post_id'] ?? null;

            if(!$post){ 
                echo t('POST_ID_MISSING');
                exit;
            }

            $comments = new Comments( $post );

            $container_id = $_ARGS['container_id'] ?? 'comments_container';

            $html = $comments->renderComments(array(
                'container_id' => $container_id,
                'module_id'    => $_ARGS['module_id'] ?? null,
            ), isset($_ARGS['test']) );

            //            $html = APP::$shortcodes->do_shortcode($html);
            echo $html;

        }else if ($_ARGS['op'] == 'add_comment'){

            //TEST /comments/ajax/op=add_comment/module_id=1/user_id=1/post_id=9/parent_id=4/comment_text=blablabla/test=1
        
            $result = array();
            $result['error'] = 0;

            $post = $_ARGS['post_id'] ?? null;

            if(!$post){ 
                $result['error'] = 1;
                $result['msg'] = t('POST_ID_MISSING');
                echo json_encode($result);
                exit;
            }

            $comments = new Comments( $post );

            // IF ENCRYPTED TEXTAREA FIELDS (if not, comment this block)
            $_encrypted_text = $_ARGS['comment_text'];           
            $_decrypted_text = isset($_ARGS['test']) ? $_encrypted_text : Crypt::crypt2str($_encrypted_text,$_SESSION['token']);

            if($_encrypted_text !== NULL && $_decrypted_text === NULL){ 
                $result['error'] = 1;
                $result['msg'] = t('TOKEN_HAS_EXPIRED_PLEASE_RELOAD_SESSION'); //<br>'.$col->fieldname.'<br>ENC: '.$_encrypted_text.'<br>DEC: '.$_decrypted_text.'<br>TOKEN'.$_SESSION['token']);
                echo json_encode($result);
                exit;
            }else{
                $_ARGS['comment_text'] = $_decrypted_text;
            }
            //ENDIF ENCRYPTED TEXTAREA FIELDS

            $query = $comments->saveComment($_ARGS);              
            
            if ($query) {
                $query['url_avatar'] = Login::getUrlAvatar();
                $result['comment']=$query;
                $result['msg']=t('MESSAGE_RECEIVED','Mensaje recibido');
            }else{
                $result['error']=1;
                $result['msg']=$comments->getError(); //'Error al recibir el mensaje '.print_r($query,true );
            }
            echo json_encode($result);

        }else if ($_ARGS['op'] == 'moderate_comment'){
            
            $result = array();
            $result['error'] = 0;

            $post = $_ARGS['post_id'] ?? null;

            if(!$post){ 
                $result['error'] = 1;
                $result['msg'] = t('POST_ID_MISSING');
                echo json_encode($result);
                exit;
            }

            $comments = new Comments($post); 

            $result['error'] = $comments->moderateComment($_ARGS['comment_id'], $_ARGS['status']);
           
             if($result['error']!==0){
                $result['msg'] = t('ERROR_MODERATING_COMMENT','Error al moderar el comentario');
            } else {
                $result['msg'] =  $_ARGS['status']==Comments::APPROVED ? t('MESSAGE_APPROVED','Mensaje aprobado') : t('MESSAGE_REJECTED','Mensaje rechazado');
            }
            $result['msg'] .= '<br>ID:'.$_ARGS['comment_id'].' STATUS:'.$_ARGS['status'];

            $result = array_merge($result, $_ARGS);
            echo json_encode($result);       
            
        }else if ($_ARGS['op'] == 'vote_comment'){
            
            $result = array();
            $result['error'] = 0;

            $post = $_ARGS['post_id'] ?? null;

            if(!$post){ 
                $result['error'] = 1;
                $result['msg'] = t('POST_ID_MISSING');
                echo json_encode($result);
                exit;
            }

            $comments = new Comments($post); 

            $result = $comments->voteComment($_ARGS['comment_id'], $_ARGS['vote']);
           
            $result = array_merge($result, $_ARGS);
            echo json_encode($result);       
            
        }else if ($_ARGS['op'] == 'get_comment'){
                                            
            $result = array();
            $result['error'] = 0;
            
            $post = $_ARGS['post_id'] ?? null;

            if(!$post){ 
                $result['error'] = 1;
                $result['msg'] = t('POST_ID_MISSING');
                echo json_encode($result);
                exit;
            }

            $comments = new Comments( $post );

            $query = $comments->getComment($_ARGS);              
            
            if ($query) {
                $query['url_avatar'] = Login::getUrlAvatar();
                $result['comment']=$query;
                $result['msg']=t('MESSAGE_RECEIVED','Mensaje recibido');
            }else{
                $result['error']=1;
                $result['msg']=$comments->getError(); //'Error al recibir el mensaje '.print_r($query,true );
            }
            echo json_encode($result);

        }else if ($_ARGS['op'] == 'edit_comment'){
        
            $result = array();
            $result['error'] = 0;
            
            // TEST /news/ajax/op=edit_comment/module_id=2/user_id=1/post_id=11/comment_id=60/parent_id=0/comment_text=Bla bla bla/test=1

            $post = $_ARGS['post_id'] ?? null;

            if(!$post){ 
                $result['error'] = 1;
                $result['msg'] = t('POST_ID_MISSING');
                echo json_encode($result);
                exit;
            }

            $comments = new Comments( $post );
            
            // IF ENCRYPTED TEXTAREA FIELDS (if not, comment this block)
            $_encrypted_text = $_ARGS['comment_text'];           
            $_decrypted_text = isset($_ARGS['test']) ? $_encrypted_text : Crypt::crypt2str($_encrypted_text,$_SESSION['token']);

            if($_encrypted_text !== NULL && $_decrypted_text === NULL){ 
                $result['error'] = 1;
                $result['msg'] = t('TOKEN_HAS_EXPIRED_PLEASE_RELOAD_SESSION'); //<br>'.$col->fieldname.'<br>ENC: '.$_encrypted_text.'<br>DEC: '.$_decrypted_text.'<br>TOKEN'.$_SESSION['token']);
                echo json_encode($result);
                exit;
            }else{
                $_ARGS['comment_text'] = $_decrypted_text;
            }
            //ENDIF ENCRYPTED TEXTAREA FIELDS

            $query = $comments->editComment($_ARGS);              
            
            if ($query) {
                $query['url_avatar'] = Login::getUrlAvatar();
                $result['comment']=$query;
                $result['msg']=t('MESSAGE_EDITED','Mensaje editado');
            }else{
                $result['error']=1;
                $result['msg']=$comments->getError(); //'Error al recibir el mensaje '.print_r($query,true );
            }
            echo json_encode($result);
        /*******
        }else if ($_ARGS['op'] == 'get_comment_user_votes'){

            $result = array();
            $result['error'] = 0;
            
            $post = $_ARGS['post_id'] ?? null;

            if(!$post){ 
                $result['error'] = 1;
                $result['msg'] = t('POST_ID_MISSING');
                echo json_encode($result);
                exit;
            }
            
            $comments = new Comments( $post );

            $votes = $comments->getUserVotes();
            echo json_encode($votes);
        **/

        }else if ($_ARGS['op'] == 'render_rating'){

            // TEST /comments/ajax/op=render_comments/module_id=1/post_id=9/container_id=comments_container/test=1

            $post = $_ARGS['post_id'] ?? null;

            if(!$post){ 
                echo t('POST_ID_MISSING');
                exit;
            }          

             
            $rating = new Rating('vintage_demo',$_ARGS['post_id']);
             /*
            $rating_query = Table::sqlQueryPrepared(
                'SELECT avg(rating) AS item_rating, count(id) AS item_votes FROM POST_RATINGS WHERE module_id = ? AND post_id = ?',
                [$_ARGS['module_id'], $_ARGS['post_id']]
            ) ?? [];

            $rating->initialRating = $rating_query[0]['item_rating'] ?? 0;
            $rating->totalVotes = $rating_query[0]['item_votes'] ?? 0;             
            */
            $rating->ajaxurl = MODULE.'/ajax/op=rating/module='.$_ARGS['module_id'].'/id='.$_ARGS['post_id'];
          //  $rating->theme = 'test';  //vintage';
            //$rating->shape='heart';
           // $rating->setSize(25); // Un poco más grande para mostrar mejor el efecto
            $rating->render();
            

        }else if ($_ARGS['op'] == 'rating'){

            $result = array();
            $result['error'] = 0;
            Rating::$module = $_ARGS['module'] ?? 1;
            $rating = new Rating('vintage_demo', $_ARGS['id']);

            $result = $rating->setRating($_ARGS['rating']);
            
            // Verificar si hubo error al establecer el rating
            if (isset($result['error']) && $result['error'] !== 0) {
                // Ya tiene error y msg, solo añadir datos extra
                $result['data']['container_id'] = $_ARGS['container_id'] ?? null;
                $result['data']['timestamp'] = time();
            } else {
                $rating_query = $rating->getRating();
                
                $result['new_rating']  = $rating_query['item_rating'] ?? 0;
                $result['total_votes'] = $rating_query['item_votes'] ?? 0;      
                $result['data']['container_id'] = $_ARGS['container_id'] ?? null;
                $result['data']['action'] = 'create';
                $result['data']['timestamp'] = time();
            }
            
            echo json_encode($result);


        }else if ($_ARGS['op'] == 'withdraw' ) {

            $storeId = CFG::$vars['btcpay']['store_id'];
            ///////// $apiKey  = CFG::$vars['shop']['bitcoin']['btcpay']['api_key'];
            $apiKey  = CFG::$vars['btcpay']['api_key'];

            $userid = ($_SESSION['valid_user'] ?? false) && !empty($_SESSION['userid']) ? (int)$_SESSION['userid'] : 0;
            if ($userid <= 0) {
                echo json_encode([
                    "success" => false,
                    "error"   => 'USER_ID_MISSING'
                ]);
                exit;
            }

            $csrfToken = $_ARGS['token'] ?? null;
            if (!$csrfToken || $csrfToken !== ($_SESSION['token'] ?? null)) {
                echo json_encode([
                    "success" => false,
                    "error"   => 'INVALID_TOKEN'
                ]);
                exit;
            }

            $_encrypted_text = $_ARGS['invoice_ln'] ?? null;
            if (!$_encrypted_text) {
                echo json_encode([
                    "success" => false,
                    "error"   => 'INVOICE_MISSING'
                ]);
                exit;
            }

            $_decrypted_text = Crypt::crypt2str($_encrypted_text, $_SESSION['token']);
            if ($_decrypted_text === null || $_decrypted_text === '') {
                echo json_encode([
                    "success" => false,
                    "error"   => 'TOKEN_HAS_EXPIRED_PLEASE_RELOAD_SESSION'
                ]);
                exit;
            }

            $invoice_ln = $_decrypted_text;
            if (!preg_match('/^lnbc/i', $invoice_ln)) {
                echo json_encode([
                    "success" => false,
                    "error"   => 'INVALID_INVOICE_FORMAT'
                ]);
                exit;
            }
            
            $sql = "SELECT COALESCE(balance_sats, 0) AS balance_sats FROM CLI_USER WHERE user_id = ?";
            $params = [$userid];
            $balance = Table::sqlQueryPrepared( $sql, $params )[0]['balance_sats'] ?? 0;
            if ($balance <= 0) {
                echo json_encode([
                    "success" => false,
                    "error"   => 'INSUFFICIENT_FUNDS',
                    "balance" => (int)$balance
                ]);
                exit;
            }

            $parsedAmountSats = null;
            if (preg_match('/^lnbc([0-9]+)([munp]?)/i', $invoice_ln, $m)) {
                $amount = (float)$m[1];
                $unit   = strtolower($m[2] ?? '');
                $factors = [
                    ''  => 100000000,
                    'm' => 100000,
                    'u' => 100,
                    'n' => 0.1,
                    'p' => 0.0001,
                ];
                if (isset($factors[$unit])) {
                    $parsedAmountSats = (int)floor($amount * $factors[$unit]);
                }
            }

            if ($parsedAmountSats === null || $parsedAmountSats <= 0) {
                echo json_encode([
                    "success" => false,
                    "error"   => 'INVOICE_AMOUNT_REQUIRED',
                    "balance" => (int)$balance
                ]);
                exit;
            }

            if ($parsedAmountSats > $balance) {
                echo json_encode([
                    "success" => false,
                    "error"   => 'INSUFFICIENT_FUNDS',
                    "balance" => (int)$balance,
                    "requested" => (int)$parsedAmountSats
                ]);
                exit;
            }
            


            $ch = curl_init( CFG::$vars['btcpay']['url']."/api/v1/stores/$storeId/lightning/BTC/invoices/pay");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "BOLT11" => $invoice_ln
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: token ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // allow LN payment to settle
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);     // fail fast on DNS/conn issues
            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode === 0) {
                echo json_encode([
                    "success" => false,
                    "error"   => 'BTCPAY_REQUEST_FAILED: ' . ($curlError ?: 'NO_RESPONSE'),
                    "httpCode" => $httpCode
                ]);
                exit;
            }

            $invoice = json_decode($response, true);
            header("Content-Type: application/json");

            if ($httpCode !== 200 || isset($invoice['code'])) {
                $errorMsg = $invoice['message'] ?? t('ERROR_CREATING_INVOICE','Error creando la invoice');
                echo json_encode([
                    "success" => false,
                    "error"   => $errorMsg
                ]);
            } else {
                $sql = "UPDATE CLI_USER SET balance_sats = COALESCE(balance_sats, 0) - ? WHERE user_id = ?";
                $params = [$parsedAmountSats, $userid];
                Table::sqlQueryPrepared($sql, $params);

                Table::sqlQueryPrepared(
                    "INSERT INTO CLI_USER_TRANSACTIONS (from_user, to_user, transaction_type, amount_sats, commission_sats, invoice_id, module_id, article_id, payment_method, direct_payment, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$userId, 0, 2, $parsedAmountSats, 0, '', 4, 0, 'lightning', 1, time()]
                );

                echo json_encode([
                    "success"      => true,
                    "invoiceId"    => $invoice['id'] ?? null,
                    "checkoutLink" => $invoice['checkoutLink'] ?? null,
                    "balance"      => (int)max(0, $balance - $parsedAmountSats)
                ]);
            }



        }else if ($_ARGS['op'] == 'btc_rating') {

            $storeId = CFG::$vars['btcpay']['store_id'];
            $apiKey  = CFG::$vars['btcpay']['api_key'];
            
            $ch = curl_init(CFG::$vars['btcpay']['url']."/api/v1/stores/$storeId/rates?currencyPair=BTC_EUR");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: token $apiKey"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($response, true);
            $rate = $data[0]['rate'] ?? 0;
            
            header("Content-Type: application/json");
            echo json_encode(["rate" => (float)$rate]);
            exit;


        }else if ($_ARGS['op'] == 'invoice_ln' ) {

                
                $storeId = CFG::$vars['btcpay']['store_id'];
               //$apiKey  = CFG::$vars['shop']['bitcoin']['btcpay']['api_key'];
                $apiKey  = CFG::$vars['btcpay']['api_key'];
                
                //MODULE 
                // Recoger el ID del post desde la URL
                $module = $_ARGS['module'] ?? null;
                $postId = $_ARGS['post'] ?? null;
                $userid = $_ARGS['user'] ?? ip_as_integer();

                $userid = $_SESSION['valid_user'] && $_SESSION['userid'] ? $_SESSION['userid'] : $userid;
               
                if(!$postId || !$module || (int)$userid<0){ 
                    
                    $errorMsg = 'MODULE_ID_MISSING or POST_ID_MISSING'.' ['.$module.']['.$postId.']['.$userid.']';
                    
                    echo json_encode([
                        "success" => false,
                        "error"   => $errorMsg
                    ]);

                    exit;
                }

                // FIX CHECK AUTHOR ID FROM MODULE/POST
                $authorId = null;
                
                //echo '<br> module: ['.$module.']';
                //echo '<br> postId: '.$postId;
                //echo '<br> userid: '.$userid;

                     if ($module == 1)  $sql = "SELECT id_user AS USER_ID FROM CLI_PAGES WHERE item_id = ? LIMIT 1";
                else if ($module == 2)  $sql = "SELECT USER_ID FROM NOT_NEWS WHERE NOT_ID = ? LIMIT 1";
                else if ($module == 3)  $sql = "SELECT USER_ID FROM BLG_BLOG WHERE BLG_ID = ? LIMIT 1";
                //else if ($module == 4)  $sql = "SELECT user_id AS USER_ID FROM CLI_USER order BY user_id LIMIT 1";  //Module 4 is timextamp, pyment is for web admin (first user)
                
                if($module==4){   // 4 == timextamping
                    $authorId =  $_SESSION['userid'];
                }else{
                    $row = Table::sqlQueryPrepared($sql, [$postId])[0] ?? null;    
                    $authorId = (int)($row['USER_ID'] ?? 0);
                }

                //echo '<br> authorId: '.$authorId; 

                //return false;

                $amountSats = (int)($_ARGS['amount'] ?? 1000);
                if ($amountSats < 100) $amountSats = 100;
                if ($amountSats > 1000000) $amountSats = 1000000;

                //$amountSats = 1000;   //FIX Payer can change amount ???? ¿show list of amounts to pay when click in pay b utton?
                $amountBtc  = $amountSats / 100000000;

                $ch = curl_init(CFG::$vars['btcpay']['url']."/api/v1/stores/$storeId/invoices");
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: token $apiKey",
                "Content-Type: application/json"
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $data = [
                    "amount"   => $amountBtc,
                    "currency" => "BTC",
                // "checkout" => [ "paymentMethods" => ["BTC-LN","BTC"] ],
                // "checkout" => [ "paymentMethods" => ["BTC-Lightning"] ],
                // "checkout" => [ "paymentMethods" => ["BTC-LNURL"] ],
                    "metadata" => [
                        "webhook"    => SCRIPT_HOST.'/page/checkout/bitcoin/callback/raw/',
                        "articleId"  => $postId,
                        "moduleId"   => $module,
                        "userId"     => $userid,     // quien paga
                        "authorId"   => $authorId,   // quien cobra
                        "amountSats" => $amountSats,
                    ]                
                ];
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // <-- recoger el código HTTP
                curl_close($ch);

               // print_r($response);

                $invoice = json_decode($response, true);
                header("Content-Type: application/json");

                if ($httpCode !== 200 || isset($invoice['code'])) {
                    // Error detectado
                    $errorMsg = $invoice['message'] ?? t('ERROR_CREATING_INVOICE','Error creando la invoice');
                    echo json_encode([
                        "success" => false,
                        "error"   => $errorMsg
                    ]);
                } else {
                    // Éxito
                    echo json_encode([
                        "success"      => true,
                        "invoiceId"    => $invoice['id'] ?? null,
                        "checkoutLink" => $invoice['checkoutLink'] ?? null
                    ]);
                }
                

        }

    }