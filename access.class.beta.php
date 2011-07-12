<?php
/**
 * PHP Class to user access (login, register, logout, etc)
 * 
 * <code><?php
 * include('access.class.php');
 * $user = new flexibleAccess();
 * ? ></code>
 * 
 * For support issues please refer to the webdigity forums :
 *				http://www.webdigity.com/index.php/board,91.0.html
 * or the official web site:
 *				http://phpUserClass.com/
 * also check and contribute to our gitHub repo:
 *				https://github.com/HumanWorks/phpUserClass
 * ==============================================================================
 * 
 * @version $Id: access.class.php,v 1.00 2009/11/18 10:54:32 $
 * @copyright Copyright (c) 2007-2011 Nick Papanotas (http://www.webdigity.com)
 * @author Nick Papanotas <nikolas@webdigity.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * 
 * ==============================================================================
 * Changelog for this version:
 * . class rewritten in php 5 (it was about time :))
 * . various minor bugs fixed
 * . added the updateProperty() method for updating user data (written by Downlord@webdigity, updated by Nick)
 * . added the DEVELOPMENT_MODE property, which is important for the project's development
 */

/**
 * Flexible Access - The main class
 * 
 * @param string $dbName
 * @param string $dbHost 
 * @param string $dbUser
 * @param string $dbPass
 * @param string $dbTable
 */

class flexibleAccess{
  /*Settings*/
  /**
   * The database that we will use
   * var string
   */
  private $dbName = 'database';
  /**
   * The database host
   * var string
   */
  private $dbHost = 'localhost';
  /**
   * The database port
   * var int
   */
  private $dbPort = 3306;
  /**
   * The database user
   * var string
   */
  private $dbUser = 'user';
  /**
   * The database password
   * var string
   */
  private $dbPass = 'password';
  /**
   * The database table that holds all the information
   * var string
   */
  private $dbTable  = 'users';
  /**
   * The session variable ($_SESSION[$sessionVariable]) which will hold the data while the user is logged on
   * var string
   */
  private $sessionVariable = 'userSessionValue';
  /**
   * Those are the fields that our table uses in order to fetch the needed data. The structure is 'fieldType' => 'fieldName'
   * var array
   */
  private $tbFields = array(
  	'userID'=> 'userID', 
  	'login' => 'username',
  	'pass'  => 'password',
  	'email' => 'email',
  	'active'=> 'active'
  );
	/**
   * When user wants the system to remember him/her, how much time to keep the cookie? (seconds)
   * var int
   */
  private $remTime = 2592000;//One month
  /**
   * The name of the cookie which we will use if user wants to be remembered by the system
   * var string
   */
  private $remCookieName = 'ckSavePass';
  /**
   * The cookie domain
   * var string
   */
  private $remCookieDomain = '';
  /**
   * The method used to encrypt the password. It can be sha1, md5 or nothing (no encryption)
   * var string
   */
  private $passMethod = 'sha1';
  /**
   * Display errors? Set this to true if you are going to seek for help, or have troubles with the script
   * var bool
   */
  private $displayErrors = true;
  /*Do not edit after this line*/
  private $userID;
  private $dbConn;
  private $userData=array();
  public $DEVELOPMENT_MODE = false;
  /**
   * Class Constructure
   * 
   * @param string $dbConn
   * @param array $settings
   * @return void
   */
  public function flexibleAccess($dbConn = '', $settings = '') {
	    if ( is_array($settings) ){
		    foreach ( $settings as $k => $v ){
				    if ( !isset( $this->{$k} ) ) die('Property '.$k.' does not exists. Check your settings.');
				    $this->{$k} = $v;
			}
	    }
	    $this->remCookieDomain = $this->remCookieDomain == '' ? $_SERVER['HTTP_HOST'] : $this->remCookieDomain;
	    $this->dbConn = ($dbConn=='')? mysql_connect($this->dbHost.':'.$this->dbPort, $this->dbUser, $this->dbPass):$dbConn;
	    if ( !$this->dbConn ) die(mysql_error($this->dbConn));
	    mysql_select_db($this->dbName, $this->dbConn)or die(mysql_error($this->dbConn));
	    if( !isset( $_SESSION ) ) session_start();
	    if ( !empty($_SESSION[$this->sessionVariable]) )
		    $this->loadUser( $_SESSION[$this->sessionVariable] );
	    //Maybe there is a cookie?
	    if ( isset($_COOKIE[$this->remCookieName]) && !$this->is_loaded()) {
	      //echo 'I know you<br />';
	      $u = unserialize(base64_decode($_COOKIE[$this->remCookieName]));
	      $this->login($u['uname'], $u['password']);
	    }
  }
  
