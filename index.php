<?php
/**
* Kaylune Private Messaging System: Inbox page shows user all of their current conversations
* @author Kayla Parker
* Last Update: May 15 2018
*/

// requires
require($_SERVER['DOCUMENT_ROOT'].'/inc/files/core/config.php');
require($_SERVER['DOCUMENT_ROOT'].'/inc/files/core/htmlFunctions.php');
require($_SERVER['DOCUMENT_ROOT'].'/inc/classes/features/user/messages.php');
echo('<link rel="stylesheet" type="text/css" href="/inc/layout/css/day/features/messages.css" />');?>

<script>
function checkAll(source) {
  checkboxes = document.getElementsByName('delete[]');
  for(var i=0, n=checkboxes.length;i<n;i++)
  {
    checkboxes[i].checked = source.checked;
  }
}
</script>
<?php

$layout->header('Private Messaging');
headBlock('Messaging', 'Inbox', '');
$messageClass = new Messaging;

echo '
<style>
  table {
    width: 100%;
    border-collapse: collapse;
  }
  table td {
    padding: 1em;
    vertical-align: middle;
  }
</style>';

//initialize some stuff:
$convos = $messageClass->getConversations($user['id']);
$numMessages = $messageClass->countMessages($user['id']);
$capacity = $messageClass->getCapacity($user['userlevel']);

if ( isset($_POST['selectDelete']) )
{

  if ( !isset($_POST['delete']) )
    echo '<div style="text-align:center;display:block;"><font color="red">Error: You cannot delete 0 conversations! <br /></font></div>';
  else
  {
    $count=0;
    foreach($_POST['delete'] as $selected)
    {
      $count+=1;
      $check = $messageClass->deleteConversation($selected, $user['id']);
      if ($check == false) break;
    }

    if ($check == false) echo '<div style="text-align:center;display:block;"><font color="red">Error: Something went wrong.</font></div>';
    else echo '<div style="text-align:center;display:block;"><font color="green"><b>'.$count.' conversations and their messages were deleted!</b></font></div>';
  }
}

echo<<<VIEW
<div id="event">
	<div class="leftCol" style="text-align:center;">
      <img src="/images/npc/mailnpc.png" alt="Norton" title="Norton"><br />
      <a href="/features/site/npc/interaction.php?id=21"><img src="/images/interaction/interact.png" title="Interact" alt="Interact Button"></a><br /><br />

      What? It seems like... Norton has something to say?! (Text for norton here if any?)<br />
      <br />
      <b>Capacity:</b> $numMessages / $capacity <br />

VIEW;

	echo '</div><div class="rightCol">
    <a href="new.php"><button>Start New Conversation</button></a>


    <h3>Conversations</h3>';
    if ($convos)
    {
      echo '<form action="" method="post" name="conversations" id="conversations">
      <label><input type="checkbox" onClick="checkAll(this)"> Select All</label>
      <input type="submit" value="Delete Selected" name="selectDelete" onclick="return confirm(\'Are you sure you want to delete all the selected messages and conversations?\')">
      <div id="conversationList">
      <table class="bandedTable">';

        foreach($convos as $convo)
        {
          //assumes there are only two possible cases at this point: playing user is user1 or user2 , impossible for it to be neither
          if ($convo['user1'] != $user['id']) $otherUser = $convo['user1'];
          else $otherUser = $convo['user2'];

          //get user's info, kill if they don't exist, and determine if mod
          $otherUser = $messageClass->getOtherUser($otherUser);

          if (!$otherUser['username'])
          {
            echo '<font color="red"><b>There was an issue retrieving some information. Please contact an administrator.</b></font><br /><br />';
            $layout->footer();
            die();
          }

          if ($otherUser['userlevel'] == 2) $mod = ' [MOD] ';
          else $mod='';

          //timestamp 
          $lastMsg = new DateTime("@{$convo['lastUpdated']}");
          $lastMsg->setTimeZone(new DateTimeZone('EST'));

          //avatar
          $av = $messageClass->getUserAvatar($otherUser['avatar']);

          echo '<!--Single Message-->
          <tr>
            <label><td><input type="checkbox" value="'.$convo['id'].'" name="delete[]"></td> </label>
            <td><img src="'.$av['image'].'" alt="'.$otherUser['username'].'\'s Avatar" title="'.$otherUser['username'].'\'s Avatar"></td>
            <td><a href="conversation.php?id='.$convo['id'].'"><b>'.$convo['name'].'</b></a><br />
            With <a href="">'.$otherUser['username'].$mod.'</a><br />
            Last Message at '.$lastMsg->format('M  d, g:i A').'</br >
          </tr>';
        }

        echo '</form></table></div>';
    }
    else
    {
      echo '<div style="text-align:center;">Hm, it seems you don\'t have any conversations, '.$user['username'].'! <br />
      Why not start one? Click <a href="#">here</a>!</div>';
    }

  //final closing tags:
echo'
	</div>
</div>';

$layout->footer();
 ?>
