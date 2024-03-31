<?php

function getChats($id_1, $id_2, $conn) {
    // Get the role of the second user
    $data = getUserDataByUsername($id_2, $conn);
    $role = $data['role'];

    // Define the initial SQL query and parameters based on the user role
    if ($role == 'User') {
        $sql = "SELECT * FROM chats
                WHERE (from_id = ? OR to_id = ?)
                ORDER BY chat_id ASC";
        $params = [$id_2, $id_2];
    } else {
        $sql = "SELECT * FROM chats
                WHERE (from_id = ? AND to_id = ?)
                OR    (to_id = ? AND from_id = ?)
                ORDER BY chat_id ASC";
        $params = [$id_1, $id_2, $id_2, $id_1];
    }

    // Prepare and execute SQL statement
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Fetch all chats if available
    if ($stmt->rowCount() > 0) {
        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If role is 'User', append participants to the chats array
        if ($role == 'User') {
            $participants = [];
            foreach ($chats as $chat) {
                $otherUserId = ($chat['from_id'] == $id_2) ? $chat['to_id'] : $chat['from_id'];
                if (!isset($participants[$otherUserId])) {
                    $participants[$otherUserId] = getUserDataByUsername($otherUserId, $conn);
                }
            }

            // Instead of appending, you might want to return participants separately or integrate them into chats differently
            // For simplicity, adding participants as a separate entry in the result
            return ['chats' => $chats, 'participants' => $participants];
        } else {
            return $chats;
        }
    } else {
        // Return an empty array if no chats found
        return [];
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
