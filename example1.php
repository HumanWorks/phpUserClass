<?
/*
Basic login example with php user class
http://phpUserClass.com
*/
require_once 'access.class.php';
$user = new flexibleAccess();
if ( $_GET['logout'] == 1 ) 
	$user->logout('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
if ( !$user->is_loaded() )
{
	//Login stuff:
	if ( isset($_POST['uname']) && isset($_POST['pwd'])){
	  if ( !$user->login($_POST['uname'],$_POST['pwd'],$_POST['remember'] )){//Mention that we don't have to use addslashes as the class do the job
	    echo 'Wrong username and/or password';
	  }else{
	    //user is now loaded
	    header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	  }
	} ?>
	<h1>Login</h1>
	<p><form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" />
	  username: <input type="text" name="uname" /><br /><br />
  	  password: <input type="password" name="pwd" /><br /><br />
	  Remember me? <input type="checkbox" name="remember" value="1" /><br /><br />
	  <input type="submit" value="login" />
	</form>
	</p>
	<?php
}else{
  //User is loaded
  ?>
  <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>'?logout=1">logout</a>
  <?php
}
?>
