<?php
    require "./connect.php";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $budgetID = $_POST['budgetID'];

        $sql = "UPDATE `budgets` set `budget_status` = 'Deleted' WHERE `budgetID` = $budgetID";

        if ($conn -> query($sql) === TRUE){
            echo json_encode(["status" => "success", "message" => "Budget Deleted Successfully"]);
        }else {
            echo json_encode(["status" => "failed", "message" => "Error: " . $conn -> error]);
        }
    }
?>