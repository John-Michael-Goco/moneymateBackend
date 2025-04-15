<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['transactionID']) || empty(trim($_POST['transactionID']))) {
        echo json_encode(["status" => "failed", "message" => "Missing Transaction ID"]);
        exit;
    }

    $transactionID = trim($_POST['transactionID']);

    // Step 1: Get transaction details
    $getQuery = "SELECT `accountID`, `amount`, `transaction_type` FROM `transactions` WHERE transactionID = ?";
    $stmt = $conn->prepare($getQuery);
    $stmt->bind_param("s", $transactionID);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        echo json_encode(["status" => "failed", "message" => "Transaction not found"]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $transaction = $result->fetch_assoc();
    $amount = $transaction['amount'];
    $accountID = $transaction['accountID'];
    $transactionType = strtolower($transaction['transaction_type']);
    $stmt->close();

    // Step 2: Update account balance
    if ($transactionType === 'expense') {
        $updateSql = "UPDATE `accounts` SET balance = balance + ? WHERE accountID = ?";
    } else if ($transactionType === 'income') {
        $updateSql = "UPDATE `accounts` SET balance = balance - ? WHERE accountID = ?";
    } else {
        echo json_encode(["status" => "failed", "message" => "Invalid transaction type"]);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ds", $amount, $accountID);
    if (!$stmt->execute()) {
        echo json_encode(["status" => "failed", "message" => "Failed to update balance"]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Step 3: Soft delete the transaction
    $deleteSql = "UPDATE `transactions` SET transaction_status = 'Deleted' WHERE transactionID = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("s", $transactionID);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Transaction deleted successfully"]);
    } else {
        echo json_encode(["status" => "failed", "message" => "Failed to delete transaction"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$conn->close();
?>
