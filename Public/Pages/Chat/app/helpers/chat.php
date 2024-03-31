<?php

function getChats($id_1, $id_2, $conn)
{
    $data = getUserDataByUsername($id_2, $conn);
    $role = $data['role'];
    if ($role == 'User') {
        $sql = "SELECT * FROM chats
           WHERE (from_id=? OR to_id=?)
           ORDER BY chat_id ASC";
        echo $sql;
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_2, $id_2]); // Binding $id_2 twice
    } else {
        $sql = "SELECT * FROM chats
            WHERE (from_id=? AND to_id=?)
            OR    (to_id=? AND from_id=?)
            ORDER BY chat_id ASC";
        echo $sql;

        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_1, $id_2, $id_2, $id_1]); // Corrected the order of parameters
    }

    if ($stmt->rowCount() > 0) {
        $chats = $stmt->fetchAll();
        print_r($chats);
        return $chats;
    } else {
        $chats = [];
        return $chats;
    }
}
function getChatPage($id_1, $id_2, $conn)
{

    $sql = "SELECT * FROM bmessages
            WHERE (from_id=? AND pagename=?)
            OR    (pagename=? AND from_id=?)
            ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_1, $id_2, $id_1, $id_2]);

    if ($stmt->rowCount() > 0) {
        $chats = $stmt->fetchAll();
        return $chats;
    } else {
        $chats = [];
        return $chats;
    }
}
function getUserDataByUsername($username, $conn)
{
    // Sanitize input to prevent SQL injection
    $username = ($username);

    // SQL query to retrieve user data by username
    $query = "SELECT * FROM user WHERE id = ?"; // Corrected the query with a placeholder

    // Execute the query with bound parameter
    $stmt = $conn->prepare($query);
    $stmt->execute([$username]);

    // Fetch the user data
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the user data
    return $userData;
}
