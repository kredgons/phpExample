{\rtf1\ansi\ansicpg1252\cocoartf1561\cocoasubrtf400
{\fonttbl\f0\fswiss\fcharset0 Helvetica;}
{\colortbl;\red255\green255\blue255;}
{\*\expandedcolortbl;;}
\margl1440\margr1440\vieww10800\viewh8400\viewkind0
\pard\tx720\tx1440\tx2160\tx2880\tx3600\tx4320\tx5040\tx5760\tx6480\tx7200\tx7920\tx8640\pardirnatural\partightenfactor0

\f0\fs24 \cf0 THIS WILL NOT RUN BY ITSELF, you can see this code by creating an account on kaylune.com and going to this link: http://www.kaylune.com/features/social/messaging/ , or I can show you in person / via screenshare \
\
This is a feature I designed for the site Kaylune.com in PHP. The feature is meant to be a direct messaging system involving only two users.\
\
There are 5 files not including this one:\
\

\b conversation.php
\b0  - Where a user can view their conversation, the code determines if this is the user who \'93sent\'94 particular messages, or if the user is the \'93other user\'94, i.e., the recipient \
\

\b index.php
\b0  - The homepage, listing all of a user\'92s conversations \
\

\b messages.css
\b0  - the css for all involved pages\
\

\b messages.php 
\b0 - the class that is used in all the pages (declared as: $messageClass = new Messaging;) \
\

\b new.php
\b0  - a simple page allowing a user to start a new conversation }