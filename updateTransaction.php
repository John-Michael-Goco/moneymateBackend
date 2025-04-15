<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST["userID"]);
    $transactionID = trim($_POST["transactionID"]);
    $transaction_name = trim($_POST["transaction_name"]);
    $amount = floatval(trim($_POST["amount"]));
    $category = trim($_POST["category"]);
    $transaction_date = trim($_POST["transaction_date"]);
    $notes = trim($_POST["notes"]);
    $account_type = trim($_POST["account_type"]);
    $account_name = trim($_POST["account_name"]);

    // 1. Fetch old transaction data
    $fetchSQL = "SELECT * FROM `transactions` WHERE transactionID = ?";
    $stmt = $conn->prepare($fetchSQL);
    $stmt->bind_param("i", $transactionID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo json_encode(["status" => "error", "message" => "Transaction not found."]);
        exit;
    }

    $oldTransaction = $result->fetch_assoc();
    $oldAmount = floatval($oldTransaction["amount"]);
    $oldAccountID = $oldTransaction["accountID"];
    $oldTransactionType = $oldTransaction["transaction_type"];
    $stmt->close();

    // 2. Reverse the old transaction effect on the old account
    if ($oldTransactionType === "income") {
        $reverseSQL = "UPDATE `accounts` SET balance = balance - ? WHERE accountID = ?";
    } else {
        $reverseSQL = "UPDATE `accounts` SET balance = balance + ? WHERE accountID = ?";
    }
    $stmt = $conn->prepare($reverseSQL);
    $stmt->bind_param("di", $oldAmount, $oldAccountID);
    $stmt->execute();
    $stmt->close();

    // 3. Fetch new accountID and balance
    $accountSQL = "SELECT `accountID`, `balance` FROM `accounts` WHERE `account_type` = ? AND `account_name` = ? AND `userID` = ? AND `account_status` != 'Deleted'";
    $stmt = $conn->prepare($accountSQL);
    $stmt->bind_param("sss", $account_type, $account_name, $userID);
    $stmt->execute();
    $accountResult = $stmt->get_result();

    if ($accountResult->num_rows !== 1) {
        echo json_encode(["status" => "error", "message" => "Account not found."]);
        exit;
    }

    $account = $accountResult->fetch_assoc();
    $newAccountID = $account["accountID"];
    $currentBalance = floatval($account["balance"]);
    $stmt->close();
    
    // 4. Check for sufficient balance if it's an expense
    if ($oldTransactionType === "expense" && $amount > $currentBalance) {
        echo json_encode(["status" => "error", "message" => "Insufficient balance for this transaction."]);
        exit;
    }

    // 5. Update the transaction record
    $updateSQL = "UPDATE `transactions` 
        SET `userID` = ?, `accountID` = ?, `transaction_name` = ?, `amount` = ?, 
        `category` = ?, `transaction_date` = ?, `notes` = ?
        WHERE `transactionID` = ?";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param(
        "sisdsssi",
        $userID,
        $newAccountID,
        $transaction_name,
        $amount,
        $category,
        $transaction_date,
        $notes,
        $transactionID
    );

    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Failed to update transaction: " . $stmt->error]);
        exit;
    }
    $stmt->close();

    // 6. Update the balance of the new account
    $newBalance = ($oldTransactionType === "expense") ? $currentBalance - $amount : $currentBalance + $amount;
    $balanceSQL = "UPDATE `accounts` SET `balance` = ? WHERE `accountID` = ?";
    $stmt = $conn->prepare($balanceSQL);
    $stmt->bind_param("di", $newBalance, $newAccountID);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "Transaction updated successfully."]);
    $conn->close();
}
