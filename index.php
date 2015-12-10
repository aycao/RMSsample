<?php
if(isset($_POST['request'])){
    if ($_POST['request'] == "fetch-menu" || 
            $_POST['request'] == "fetch-chief" ||
            $_POST['request'] == 'submit-order'||
            $_POST['request'] == "fetch-orders"){
        
        $configs = include('config.php');
        $conn=mysqli_connect($configs['host'],$configs['username'],$configs['password'],$configs['dbname']);
        if (mysqli_connect_errno($conn)){
           echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        
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
                
                $result = mysqli_query($conn,"SELECT * FROM orders ORDER BY orderid;");
                
                $encode = array();
                while($row = mysqli_fetch_assoc($result)){
                    $encode[] = $row;
                }
                
                $jsonobj = new stdClass();
                $jsonobj->orders = $encode;
                
                echo json_encode($jsonobj);
                break;
            }
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
                
                $sql = "INSERT INTO order (tablenumber, dishname, quantity, orderid, comment) 
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
    
}elseif (isset($_POST['submit-order'])) {
    $data = json_decode($_POST["submit-order"]);
    $data->msg = strrev($data->msg);
 
     echo json_encode($data);
    
}else{
    $response = "<h1> No POST received </h1>";
    echo $response;
}


?>