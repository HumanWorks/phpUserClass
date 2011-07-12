<?
/*
Adding a user to a table. Here is the table that I am using (it is the same we use with the default settings):
====================== MySQL Dump ===============================
CREATE TABLE `users` (
  `userID` mediumint(8) unsigned NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `email` varchar(150) NOT NULL default '',
  `active` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`userID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `active` (`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
================================================================
In this example we will automatically activate the user
IMPORTANT:
Do not use this example as is. Here we do not validate anything. In your application you should validate the data first, but you don't have to addslashes() as the class does this operation.
http://phpUserClass.com
*/

if (!empty($_POST['username'])){
  //Register user:
  require_once 'access.class.php';
  $user = new flexibleAccess();
  //The logic is simple. We need to provide an associative array, where keys are the field names and values are the values :)
  $data = array(
  	'username' => $_POST['username'],
  	'email' => $_POST['email'],
  	'password' => $_POST['pwd'],
  	'active' => 1
  );
  $userID = $user->insertUser($data);//The method returns the userID of the new user or 0 if the user is not added
  if ($userID==0)
  	echo 'User not registered';//user is allready registered or something like that
  else
  	echo 'User registered with user id '.$userID;
}


echo '<h1>Register</h1>
	<p><form method="post" action="'.$_SERVER['PHP_SELF'].'" />
	 username: <input type="text" name="username" /><br /><br />
	 password: <input type="password" name="pwd" /><br /><br />
	 email: <input type="text" name="email" /><br /><br />
	 <input type="submit" value="Register user" />
	</form>
	</p>';

?>