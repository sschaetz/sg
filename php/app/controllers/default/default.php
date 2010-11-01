<?php 

echo("default/default.php");
if($this->loggedin)
{
	echo("loggedin");
}
else
{
	echo("loggedout");
}