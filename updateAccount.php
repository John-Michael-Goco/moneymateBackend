<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accountID = $_POST['accountID'];
    $account_name = $_POST["account_name"];
    $account_number = $_POST["account_number"];
    $balance = $_POST["balance"];

    // Step 1: Check for duplicate name or number in other accounts
    $checkSql = "SELECT * FROM `accounts` WHERE account_number = ? AND accountID = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $account_number, $accountID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "failed", "message" => "Account name or number already exists."]);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();

    // Step 2: Proceed with update if no duplicates
    $sql = "UPDATE `accounts` SET account_name = ?, account_number = ?, balance = ? WHERE accountID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssdi", $account_name, $account_number, $balance, $accountID);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Account Details Updated"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Error: " . $stmt->error]);
        }

        $stmt->close(); 
    } else {
        echo json_encode(["status" => "failed", "message" => "Error in preparing statement: " . $conn->error]);
    }
}

$conn->close();
?>
