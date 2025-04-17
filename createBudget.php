<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST["userID"]);
    $budget_name = trim($_POST["budget_name"]);
    $category = trim($_POST["category"]);
    $amount = trim($_POST["amount"]);
    $budget_status = "Listed";

    // Check if the budget already exists
    $checkSql = "SELECT * FROM `budgets` WHERE `userID` = ? AND `budget_name` = ? AND `budget_status` != 'Deleted'";
    if ($checkStmt = $conn->prepare($checkSql)) {
        $checkStmt->bind_param("ss", $userID, $budget_name);
        $checkStmt->execute();
        $result = $checkStmt->get_result(); 

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "exists", "message" => "Budget already exists."]);
        } else {
            // Insert the new budget
            $sql = "INSERT INTO `budgets` (`userID`, `budget_name`, `category`, `amount`, `budget_status`)
                    VALUES (?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssds", $userID, $budget_name, $category, $amount, $budget_status);

                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Budget created successfully"]);
                } else {
                    echo json_encode(["status" => "failed", "message" => "Error: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(["status" => "failed", "message" => "Error preparing statement: " . $conn->error]);
            }
        }
        $checkStmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Error preparing check statement: " . $conn->error]);
    }
}

$conn->close();
?>
