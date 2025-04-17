<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $budgetID = trim($_POST["budgetID"]);
    $budget_name = trim($_POST["budget_name"]);
    $category = trim($_POST["category"]);
    $amount = trim($_POST["amount"]);
    $start_date = trim($_POST["start_date"]);
    $end_date = trim($_POST["end_date"]);
    
    $sql = "UPDATE `budgets` SET `budget_name` = ?, `category` = ?, `amount` = ?, `start_date` = ?, `end_date` = ? WHERE `budgetID` = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssdsss", $budget_name, $category, $amount, $start_date, $end_date, $budgetID);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Budget Details Updated"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Error: " . $stmt->error]);
        }

        $stmt->close(); 
    } else {
        echo json_encode(["status" => "failed", "message" => "Error in preparing statement: " . $conn->error]);
    }
}
$conn->close();
?>
