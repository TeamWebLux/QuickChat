<?php

function getChats($id_1, $id_2, $conn)
{
    $data = getUserDataByUsername($id_1, $conn);
    $role = $data['role'];
    if ($role == 'User') {


        $sql = "SELECT * FROM chats
           WHERE (from_id=? OR to_id=?)
        
           ORDER BY chat_id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_2, $id_2]);
    } else {
        $sql = "SELECT * FROM chats
            WHERE (from_id=? AND to_id=?)
            OR    (to_id=? AND from_id=?)
            ORDER BY chat_id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_1, $id_2, $id_1, $id_2]);
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_2, $id_2]);
    }

    if ($stmt->rowCount() > 0) {
        $chats = $stmt->fetchAll();
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
    $query = "SELECT * FROM user WHERE uid = $username";

    // Execute the query
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        // Fetch the user data from the result
        $userData = $result->fetch_assoc();

        // Free the result set
        $result->free_result();

        // Return the user data as an array
        return $userData;
    } else {
        // Return null if no result found
        return null;
    }
}
