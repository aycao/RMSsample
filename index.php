<?php

$userequest = false;
$usejson = false;

if(isset($_POST['request'])){
    $userequest = true;
}else{
    $json = file_get_contents('php://input');
    $obj = json_decode($json,true);
    if($obj['header']['request'] == "submit-order"){
        $usejson = true;
    }
}

$configs;
$conn;

if($usejson || $userequest){
    $configs = include('config.php');
    $conn=mysqli_connect($configs['host'],$configs['username'],$configs['password'],$configs['dbname']);
    if (mysqli_connect_errno($conn)){
       echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
}

if($userequest){
    if ($_POST['request'] == "fetch-menu" || 
            $_POST['request'] == "fetch-chief" ||
            $_POST['request'] == 'submit-order' ||
            $_POST['request'] == "fetch-orders" ||
            $_POST['request'] == "update-orders"){
       
        switch($_POST['request']){
            case "fetch-menu": {
                
                $result = mysqli_query($conn,"SELECT dish,price,dishtype,imageurl FROM menu");
                
                $encode = array();
                while($row = mysqli_fetch_assoc($result)){
                    $encode[] = $row;
                }
                
                $jsonobj = new stdClass();
                $jsonobj->fooditems = $encode;
                
                echo json_encode($jsonobj);
                break;
            }
            case "fetch-chiefs":{
                
                $result = mysqli_query($conn,"SELECT firstname,lastname FROM chief GROUP BY firshname,lastname");
                
                $encode = array();
                while($row = mysqli_fetch_assoc($result)){
                    $encode[] = $row;
                }
                
                $jsonobj = new stdClass();
                $jsonobj->chiefs = $encode;
                
                echo json_encode($jsonobj);
                break;
            }
            case "fetch-orders":{
                
                $result = mysqli_query($conn,"SELECT * FROM orders ORDER BY processed, tablenumber, dishname;");
                
                $ordercount = 0;
                
                $encode = array();
                $food_orders = array();
                $dish_quant_pairs = array();
                $dish_quant_pair = new stdClass();
                $food_order = new stdClass();
                $oldorderid = "";
                $neworderid = "";
                while($row = mysqli_fetch_assoc($result)){
                    $encode[] = $row;
                    //echo $row['orderid'] .  "  " . $row['inputtime'] . "\r\n";
                    //echo json_encode($row) . "\r\n";
                    
                    $neworderid = $row['orderid'];
                    
                    if($neworderid <> $oldorderid){
                       
                        if($oldorderid <> ""){
                            $food_order->dish_quant_pairs = $dish_quant_pairs;
                            $food_orders[] = ($food_order);
                            unset($dish_quant_pairs);
                            $dish_quant_pairs = array();
                        }
                        $ordercount++;
                        
                        $oldorderid = $neworderid;
                        $food_order = new stdClass();
                        $food_order->comment = $row['comment'];
                        $food_order->orderid = $neworderid;
                        $food_order->table_number = $row['tablenumber'];
                        $food_order->processed = $row['processed'];
                        $food_order->cleared = $row['cleared'];
                        $food_order->chief = $row['chief'];
                        
                    }
                    
                    $dish_quant_pair->dishname = $row['dishname'];
                    $dish_quant_pair->quantity = $row['quantity'];
                    $dish_quant_pairs[] = ($dish_quant_pair);
                    if($row['comment'] <> "null"){
                        $food_order->comment .= $row['comment'];    
                    }
                    
                    $dish_quant_pair = new stdClass();
                    
                    
                }
                // last record
                $food_order->dish_quant_pairs = $dish_quant_pairs;
                $food_orders[] = ($food_order);
                
                //$jsonobj = new stdClass();
                //$jsonobj->orders = $encode;
                $theorders = new stdClass();
                $theorders->ordercount = $ordercount;
                $theorders->theorders = $food_orders;
                echo json_encode($theorders);
                
                break;
            }
            
            case "update-orders":{
                $orderid = $_POST['orderid'];
                $processed = $_POST['processed'];
                $cleared = $_POST['cleared'];
                
                $sql = "UPDATE orders SET cleared = " . $cleared . ", processed = " . $processed . " WHERE orderid = '" . $orderid . "' ;";
                if(mysqli_query($conn,$sql)){
                    $jsonobj = new stdClass;
                    $results = array(
                        'success' => true,
                        'result_string' => "Order updated successfully.");
                    $jsonobj->result = $results;
                    echo json_encode($jsonobj);
                    
                }else{
                    
                    //echo $sql;
                    
                    $jsonobj = new stdClass;
                    $results = array(
                        'success' => false,
                        'result_string' => "Failed to update order");
                    $jsonobj->result = $results;
                    echo json_encode($jsonobj);
                }
                
                break;
            }
            // not in use now !!!!
            case "submit-order":{ 
                if(!isset($_POST['table-number']) || 
                        !isset($_POST['dish-name'])|| 
                        !isset($_POST['quantity']) ||
                        !isset($_POST['comment']) ||
                        !isset($_POST['orderid'])){
                    $jsonobj = new stdClass;
                    $results = array(
                        'success' => 0,
                        'result_string' => "Missing parameters.");
                    $jsonobj->result = $results;
                    echo json_encode($jsonobj);
                    break;
                }
                
                $table_number = $_POST['table-number'];
                $dish_name = $_POST['dish-name'];
                $quantity = $_POST['quantity'];
                $orderid = $_POST['orderid'];
                $comment = $_POST['comment'];
                
                $sql = "INSERT INTO orders (tablenumber, dishname, quantity, orderid, comment) 
                        VALUES (" . $table_number . ", '" . $dish_name . "', " . $quantity . ", '" . $orderid . "', '" . $comment . "');" ; 
                if(mysqli_query($conn,$sql)){
                    $jsonobj = new stdClass;
                    $results = array(
                        'success' => 1,
                        'result_string' => "Order submitted successfully.");
                    $jsonobj->result = $results;
                    echo json_encode($jsonobj);
                    
                }else{
                    $jsonobj = new stdClass;
                    $results = array(
                        'success' => 0,
                        'result_string' => "Failed to submit order");
                    $jsonobj->result = $results;
                    echo json_encode($jsonobj);
                }
                
                break;
            }
        }
        
        mysqli_close($conn);
        
    }else{
        $response = "<h1> Don't know about this POST request... </h1>";
        echo $response;
    }
}
    
elseif($usejson){
    $json = file_get_contents('php://input');
    $obj = json_decode($json,true);
    
    if($obj['header']['request'] == "submit-order"){
        
        $table_number = $obj['header']['table-number'];
        $orderid = $obj['header']['orderid'];
        $comment = $obj['header']['comment'];
        $processed = $obj['header']['processed'];
        $cleared = $obj['header']['cleared'];
        
        $sql = "INSERT INTO orders (tablenumber, dishname, quantity, orderid, comment, processed, cleared) VALUES ";
        $count = count($obj['dish-quant-pairs']);
        $i = 0;
        foreach($obj['dish-quant-pairs'] as $dish_quant_pair){
            $dish_name = $dish_quant_pair['dish-name'];
            $quantity = $dish_quant_pair['quantity'];
            $sql .= "(" . $table_number . ", '" . 
                    $dish_name . "', " . 
                    $quantity . ", '" . 
                    $orderid . "', '" . 
                    $comment . "', " .
                    $processed . ", " .
                    $cleared . ")" ; 
            if((++$i) === $count){
                $sql .= "; ";
            }else{
                $sql .= ", ";
            }
        }
        
        if(mysqli_query($conn,$sql)){
            $jsonobj = new stdClass;
            $results = array(
                'success' => true,
                'result_string' => "Order submitted successfully.");
            $jsonobj->result = $results;
            echo json_encode($jsonobj);
            
        }else{
            
            //echo $sql;
            
            $jsonobj = new stdClass;
            $results = array(
                'success' => false,
                'result_string' => "Failed to submit order");
            $jsonobj->result = $results;
            echo json_encode($jsonobj);
        }
        
    }else{ 
        echo "Undentified json request";
    }
    mysqli_close($conn);
}

else{
    $contents = file_get_contents("greeting.html");
    echo $contents;
}

?>

