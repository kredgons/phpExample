<?php
/**
* Kaylune Private Messaging System: Inbox page shows user all of their current conversations
* @author Kayla Parker
* Last Update: May 3 2018
*/

// requires
require($_SERVER['DOCUMENT_ROOT'].'/inc/files/core/config.php');
require($_SERVER['DOCUMENT_ROOT'].'/inc/files/core/htmlFunctions.php');
require($_SERVER['DOCUMENT_ROOT'].'/inc/classes/features/user/messages.php');

echo('<link rel="stylesheet" type="text/css" href="/inc/layout/css/day/features/messages.css" />');


$messageClass = new Messaging;
$msg = 'Send a message!';

$_GET['id'] = (int)$_GET['id'];
$convoData = $messageClass->getConversation( $_GET['id'] ); //get information for this conversation

// CHECKS ( CONVO EXISTS & USER IS NOT TRYING TO SEE SOMEONE ELSE'S CONVO )
if (!$convoData)
{
  $layout->header();
  echo 'This conversation does not exist. <a href="/features/social/messaging/">Return to your inbox.</a>';
}
elseif ($convoData['user1'] != $user['id'] && $convoData['user2'] != $user['id'])
{
  $layout->header();
  echo 'This conversation does not belong to you. <a href="/features/social/messaging/">Return to your inbox.</a>';
}
else
{
  $layout->header($convoData['name']);
  headBlock('Messaging', $convoData['name'], '<a href="/features/social/messaging/">Return to your inbox</a>');

  /* get other user's id and info  */
  if ($convoData['user1'] != $user['id']) $otherUser = $convoData['user1'];
  else $otherUser = $convoData['user2'];

  $otherUser = $messageClass->getOtherUser($otherUser);
  if (!$otherUser['username'])
  {
    echo '<font color="red"><b>There was an issue retrieving some information. Please contact an administrator.<b/></font><br /><br />';
    die();
    $layout->footer();
  }

  /* avatars */
  $oAvImg = $messageClass->getUserAvatar($otherUser['avatar']);
  $uAvImg = $messageClass->getUserAvatar($user['avatar']);

  if ( isset($_POST['new_submit']) ) // SEND MESSAGE SUBMIT
  {
    $msg = $_POST['newMessage'];

    if ( !$msg || ( strlen($msg) > 5000 ) || ( strlen($msg) < 2 ) )
    {
      $error = true;
      echo '<div style="text-align:center;display:block;"><font color="red">Error: You must enter a message between 2 and 5,000 characters.</font></div>';
    }
    else
    {
      $sentCheck = $messageClass->sendMessage($user['id'], $otherUser['id'], $msg, $convoData['id']);
      if ($sentCheck)
      {
        echo '<div style="text-align:center;display:block;"><font color="green"><b>Your message was sent!</b></font></div>';

        $notifications->create($otherUser['id'], '<a href="/features/social/messaging/conversation.php?id='.$convoData['id'].'">'.$user['username'].' has sent you a message in '.$convoData['name'].'</a>', 'mail');

      }
      else
        echo '<div style="text-align:center;display:block;"><font color="red"><b>Your message was not sent! Please try again.</b></font></div>';
    }
  }
  //time seen originally an else here - for kayla's ref

  $messages = $messageClass->getMessages($convoData['id'], $user['id']);

  // MESSAGE VIEW:
  if(!$messages)
  {
    echo 'There are no messages in this conversation. <br />
    <a href="/features/social/messaging/">Go back?</a>';
  }
  else
  {
      //if a message was just deleted:
      if (isset($_POST['messageID']))
      {
        $check = $messageClass->deleteMessage($_POST['messageID'], $user['id']);

        if ($check == false) echo '<div style="text-align:center;display:block;"><font color="red">Error: Something went wrong.</font></div>';
        else echo '<div style="text-align:center;display:block;"><font color="green"><b>The message was deleted!</b></font></div>';

      }

      //loop through and display messages:
      foreach($messages as $oneMsg)
      {
        $lastMsg = new DateTime("@{$oneMsg['timeSent']}");
        $lastMsg->setTimeZone(new DateTimeZone('EST'));

        //update time seen:
        if ($oneMsg['userTo'] == $user['id'] && !$oneMsg['timeRead'] )
          $messageClass->setSeen($convoData['id'], $user['id']);

        if ($oneMsg['timeRead']) //if user has not read, this will not be visible
        {
          $msgSeen = new DateTime("@{$oneMsg['timeRead']}");
          $msgSeen->setTimeZone(new DateTimeZone('EST'));
          $seen = '<span style="padding:1.5%"> </span> Seen on ' .$msgSeen->format('M  d, g:i A');
        }
        else
          $seen = '<span style="padding:1.5%"> </span> Not Seen';

        echo '<div id="container">
        <form action="" method="POST" id="deleteMessages" name="deleteMessages">
        <input type="hidden" name="messageID" value="'.$oneMsg['id'].'" />'; //message id for the delete + the form declaration

        if ($oneMsg['userFrom'] == $user['id']) //SENT BY CURR USER
        {
          echo<<<MESSAGE
                 <div id="totheleftbig">
                 <div id="leftokay" class="accent1bg">

                 <div id="message">{$bbCode->bbc($oneMsg['message'])}</div>
                       <div id="date">
                        <i><b>Sent on {$lastMsg->format('M  d, g:i A')}</b> {$seen} <span style="padding:3%"> </span></i>
                        <!--<a onclick="document.deleteMessages.submit();">Delete Message</a>-->
                        <input type="submit" value="Delete Messsage" name="submit" onclick="return confirm(\'Are you sure you want to delete this message?\')">
                      </div>
                 </div>
                 </div>
                 <div id="okayTwo">
                   <img src='{$uAvImg['image']}' alt="Your Avatar" title="Your Avatar" /><br />
                   <a href="profile/{$user['username']}">{$user['username']}</a>
                 </div>

MESSAGE;
        }
        elseif ($oneMsg['userFrom'] != $user['id']) //SENT BY OTHER USER
        {
          echo<<<MESSAGE
                 <div id="okayTwo">
                   <img src='{$oAvImg['image']}' alt="{$otherUser['username']}'s Avatar" title="{$otherUser['username']}'s Avatar" /><br />
                   <a href="profile/{$otherUser['username']}">{$otherUser['username']}</a>
                 </div>
                 <div id="totheright">
                 <div id="okay">

                  <div id="message">{$bbCode->bbc($oneMsg['message'])}</div>

                      <div id="date">
                       <i><b>Sent on {$lastMsg->format('M  d, g:i A')}</b> <span style="padding:3%"> </span></i>
                       <!--<a onclick="document.deleteMessages.submit();">Delete Message</a>-->
                       <input type="submit" value="Delete Messsage" name="submit" onclick="return confirm(\'Are you sure you want to delete this message?\')">
                      </div>
                 </div>
                 </div>
MESSAGE;

        }
        else
          echo '<font color=red>ERROR: Please contact an administrator.</font>';

        echo '</form>';
      } //end loop
  }
  //send a message:

  echo '
  <div id="form">
  <form action="" method="post" name="newMessage" id="newMessage">

  <textarea name="newMessage" id="newMessage" style="background-color: #f9f9f9;">
    '.$msg.'
  </textarea><br /><br />

  <span style="text-align:right;display:block;"><input type="submit" value="Submit" name="new_submit"></span>

  </form>
  </div>
  </div> </div> </div></div>';

}


$layout->footer();

?>
