<?php
    require "./connect.php";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $userID = $_POST['userID'];

        $sql = "UPDATE `users` set `account_status` = 'Deleted' WHERE `userID` = $userID";

        if ($conn -> query($sql) === TRUE){
            echo json_encode(["status" => "success", "message" => "User Deleted Successfully"]);
        }else {
            echo json_encode(["status" => "failed", "message" => "Error: " . $conn -> error]);
        }
    }

    $conn->close();
?>