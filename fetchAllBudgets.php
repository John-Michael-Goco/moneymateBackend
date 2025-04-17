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

    $sql = "SELECT * FROM `budgets` WHERE `userID` = ? AND `budget_status` != 'Deleted'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $budgets = [];
        while ($row = $result->fetch_assoc()) {
            $category = $row["category"];

            // Query total amount for transactions in the current month/year and same category
            $totalSQL = "SELECT SUM(amount) AS total_spent FROM `transactions`
                            WHERE `userID` = ? AND `category` = ?
                            AND `transaction_status` != 'Deleted'
                            AND MONTH(`transaction_date`) = ? AND YEAR(`transaction_date`) = ?";
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
