<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["budgetID"]) || empty(trim($_POST["budgetID"]))) {
        echo json_encode(["status" => "failed", "message" => "Missing Budget ID"]);
        exit;
    }

    $budgetID = trim($_POST["budgetID"]);

    $sql = "SELECT * FROM `budgets` WHERE `budgetID` = ? AND `budget_status` != 'Deleted'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $budgetID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $category = $row["category"];
            $userID = $row["userID"];

            // Get current month and year
            $currentMonth = date("m");
            $currentYear = date("Y");

            // Join with accounts and exclude deleted ones
            $totalSQL = "SELECT SUM(t.amount) AS total_spent 
                FROM `transactions` t
                INNER JOIN `accounts` a ON t.accountID = a.accountID
                WHERE t.userID = ? 
                AND t.category = ? 
                AND t.transaction_status != 'Deleted' 
                AND a.account_status != 'Deleted'
                AND MONTH(t.transaction_date) = ? 
                AND YEAR(t.transaction_date) = ?";

            $totalStmt = $conn->prepare($totalSQL);
            $totalStmt->bind_param("ssii", $userID, $category, $currentMonth, $currentYear);
            $totalStmt->execute();
            $totalResult = $totalStmt->get_result();
            $totalRow = $totalResult->fetch_assoc();

            $row["total_spent"] = $totalRow["total_spent"] ?? 0;

            echo json_encode(["status" => "success", "budgetDetails" => $row]);

            $totalStmt->close();
        } else {
            echo json_encode(["status" => "failed", "message" => "Budget not found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$conn->close();
