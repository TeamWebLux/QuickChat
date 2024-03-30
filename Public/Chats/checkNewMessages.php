<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $id_1 = $_SESSION['user_id'];

    // Database connection
    include "./Public/Pages/Chat/app/db.conn.php"; // Update this path as needed

    $sql = "SELECT COUNT(chat_id) AS newMessages FROM chats WHERE to_id=? AND opened=0";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_1]);
    $result = $stmt->fetch();

    echo json_encode(['newMessages' => $result['newMessages'] > 0]);
} else {
    echo json_encode(['newMessages' => false]);
}
?>