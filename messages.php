<?php
/**
* Kaylune Private Messaging System
* @author Kayla Parker
* Last Update: May 15 2018
*/

class Messaging extends database {


/* ALL GETS ---> */
  /*
     Function to pull all of a users' conversations
  */
  public function getConversations($id)
  {
    return database::retrieveAllRows(database::query("SELECT id, user1, user2, name, lastUpdated FROM conversations WHERE (user1 = $id OR user2 = $id) AND ( ( user1Deleted != $id AND user2Deleted != $id )  ) ORDER BY lastUpdated DESC"));
  }

  /*
     Function to pull the information on one conversation
  */
  public function getConversation($id)
  {
    return database::getArray(database::query("SELECT id, user1, user2, name, lastUpdated FROM conversations WHERE id=$id"));
  }

  /*
     Function to count how many conversations a user has
  */
  public function countMessages($id)
  {
    return database::numRows(database::query("SELECT id FROM messages WHERE (userFrom = $id OR userTo=$id) AND (fromDeleted != $id OR toDeleted != $id)"));
  }

  /*
     Function to pull all the messages in this conversation
  */
  public function getMessages($id, $user)
  {
    return database::retrieveAllRows(database::query("SELECT id, userFrom, userTo, timeSent, timeRead, message, fromDeleted, toDeleted FROM messages WHERE conversation=$id AND ( ( toDeleted != $user AND fromDeleted != $user ))"));
  }

  /*
     Function to get a user's friends
  */
  public function getFriends($id)
  {
    return database::retrieveAllRows(database::query("SELECT fromID, toID FROM friends WHERE ( fromID = {$id} OR toID = {$id} ) AND status = 1"));
  }

/* GRABS OF NON-MESSAGING INFORMATION: */
  /*
    Function to grab other users' information
  */
  public function getOtherUser($id)
  {
    return database::getArray(database::query("SELECT id, username, userlevel, avatar FROM members WHERE id={$id}"));
  }

  /*
    Function to grab other users' information by username
  */
  public function getUserByUsername($name)
  {
    return database::getArray(database::query("SELECT id, username, userlevel, avatar FROM members WHERE username=%s", $name));
  }


  /*
    Function to grab avatar
  */
  public function getUserAvatar($id)
  {
    return database::getArray(database::query("SELECT image FROM avatars WHERE id=$id"));
  }

  /*
    Function to decide the capacity of an inbox
  */
  public function getCapacity($userlevel)
  {
    if($userlevel == 10 || $userlevel == 2) $capacity = 2000; //admins and mods
    else $capacity = '50'; //user

    return $capacity;
  }


/* ALL UPDATES ------------------> */

  /*
    Function to set time
  */
  public function setSeen($convo, $user)
  {
    database::query("UPDATE messages SET timeRead = UNIX_TIMESTAMP() WHERE (timeRead IS NULL || timeRead = 0) AND userTo = {$user} AND conversation = {$convo}");
  }


/* ALL INSERTS  ---------------> */

  /*
    Function to send a message.
    Returns true/false based on if message was entered into the database successfully.
  */
  public function sendMessage($p_from, $p_to, $msg, $convo)
  {
    $error = false;

    //empty error checks
    if (!$convo)
    {
      $error = true;
      echo '<div style="text-align:center;display:block;"><font color="red">Error: This conversation does not exist.<br /></font></div>';
    }
    if (!$msg) //this shouldn't be possible bc of checks before the function but to be safe
    {
      $error = true;
      echo '<div style="text-align:center;display:block;"><font color="red">Error: You must enter a message!<br /></font></div>';
    }
    if (!$p_to)
    {
      $error = true;
      echo '<div style="text-align:center;display:block;"><font color="red">Error: you must send your message to someone!<br /></font></div>';
    }
    if (!$p_from) //this is the user trying to send - should always exist
    {
      $error = true;
      echo '<div style="text-align:center;display:block;"><font color="red">Error: Please contact an administrator. <br /></font></div>';
    }
    //end error checks

    $error = self::checkRoom($p_from, $p_to);

    if ($error == false)
    {
      database::query("INSERT INTO `messages` (`id`, `userFrom`, `userTo`, `timeSent`, `message`, `conversation`, `fromDeleted`, `toDeleted`) VALUES (NULL, {$p_from}, {$p_to}, UNIX_TIMESTAMP(), %s, {$convo}, 0, 0)", $msg);
      database::query("UPDATE `conversations` SET `lastUpdated` = UNIX_TIMESTAMP() WHERE id={$convo}");

      return true; //success!
    }

    return false;
  }

