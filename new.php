<?php
/**
* Kaylune Private Messaging System: Start a new conversation with somebody
* @author Kayla Parker
* Last Update: May 15 2018
*/

// requires
require($_SERVER['DOCUMENT_ROOT'].'/inc/files/core/config.php');
require($_SERVER['DOCUMENT_ROOT'].'/inc/files/core/htmlFunctions.php');
require($_SERVER['DOCUMENT_ROOT'].'/inc/classes/features/user/messages.php');

?><?php

$layout->header('Private Messaging');
headBlock('Messaging', 'New Conversation', '<a href="/features/social/messaging/">Return to your inbox</a>');
$messageClass = new Messaging;
echo('<link rel="stylesheet" type="text/css" href="/inc/layout/css/day/features/messages.css" />');

$recipient = 'Username';
$convoName = 'Conversation';
$msg = 'Send a message!';


if (isset($_POST['submit'])) //if user has submitted the form
{

  //get vars
  $recipient = (string)($_POST['username']);
  $convoName = $_POST['subject'];
  $msg = $_POST['newMessage'];

  //strip($recipient);
  $recipientData = $messageClass->getUserByUsername($recipient);

  if (!$recipientData)
    echo '<div style="text-align:center;display:block;"><font color="red">Error: The user '.$recipient.' does not exist!</font></div>';
  else
  {
    if ( !$msg || ( strlen($msg) > 5000 ) || ( strlen($msg) < 2 ) )
    {
      $error = true;
      echo '<div style="text-align:center;display:block;"><font color="red">Error: You must enter a message between 2 and 5,000 characters.</font></div>';
    }
    else
    {
        $newConvo = $messageClass->newConversation($convoName, $user['id'], $recipientData['id']);

        if ($newConvo == -1)
          echo '<div style="text-align:center;display:block;"><font color="red">Error: An unclear error occurred. Please contact an administrator. <br /></font></div>';
        else
        {
            $sentCheck = $messageClass->sendMessage($user['id'], $recipientData['id'], $msg, $newConvo['id']);

            if ($sentCheck)
            {
              $notifications->create($recipientData['id'], '<a href="/features/social/messaging/conversation.php?id='.$newConvo['id'].'">'.$user['username'].' has started a new conversation with you!</a>', 'mail');

              echo '<div style="text-align:center;display:block;"><font color="green">
              <b>The conversation was created and your message was sent! Click <a href="/features/social/messaging/conversation.php?id='.$newConvo['id'].'">here</a> to view it.</b></font></div>';


            }

            else echo '<div style="text-align:center;display:block;"><font color="red">
            <b>Your message was not sent! Please try again.</b></font></div>';
        }
    }
  }
}

$friends = $messageClass->getFriends($user['id']);

if ( isset( $_GET['user'] ) )
{
  $recipient = $_GET['user'];
}

echo '
<div id="contain" style="text-align:center; margin:0 auto; width: 60%;">

        <form action="" method="post" name="newConversation" id="newConversation">

        Who are you sending your message to?<br />
        <select id="friends">
          <option disabled selected value> -- select a friend -- </option>';

                foreach ($friends as $friend) //loop through users' friends
                {
                  if ($friend['fromID'] == $user['id']) $friendData = $messageClass->getOtherUser($friend['toID']);
                  else $friendData = $messageClass->getOtherUser($friend['fromID']);
                  echo '<option>'.$friendData['username'].'</option>';
                }


        echo '</select> <input style="text" id="recipient" name="username" value='.$recipient.' required>
        <br /><br />
        Conversation name: <input style="text" name="subject" value='.$convoName.' required> </input><br /><br />

        <textarea name="newMessage" id="newMessage" style="background-color: #f9f9f9; min-height: 100px;" required>
          '.$msg.'
        </textarea><br /><br />

        <span style="text-align:right;display:block;"><input type="submit" value="Submit" name="submit"></span>

        </form>
</div>';?>

<script>
  var dropdown = document.getElementById('friends');

  dropdown.onchange = function(){
       var textbox = document.getElementById('recipient');
       textbox.value = this.value;
       console.log(this.value);
  }
</script>

<?php
$layout->footer();
