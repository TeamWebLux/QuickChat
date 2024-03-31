<?php

function getConversation($user_id, $conn)
{
  /**
      Getting all the conversations 
      for current (logged in) user
   **/
  $sql = "SELECT user_1, user_2, MAX(created_at) as last_message_time
  FROM (
      SELECT CASE WHEN from_id = ? THEN to_id ELSE from_id END AS user_1,
             CASE WHEN to_id = ? THEN from_id ELSE to_id END AS user_2,
             created_at
      FROM chats
      WHERE from_id = ? OR to_id = ?
  ) AS derived_table
  GROUP BY user_1, user_2
  ORDER BY last_message_time DESC";

  $stmt = $conn->prepare($sql);
  $stmt->execute([$user_id, $user_id]);

  if ($stmt->rowCount() > 0) {
    $conversations = $stmt->fetchAll();

    /**
          creating empty array to 
          store the user conversation
     **/
    $user_data = [];

    # looping through the conversations
    foreach ($conversations as $conversation) {
      # if conversations user_1 row equal to user_id
      if ($conversation['user_1'] == $user_id) {
        $sql2  = "SELECT *
            	          FROM user WHERE id=?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$conversation['user_2']]);
      } else {
        $sql2  = "SELECT *
            	          FROM user WHERE id=?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$conversation['user_1']]);
      }

      $allConversations = $stmt2->fetchAll();

      # pushing the data into the array 
      array_push($user_data, $allConversations[0]);
    }

    return $user_data;
  } else {
    $conversations = [];
    return $conversations;
  }
}
