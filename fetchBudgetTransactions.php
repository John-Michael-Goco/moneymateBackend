<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing userID"]);
        exit;
    }

    if (!isset($_POST["budgetID"]) || empty(trim($_POST["budgetID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing budgetID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);
    $budgetID = trim($_POST["budgetID"]);

    // Get the budget details first
    $budgetSQL = "SELECT * FROM `budgets` WHERE `budgetID` = ? AND `userID` = ? AND `budget_status` != 'Deleted'";
    if ($budgetStmt = $conn->prepare($budgetSQL)) {
        $budgetStmt->bind_param("ss", $budgetID, $userID);
        $budgetStmt->execute();
        $budgetResult = $budgetStmt->get_result();

        if ($budgetRow = $budgetResult->fetch_assoc()) {
            $category = $budgetRow["category"];

            // Get current month and year
            $currentMonth = date("m");
            $currentYear = date("Y");

            // Get transactions related to this budget's category, this month/year
            $sql = "SELECT t.* 
                    FROM `transactions` t
                    INNER JOIN `accounts` a ON t.accountID = a.accountID
                    WHERE t.userID = ? 
                    AND t.category = ?
                    AND t.transaction_status != 'Deleted' 
                    AND a.account_status != 'Deleted'
                    AND MONTH(t.transaction_date) = ? 
                    AND YEAR(t.transaction_date) = ?
                    ORDER BY t.transaction_date DESC, t.transaction_type ASC";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssii", $userID, $category, $currentMonth, $currentYear);
                $stmt->execute();
                $result = $stmt->get_result();

                $transactions = [];
                while ($row = $result->fetch_assoc()) {
                    $transactions[] = $row;
                }

                echo json_encode([
                    "status" => "success",
                    "category" => $category,
                    "transactions" => $transactions
                ]);

                $stmt->close();
            } else {
                echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "failed", "message" => "Budget not found"]);
        }

        $budgetStmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$conn->close();
