<?php
  /**
   * header.php
   * Author: Adam Beagle
   *
   * head and body header/nav, to be included in each user-facing page.
   */
  require_once dirname(dirname(__FILE__)) . '/config.php';
  //require_once dirname(__FILE__, 2) . '/config.php'; // PHP 7
  require_once "$root/inc/auth.php";

  // Only define $auth if not already defined by including file
  if (!isset($auth)) {
    $auth = new AuthHandler();
  }
  
  $logged_in = $auth->am_logged_in();

  // Fallback to defaults if including page doesn't set these
  $page = isset($page) ? $page : '';
  $page_title = isset($page_title) ? $page_title : '';
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
  <title><?= $page_title ? "$page_title | " : '' ?>WDW-Tips</title>
  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
  
  <link rel="shortcut icon" href="<?= BASEURL ?>favicon.ico" type="image/x-icon">
  <link rel="icon" href="<?= BASEURL ?>favicon.ico" type="image/x-icon">

<!--  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">-->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
  <link href="<?= STATIC_ROOT ?>css/style.min.css" rel="stylesheet">
  <link href="<?= STATIC_ROOT ?>css/jquery-ui.min.css" rel="stylesheet">
  
  <script src="<?= STATIC_ROOT ?>js/jquery-3.3.1.min.js"></script>
</head>
<body>
  <header class="top">
    <a class="site-name" href="<?= BASEURL ?>">
      <img src="<?= STATIC_ROOT ?>images/tips.png">
      <span class="logo">
        WDW-Tips
      </span>
    </a>
    
    <nav>
      <ul>
<?php
  if ($logged_in) {
    $uname = $auth->get_user();
?>
        <li class="user">
          <a class="user" href="<?= BASEURL ?>user/?id=<?= $uname ?>"><?= $uname ?></a>
          <a class="avatar" href="<?= BASEURL ?>user/?id=<?= $uname ?>">
<?php
    if (file_exists("$root/static/images/avatars/$uname.jpg")) {
?>
            <img src="<?= STATIC_ROOT ?>images/avatars/<?= $uname ?>.jpg">
<?php
    } else {
?>
            <img src="<?= STATIC_ROOT ?>images/avatars/default.jpg">
<?php
    }
?>
          </a>
        </li>
        <li>
          <a href="<?= BASEURL ?>logout/" class="logout">[Logout]</a>
        </li>
<?php
  } else {
?>
        <li>
          <a class="btn login" href="<?= BASEURL ?>login/">Log In</a>
        </li>
<?php
  }
?>
      </ul>
    </nav>
  </header>
  <nav class="topics">
    <ul>
      <li class="all"><a title="All Topics" href="<?= BASEURL ?>topics/">Topics</a></li>
      <li class="secondary"><a href="<?= BASEURL ?>topics/?topic=planning">Planning</a></li>
      <li class="secondary"><a href="<?= BASEURL ?>topics/?topic=parks">Parks</a></li>
      <li class="secondary hide-sm"><a href="<?= BASEURL ?>topics/?topic=resorts">Resorts</a></li>
      <li class="secondary"><a href="<?= BASEURL ?>topics/">More...</a></li>
    </ul>
  </nav>
  <div class="content-wrapper<?= $page ? " $page" : ''?>">