  /**
  	* Login function
  	* @param string $uname
  	* @param string $password
  	* @param bool $loadUser
  	* @return bool
  */
  public function login($uname, $password, $remember = false, $loadUser = true) {
    	$uname    = $this->escape($uname);
    	$password = $originalPassword = $this->escape($password);
		switch(strtolower($this->passMethod)){
		  case 'sha1':
		  	$password = "SHA1('$password')"; break;
		  case 'md5' :
		  	$password = "MD5('$password')";break;
		  case 'nothing':
		  	$password = "'$password'";
		}
		$res = $this->query("SELECT * FROM `{$this->dbTable}` 
		WHERE `{$this->tbFields['login']}` = '$uname' AND `{$this->tbFields['pass']}` = $password LIMIT 1",__LINE__);
		if ( @mysql_num_rows($res) == 0)
			return false;
		if ( $loadUser ) {
			$this->userData = mysql_fetch_array($res);
			$this->userID = $this->userData[$this->tbFields['userID']];
			$_SESSION[$this->sessionVariable] = $this->userID;
			if ( $remember ){
			  $cookie = base64_encode(serialize(array('uname'=>$uname,'password'=>$originalPassword)));
			  $a = setcookie($this->remCookieName, 
			  $cookie,time()+$this->remTime, '/', $this->remCookieDomain);
			}
		}
		return true;
  }
  
  /**
  	* Logout function
  	* param string $redirectTo
  	* @return bool
  */
  public function logout($redirectTo = '') {
    setcookie($this->remCookieName, '', time()-3600);
    $_SESSION[$this->sessionVariable] = '';
    $this->userData = '';
    if ( $redirectTo != '' && !headers_sent()){
	   header('Location: '.$redirectTo );
	   exit;//To ensure security
	}
  }
  /**
  	* Function to determine if a property is true or false
  	* param string $prop
  	* @return bool
  */
  public function is($prop){
  	return $this->get_property($prop)==1?true:false;
  }
  
    /**
  	* Get a property of a user. You should give here the name of the field that you seek from the user table
  	* @param string $property
  	* @return string
  */
  public function get_property($property) {
    if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
    if (!isset($this->userData[$property])) $this->error('Unknown property <b>'.$property.'</b>', __LINE__);
    return $this->userData[$property];
  }
  /**
  	* Is the user an active user?
  	* @return bool
  */
  public function is_active() {
    return $this->userData[$this->tbFields['active']];
  }
  
  /**
   * Is the user loaded?
   * @ return bool
   */
  public function is_loaded() {
    return empty($this->userID) ? false : true;
  }
  /**
  	* Activates the user account
  	* @return bool
  */
  public function activate() {
    if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
    if ( $this->is_active()) $this->error('Allready active account', __LINE__);
    $res = $this->query("UPDATE `{$this->dbTable}` SET {$this->tbFields['active']} = 1 
	WHERE `{$this->tbFields['userID']}` = '".$this->escape($this->userID)."' LIMIT 1");
    if (@mysql_affected_rows() == 1) {
		$this->userData[$this->tbFields['active']] = true;
		return true;
	}
	return false;
  }
  /*
   * Creates a user account. The array should have the form 'database field' => 'value'
   * @param array $data
   * return int
   */  
  public function insertUser($data){
    if (!is_array($data)) $this->error('Data is not an array', __LINE__);
	$data[$this->tbFields['pass']] = $this->encode_password($data[$this->tbFields['pass']]);
    foreach ($data as $k => $v ) $data[$k] = "'".$this->escape($v)."'";
    $this->query("INSERT INTO `{$this->dbTable}` (`".implode('`, `', array_keys($data))."`) VALUES (".implode(", ", $data).")");
    return (int)mysql_insert_id($this->dbConn);
  }
  
  /*
   * Updates a property. Data must be in the form 'property' => 'value'
   * @author Downlord@webdigity (http://www.webdigity.com/index.php?action=profile;u=3606)
   * @param array
   * @return bool
   */
  public function updateProperty($properties) {
    if(is_array($properties) && count($properties) > 0) {
      $i=1;
      $query = "UPDATE `".$this->dbTable."` SET ";
      $c = count($properties);//a small optimization :)
      foreach($properties AS $k => $v) {
        $v = ($k == $tbFields['pass']) ? $this->encode_password($v) : $v;
        $query .= '`'.$this->escape($k)."` = '".$this->escape($v)."'".(($i++ < $c) ? ', ' : ' ');
      }
      $query .= "WHERE `".$this->tbFields['userID']."` = '".$this->userID."'";
      return mysql_query($query, $this->dbConn); 
    }
    return $this->error('$properties should be a non empty array', __LINE__);
  }
  
  /*
   * Creates a random password. You can use it to create a password or a hash for user activation
   * param int $length
   * param string $chrs
   * return string
   */
  public function randomPass($length=10, $chrs = '1234567890qwertyuiopasdfghjklzxcvbnm'){
    for($i = 0; $i < $length; $i++) {
        $pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
    }
    return $pwd;
  }
  ////////////////////////////////////////////
  // PRIVATE FUNCTIONS
  ////////////////////////////////////////////
  
  /**
  	* SQL query function
  	* @access private
  	* @param string $sql
  	* @return string
  */
  private function query($sql, $line = 'Uknown') {
    if ($this->$DEVELOPMENT_MODE ) echo '<b>Query to execute: </b>'.$sql.'<br /><b>Line: </b>'.$line.'<br />';
	$res = mysql_query($sql, $this->dbConn);
	if ( !$res )
		$this->error(mysql_error($this->dbConn), $line);
	return $res;
  }
  
  /**
  	* A function that is used to load one user's data
  	* @access private
  	* @param string $userID
  	* @return bool
  */
  private function loadUser($userID) {
	$res = $this->query("SELECT * FROM `{$this->dbTable}` WHERE `{$this->tbFields['userID']}` = '".$this->escape($userID)."' LIMIT 1");
    if ( mysql_num_rows($res) == 0 )
    	return false;
    $this->userData = mysql_fetch_array($res);
    $this->userID = $userID;
    $_SESSION[$this->sessionVariable] = $this->userID;
    return true;
  }

  /**
  	* Produces the result of addslashes() with more safety
  	* @access private
  	* @param string $str
  	* @return string
  */  
  private function escape($str) {
    $str = get_magic_quotes_gpc()?stripslashes($str):$str;
    $str = mysql_real_escape_string($str, $this->dbConn);
    return $str;
  }
  
  /**
  	* Error holder for the class
  	* @access private
  	* @param string $error
  	* @param int $line
  	* @param bool $die
  	* @return bool
  */  
  private function error($error, $line = '', $die = false) {
    if ( $this->displayErrors )
    	echo '<b>Error: </b>'.$error.'<br /><b>Line: </b>'.($line==''?'Unknown':$line).'<br />';
    if ($die) exit;
    return false;
  }
  
  private function encode_password($pass){
  	switch(strtolower($this->passMethod)){
	  case 'sha1':
	  	return "SHA1('".$pass."')";
	  case 'md5' :
	  	return "MD5('".$pass."')";
	  case 'nothing':
	  	return $pass;
	  case 'default':
	    return $this->error('Unknown password encoding method');
	}
  }
}
?>
