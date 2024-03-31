<?php

function getConversation($user_id, $conn)
{
  /**
      Getting all the conversations 
      for current (logged in) user
   **/
  $sql = "SELECT conversations.*, MAX(chats.created_at) as last_message_time
  FROM conversations
  LEFT JOIN chats ON conversations.conversation_id = chats.conversation_id
  WHERE conversations.user_1 = ? OR conversations.user_2 = ?
  GROUP BY conversations.conversation_id
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
