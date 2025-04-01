<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `email` = ? AND `account_status` != 'Deleted'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['hash_password'])) { 
            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "user" => [
                    "userID" => $user["userID"],
                    "first_name" => $user["first_name"],
                    "last_name" => $user["last_name"],
                    "nickname" => $user["nickname"],
                    "email" => $user["email"],
                    "password" => $password,
                    "account_status" => $user["account_status"]
                ]
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Incorrect password"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "User not found"
        ]);
    }
    $stmt->close();
    $conn->close();
}
?>

