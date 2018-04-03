<?php
  /**
  * tag/index.php
  * Author: Adam Beagle
  *
  * Delete tip defined by param 'id', if user has permission.
  * No warnings or 
  */

  require_once '../../inc/config.php';
  require_once "$root/inc/auth.php";
  require_once "$root/inc/db.php";
  require_once "$root/inc/util.php";

  $db = new WDWTipsDB();
  $auth = new AuthHandler();

  $id_req = get_from_request('id');

  // Default error. If delete happens, user will be redirected and never
  // see this message. It should be reset below for any expected error.
  $err_msg = 'An unknown error occurred.';

  // Validate deletion. Set error message appropriately if invalid.
  if (!$auth->am_logged_in()) {
    $err_msg = 'You must be logged in to delete a tip.';
  }
  else if (!isset($id_req)) {
    $err_msg = 'No tip specified.';
  }
  else {
    $owned = $db->user_owns_tip($id_req, $auth->get_user());
    
    if ($owned) {
      // Do delete
      if ($db->delete_tip($id_req)) {
        header('Location: ' . BASEURL . '?del_tip');
      }
      else {
        $err_msg = 'A database error occurred.';
      }
    }
    else if (is_null($owned)) {
      // if $owned is null (as opposed to false), a db error occurred
      $err_msg = 'A database error occurred.';
    }
    else {
      // False. Tip not owned
      $err_msg = 'You do not have permission to delete this tip.';
    }
  }

  $page = 'delete';
  $page_title = 'Delete Tip';
  require "$root/inc/partials/header.php";
?>
    <main>
<?php
  if (isset($err_msg)) {
?>
      <p class="err"><strong>Error: <?= $err_msg ?></strong></p>
<?php
  }
?>
    </main>
<?php
  require "$root/inc/partials/footer.php";
?>