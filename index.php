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
                if(!isset($_POST['table-number']) && 
                        !isset($_POST['dish-name']) && 
                        !isset($_POST['quantity']) &&
                        !isset($_POST['comment']) &&
                        !isset($_POST['orderid'])){
                    echo "failed: not receiving enough data.";
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
                    $jsonobj->success = 1;
                    $jsonobj->result = "Order submitted successfully.";
                    echo json_encode($jsonobj);
                    
                }else{
                    $jsonobj = new stdClass;
                    $jsonobj->success = 0;
                    $jsonobj->result = "Failed to submit order.";
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
    
}else{
    $response = "<h1> No POST received </h1>";
    echo $response;
}


?>