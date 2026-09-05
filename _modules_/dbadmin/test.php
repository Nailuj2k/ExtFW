
<h3>Test</h3>
<?php

$sql = 'SELECT K, V FROM CFG_CFG WHERE ACTIVE=1'; // UNION ALL SELECT NAME AS K, TEXT AS V FROM CFG_TPL WHERE ACTIVE=1';

/*
$r = Table::sqlQuery($sql);


print_r($r);

*/
echo '<pre>';

    $connection = MySql_PDO::singleton();

    if($connection) {
            try {

                $query = $connection->prepare($sql);

           //     $query->execute();

                if ($query->execute()){
                    $data =  $query->fetchAll(PDO::FETCH_ASSOC);
                    print_r( $data );   
                }

            }catch (PDOException $e) {

                print_r( $e->getMessage() );

            }


    }else{

        echo 'no connection';
    }

echo '<hr>';
print_r($connection->errorInfo());
echo '</pre>';