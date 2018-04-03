<?php
 /**
  * logout/index.php
  * Author: Adam Beagle
  *
  * Log out user and redirect (always) to landing page
  */
  require_once '../inc/config.php';
  require_once "$root/inc/auth.php";

  $auth = new AuthHandler();

  if ($auth->am_logged_in()) {
    $auth->logout();
  }

  header("Location: " . BASEURL . '?logout');
?>