<?
/*
Adding a user to a table with email activation. Here is the table that I am using (it is the same we use with the default settings):
====================== MySQL Dump ===============================
CREATE TABLE `users` (
  `userID` mediumint(8) unsigned NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `email` varchar(150) NOT NULL default '',
  `activationHash` varchar(150) NOT NULL default '',
  `active` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`userID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `activationHash` (`activationHash`),
  KEY `active` (`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
================================================================
IMPORTANT:
Do not use this example as is. Here we do not validate anything. In your application you should validate the data first, but you don't have to addslashes() as the class does this operation.
http://phpUserClass.com
http://www.webdigity.com
*/

$settings = array(
	'dbName'=>'accessuserclass',
	'dbUser'=>'root',
	'dbPass'=>''
);
require_once 'access.class.php';
$user = new flexibleAccess();

if (!empty($_GET['activate'])){
	//This is the actual activation. User got the email and clicked on the special link we gave him/her
	$hash = $user->escape($_GET['activate']);
	$res = $user->query("SELECT `{$user->tbFields['active']}` FROM `{$user->dbTable}` WHERE `activationHash` = '$hash' LIMIT 1",__LINE__);
	if ( $rec = mysql_fetch_array($res) ){
		if ( $rec[0] == 1 )
			echo 'Your account is already activated';
		else{
			//Activate the account:
			if ($user->query("UPDATE `{$user->dbTable}` SET `{$user->tbFields['active']}` = 1 WHERE `activationHash` = '$hash' LIMIT 1", __LINE__))
				echo 'Account activated. You may login now';
			else
				echo 'Unexpected error. Please contact an administrator';
		}
	}else{
		echo 'User account does not exists';
	}
}

if (!empty($_POST['username'])){
  //Register user:
  
  //Get an activation hash and mail it to the user
  $hash = $user->randomPass(100);
  while( mysql_num_rows($user->query("SELECT * FROM `{$user->dbTable}` WHERE `activationHash` = '$hash' LIMIT 1"))==1)//We need a unique hash
  	  $hash = $user->randomPass(100);
  //Adding the user. The logic is simple. We need to provide an associative array, where keys are the field names and values are the values :)
  $data = array(
  	'username' => $_POST['username'],
  	'email' => $_POST['email'],
  	'password' => $_POST['pwd'],
  	'activationHash' => $hash,
  	'active' => 0
  );
  $userID = $user->insertUser($data);//The method returns the userID of the new user or 0 if the user is not added
  if ($userID==0)
  	echo 'User not registered';//user is allready registered or something like that
  else {
  	echo 'User registered with user id '.$userID. '. Activate your account using the instructions on your mail.';
  	//Here is a sample mail that user will get:
	$email = 'Activate your user account by visiting : '. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] .'?activate='.$hash;
	mail($_POST['email'], 'Activate your account', $email);
  }
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