  /*
    Function to start a conversation.
    Returns the conversation info.
    $user1 = user sending
  */
  public function newConversation($name, $user1, $user2)
  {
    $error = false;

    $error = self::checkRoom($p_from, $p_to);

    //error checks
    if (!$name || strlen($name) > 50 || strlen($name) < 2)
    {
      $error = true;
      echo '<div style="text-align:center;display:block;"><font color="red">Error: The conversation must have a name between 2 and 50 characters.<br /></font></div>';
    }
    if (!$user1 || !$user2)
    {
      $error = true;
      echo '<div style="text-align:center;display:block;"><font color="red">Error: You must enter a user! <br /></font></div>';
    }

    if ($error == false)
    {
      //inserts
      database::query("INSERT INTO `conversations` ( `user1`, `user2`, `name`, `timeBegan`, `lastUpdated`, `user1Deleted`, user2Deleted) VALUES ({$user1}, {$user2}, %s, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0,0)", $name);

      return database::getArray(database::query("SELECT id, name FROM conversations ORDER BY id DESC LIMIT 1")); //returns the new conversation
    }

    else
      return -1;

  }

/* ALL DELETES  ---------------> */

    /*
      Function to delete a conversation.
      Deletes the conversation for the user and messages in it
    */
    public function deleteConversation($id, $user)
    {
      $messages = database::retrieveAllRows(database::query("SELECT * FROM messages WHERE conversation={$id}"));

      foreach($messages as $message) //delete each message
        self::deleteMessage($message['id'], $user);

      $convo = database::getArray(database::query("SELECT user1, user2, user1Deleted, user2Deleted FROM conversations WHERE id={$id}"));
      if ($convo)
      {
        if($convo['user1'] == $user) //user is user1
        {
          //if user2 has not deleted (is 0) , else if they have (is 1 or something else)
          if( $convo['user2Deleted'] == 0 ) database::query("UPDATE conversations SET user1Deleted={$user} WHERE user1={$user} AND id={$id}"); //update
          else
          {
            $messages = self::getMessages($id, $user);
            foreach ($messages as $msg)
              self::deleteMessage($msg['id'], $user);
          }
        }
        else //user is user2
        {
          //if user1 has not deleted , else if they have
          if( $convo['user1Deleted'] == 0 ) database::query("UPDATE conversations SET user2Deleted={$user} WHERE user2={$user} AND id={$id}"); //update
          else
          {
            $messages = self::getMessages($id, $user);
            foreach ($messages as $msg)
              self::deleteMessage($msg['id'], $user);
          }
        }
      }
      else
      {
        echo '<div style="text-align:center;display:block;"><font color="red">Error: Conversation does not exist!<br /></font></div>';
        return false;
      }

      return true;
    }

    /*
      Function to delete a single message
      If both users have deleted the message, moves the message to archive
    */
    public function deleteMessage($id, $user)
    {

      $message = database::getArray(database::query("SELECT id, userFrom, userTo, timeSent, message, conversation, toDeleted, fromDeleted FROM messages WHERE id={$id}"));
      if ($message)
      {
        if( $message['userFrom'] == $user ) //user is the userFrom of this message (aka, sent it)
        {
          //if other user has not deleted
          if( $message['toDeleted'] == 0 )
            database::query("UPDATE messages SET fromDeleted={$user} WHERE userFrom={$user} AND id={$id}");
          else //archive this bitch ((then delete))
          {
            database::query("INSERT INTO messagesArchive (`id`, `userFrom`, `userTo`, `timeSent`, `message`, `timeMoved`, `conversation`) VALUES ( %u, %u, %u, %u, %s, UNIX_TIMESTAMP(), %u)", $message['id'], $message['userFrom'], $message['userTo'], $message['timeSent'], $message['message'], $message['conversation']);
            database::query("DELETE FROM messages WHERE id={$message['id']}");
          }
        }
        else //user is reciever of this message
        {
          //if other user has not deleted
          if( $message['fromDeleted'] == 0 )
            database::query("UPDATE messages SET toDeleted={$user} WHERE userTo={$user} AND id={$id}");
          else //archive this bitch ((then delete))
          {
            database::query("INSERT INTO messagesArchive (`id`, `userFrom`, `userTo`, `timeSent`, `message`, `timeMoved`, `conversation`) VALUES ( %u, %u, %u, %u, %s, UNIX_TIMESTAMP(), %u)", $message['id'], $message['userFrom'], $message['userTo'], $message['timeSent'], $message['message'], $message['conversation']);
            database::query("DELETE FROM messages WHERE id={$message['id']}");
          }
        }
      }
      else
      {
        echo '<div style="text-align:center;display:block;"><font color="red">Error: Conversation does not exist!<br /></font></div>';
        return false;
      }

      return true;
    }

    /* CHECKS */
    public function checkRoom ($p_from, $p_to)
    {
      $error = false;

      //error checks part 1 - check if there's room
      if ( self::getCapacity( self::getOtherUser($p_from)['userlevel'] ) <= self::countMessages($p_from) )
      {
        $error = true;
        echo '<div style="text-align:center;display:block;"><font color="red">Error: You have too many messages to send this! Try deleting some.<br /></font></div>';
      }

      if ( self::getCapacity( self::getOtherUser($p_to)['userlevel'] ) <= self::countMessages($p_to) )
      {
        $error = true;
        echo '<div style="text-align:center;display:block;"><font color="red">Error: The user you are trying to send this to has too many messages to send this.<br /></font></div>';
      }

      return $error;

    }




} //end class
?>
