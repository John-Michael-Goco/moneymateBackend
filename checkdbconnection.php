<?php
    require "./connect.php";

    if ($conn){
        echo json_encode(["status" => "success", "message" => "Database Connected!"]);
    }else {
        echo json_encode(["status" => "failed", "message" => "Database Connection Failed"]);
    }

    $conn->close();
?>