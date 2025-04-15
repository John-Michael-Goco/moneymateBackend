<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accountID = $_POST['accountID'];
    $account_name = $_POST["account_name"];
    $account_number = $_POST["account_number"];
    $balance = $_POST["balance"];
    
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
