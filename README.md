PHP ToyCache
-
Cache made for individual sessions to store key value pairs.
Made to learn how to work with sockets with php and it's behaviour.

<strong>WARNING</strong>: This is an experiment don't use this on production environments.

How to use
-
php toycache.php

Commands:<br/>
SET KEY VALUE - Set a variable on server.<br/>
Key - Name of the remote variable.<br/>
Value - Value to store withoud spaces.

GET KEY - Get variable value on server.<br/
Key - Name of the remote variable.<br/>


Ex.:
-
nc localhost 9500<br/>
SET apiKey dHJ1c3RubzEK<br/>
GET apiKey<br/>
dHJ1c3RubzEK
