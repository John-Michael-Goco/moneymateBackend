<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing userID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);

    // Get the current month and year
    $currentMonth = date("m");
    $currentYear = date("Y");

    $sql = "SELECT * FROM `budgets` WHERE `userID` = ? AND `budget_status` != 'Deleted' LIMIT 3";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $budgets = [];
        while ($row = $result->fetch_assoc()) {
            $category = $row["category"];

            // JOIN with accounts table and check both transaction and account status
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
            $budgets[] = $row;

            $totalStmt->close();
        }

        echo json_encode(["status" => "success", "budgets" => $budgets]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$conn->close();
