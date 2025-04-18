<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST["userID"]);
    $budgetID = trim($_POST["budgetID"]);
    $budget_name = trim($_POST["budget_name"]);
    $category = trim($_POST["category"]);
    $amount = trim($_POST["amount"]);

    // Step 1: Get current category from DB
    $oldCategorySql = "SELECT `category` FROM `budgets` WHERE `budgetID` = ? AND `userID` = ? AND `budget_status` != 'Deleted'";
    $stmt = $conn->prepare($oldCategorySql);
    $stmt->bind_param("ss", $budgetID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "failed", "message" => "Budget not found"]);
        exit();
    }

    $row = $result->fetch_assoc();
    $oldCategory = $row['category'];
    $stmt->close();

    // Step 2: If category changed, check if new category already exists
    if ($oldCategory !== $category) {
        $checkCategorySql = "SELECT * FROM `budgets` WHERE `userID` = ? AND `category` = ? AND `budget_status` != 'Deleted' AND `budgetID` != ?";
        $stmt = $conn->prepare($checkCategorySql);
        $stmt->bind_param("sss", $userID, $category, $budgetID);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(["status" => "exists", "message" => "You already have a budget with that category."]);
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();
    }

    // Step 3: Update the budget
    $updateSql = "UPDATE `budgets` SET `budget_name` = ?, `category` = ?, `amount` = ? WHERE `budgetID` = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssds", $budget_name, $category, $amount, $budgetID);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Budget details updated successfully"]);
    } else {
        echo json_encode(["status" => "failed", "message" => "Error updating budget: " . $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>
