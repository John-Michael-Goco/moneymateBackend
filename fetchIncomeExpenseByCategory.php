<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Missing userID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);

    $incomeSql = "SELECT t.category, SUM(t.amount) as total
        FROM transactions t
        JOIN accounts a ON t.accountID = a.accountID
        WHERE t.userID = ?
            AND t.transaction_status != 'Deleted'
            AND t.category != 'Transfer'
            AND t.transaction_type = 'income'
            AND a.account_status != 'Deleted'
        GROUP BY t.category
        ORDER BY total DESC
        LIMIT 4";

    $expenseSql = "SELECT t.category, SUM(t.amount) as total
        FROM transactions t
        JOIN accounts a ON t.accountID = a.accountID
        WHERE t.userID = ?
            AND t.transaction_status != 'Deleted'
            AND t.category != 'Transfer'
            AND t.transaction_type = 'expense'
            AND a.account_status != 'Deleted'
        GROUP BY t.category
        ORDER BY total DESC
        LIMIT 4";

    $incomeData = [];
    $expenseData = [];

    // Prepare and execute income query
    if ($stmtIncome = $conn->prepare($incomeSql)) {
        $stmtIncome->bind_param("s", $userID);
        $stmtIncome->execute();
        $resultIncome = $stmtIncome->get_result();

        while ($row = $resultIncome->fetch_assoc()) {
            $incomeData[] = [
                'category' => $row['category'],
                'total' => (float) $row['total']
            ];
        }
        $stmtIncome->close();
    }

    // Prepare and execute expense query
    if ($stmtExpense = $conn->prepare($expenseSql)) {
        $stmtExpense->bind_param("s", $userID);
        $stmtExpense->execute();
        $resultExpense = $stmtExpense->get_result();

        while ($row = $resultExpense->fetch_assoc()) {
            $expenseData[] = [
                'category' => $row['category'],
                'total' => (float) $row['total']
            ];
        }
        $stmtExpense->close();
    }

    echo json_encode([
        "status" => "success",
        "incomeData" => $incomeData,
        "expenseData" => $expenseData
    ]);
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}
