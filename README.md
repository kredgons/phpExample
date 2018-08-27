THIS WILL NOT RUN BY ITSELF, you can see this code by creating an account on kaylune.com and going to this link: http://www.kaylune.com/features/social/messaging/ , or I can show you in person / via screenshare 

This is a feature I designed for the site Kaylune.com in PHP. The feature is meant to be a direct messaging system involving only two users. 

There are 5 files not including this one:

conversation.php - Where a user can view their conversation, the code determines if this is the user who "sent" particular messages, or if the user is the "other user', i.e., the recipient 

index.php - The homepage, listing all of a user's conversations 

messages.css - the css for all involved pages

messages.php - the class that is used in all the pages (declared as: $messageClass = new Messaging;) 

new.php - a simple page allowing a user to start a new conversation 
