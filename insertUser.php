<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $nickname = trim($_POST["nickname"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $status = "Active";

    // Check if the email already exists
    $checkSql = "SELECT * FROM `users` WHERE `email` = ? AND `account_status` != 'Deleted'";
    if ($checkStmt = $conn->prepare($checkSql)) {
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "exists", "message" => "Email already registered."]);
        } else {
            // Insert the new user
            $sql = "INSERT INTO `users` (`first_name`, `last_name`, `nickname`, `email`, `hash_password`, `account_status`)
                    VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssss", $first_name, $last_name, $nickname, $email, $password, $status);

                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Registered successfully"]);
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
