<?php
$successURL = 'index.php';//Where the user can go after a successful login
if ( $_GET['form'] == 1 ){
    if(isset($_POST['nameuser'])){
        require_once 'access.class.php';
        $user = new flexibleAccess();
	
        if ( $user->login( $_POST['nameuser'], $_POST['passuser'], true ))
            echo '{'.
                    'succes: true,'.
                    'title: \'<strong>Login Success</strong>\', '.
                    'content: \'You have authenticated successfuly<br />'.
                    'click <a href="'.$successURL.'">here</a> to continue\''.
                '}';
        else
            echo '{'.
                    'succes: false,'.
                    'title: \'<strong>Login Failed : User and Password combination is not valid</strong>\''.
                '}';
    }else{
        echo '<form name="ajaxform" id="ajaxform">
                <label>Username</label>
                <input type="text" name="nameuser" id="nameuser" class="textfield" value="'.$_POST['nameuser'].'" />
                <label>Password</label>
                <input type="password" name="passuser" id="passuser" class="textfield" />
                <input type="submit" name="submit" id="submit" class="buttonfield" value="Login" />
                </form>';
    }
    exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ajax PHP Login with jquery</title>
<meta name="robots" content="noindex,nofollow" />
<link href="js/login.css" rel="stylesheet" media="all" type="text/css" />
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="js/form.js"></script>
<script language="javascript" type="text/javascript" src="js/login.js"></script>
</head>
<body>
	<div id="err"></div>
	<div id="wrapper"></div>
    <div id="footer">
    original code by 
    <a href="http://www.chazzuka.com/" title="web design service by freelance web designer">bali web design</a>    </div>
</body>
</html>
