<?php
    require "./connect.php";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $goalID = $_POST['goalID'];

        $sql = "UPDATE `goals` SET `goal_completion` = 'Complete', `completed_at` = CURRENT_DATE WHERE `goalID` = $goalID";

        if ($conn->query($sql) === TRUE){
            echo json_encode(["status" => "success", "message" => "Goal Complete!"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Error: " . $conn->error]);
        }
    }
?>
