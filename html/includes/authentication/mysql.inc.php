<?php

function authenticate($username,$password)
{
  $encrypted_old = md5($password);
  $sql = "SELECT username,password FROM `users` WHERE `username`='".$username."'";
  $query = mysql_query($sql);
  $row = @mysql_fetch_assoc($query);
  if ($row['username'] && $row['username'] == $username)
  {
    // Migrate from old, unhashed password
    if ($row['password'] == $encrypted_old)
    {
      $query = mysql_query("DESCRIBE users password");
      $row = mysql_fetch_assoc($query);
      if ($row['Type'] == 'varchar(34)')
      {
        changepassword($username,$password);
      }
      return 1;
    }
    if ($row['password'] == crypt($password,$row['password']))
    {
      return 1;
    }
  }
  return 0;
}

function passwordscanchange($username="")
{
  
  
  /*
   * By default allow the password to be modified, unless the existing
   * user is explicitly prohibited to do so.
   */
  
  if (empty($username) || !user_exists($username)) {
    return 1;
  } else {
    return @mysql_result(mysql_query("SELECT can_modify_passwd FROM users WHERE username = '".mres($username)."'"),0);
  }
}

/**
 * From: http://code.activestate.com/recipes/576894-generate-a-salt/
 * This function generates a password salt as a string of x (default = 15) characters
 * ranging from a-zA-Z0-9.
 * @param $max integer The number of characters in the string
 * @author AfroSoft <scripts@afrosoft.co.cc>
 */
function generateSalt($max = 15)
{
  $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $i = 0;
  $salt = "";
  do
  {
    $salt .= $characterList{mt_rand(0,strlen($characterList))};
    $i++;
  } while ($i <= $max);

  return $salt;
}

function changepassword($username,$password)
{
  $encrypted = crypt($password,'$1$' . generateSalt(8).'$');
  $sql = "UPDATE `users` SET `password` = '$encrypted' WHERE `username`='".$username."'";
  $query = mysql_query($sql);
}

function auth_usermanagement()
{
  return 1;
}

function adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd='1')
{
  if (!user_exists($username))
  {
    $encrypted = crypt($password,'$1$' . generateSalt(8).'$');
    mysql_query("INSERT INTO `users` (`username`,`password`,`level`, `email`, `realname`, `can_modify_passwd`) VALUES ('".mres($username)."','".mres($encrypted)."','".mres($level)."','".mres($email)."','".mres($realname)."','".mres($can_modify_passwd)."')");
  }

  return mysql_affected_rows();
}

function user_exists($username)
{
  return @mysql_result(mysql_query("SELECT * FROM users WHERE username = '".mres($username)."'"),0);
}

function get_userlevel($username)
{
  $sql = "SELECT level FROM `users` WHERE `username`='".mres($username)."'";
  $row = mysql_fetch_assoc(mysql_query($sql));
  return $row['level'];
}

function get_userid($username)
{
  $sql = "SELECT user_id FROM `users` WHERE `username`='".mres($username)."'";
  $row = mysql_fetch_assoc(mysql_query($sql));
  return $row['user_id'];
}

function deluser($username)
{
  mysql_query("DELETE FROM `users` WHERE `user_id` = '" . mres($_GET['user_id']) . "'");
  return mysql_affected_rows();
}

?>
