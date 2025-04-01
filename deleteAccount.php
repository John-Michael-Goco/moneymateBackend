<?php
    require "./connect.php";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $accountID = $_POST['accountID'];

        $sql = "UPDATE `accounts` set `account_status` = 'Deleted' WHERE accountID = $accountID";

        if ($conn -> query($sql) === TRUE){
            echo json_encode(["status" => "success", "message" => "Account Deleted Successfully"]);
        }else {
            echo json_encode(["status" => "failed", "message" => "Error: " . $conn -> error]);
        }
    }

    $conn->close();
?>