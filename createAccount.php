<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST["userID"]);
    $account = "";
    $account_type = trim($_POST["account_type"]);
    $account_logo = trim($_POST["account_logo"]);
    $account_name = trim($_POST["account_name"]);
    $account_number = trim($_POST["account_number"]);
    $currency = trim($_POST["currency"]);
    $balance = trim($_POST["balance"]);
    $account_status = "Listed";

    if ($account_type == "Bank Account" || $account_type == "Cash" || $account_type == "Wallet" || $account_type == "Savings") {
        $account = "Cash";
    } else {
        $account = "Investment";
    }

    // Check if the account already exists
    $checkSql = "SELECT * FROM `accounts` 
    WHERE (`account_number` = ? OR `account_name` = ?) AND `userID` = ? AND `account_status` != 'Deleted'";
    if ($checkStmt = $conn->prepare($checkSql)) {
        $checkStmt->bind_param("ss", $account_number, $userID);
        $checkStmt->execute();
        $result = $checkStmt->get_result(); 

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "exists", "message" => "Account already exists."]);
        } else {
            // Insert the new account
            $sql = "INSERT INTO `accounts` (`userID`, `account`, `account_type`, `account_logo`, `account_name`, `account_number`, `currency`, `balance`, `account_status`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssssssss", $userID, $account, $account_type, $account_logo, $account_name, $account_number, $currency, $balance, $account_status);

                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Account created successfully"]);
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
