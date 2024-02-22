### Update local env after pull:
settings.php

###
Set email appserver

In app/models/Email.php:

$this->mail->Host = {email server domain};
$this->mail->Port = {email server port};