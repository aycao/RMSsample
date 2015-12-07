<?php
    
    
    $configs = include('/home/cyssndy/Web/foodorder/config.php');
    
    /*echo $configs['host'];echo "   ";
    echo $configs['username'];echo "   ";
    echo $configs['password'];echo "   ";
    echo $configs['dbname'];echo "   ";*/
    
    $con=mysqli_connect($configs['host'],$configs['username'],$configs['password'],$configs['dbname']);
    
    if (mysqli_connect_errno($con))
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }else{
        echo "successed"; 
    }
    mysqli_close($con);
    
    //echo "what?";
?>
