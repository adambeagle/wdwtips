<?php
  /**
  * user/index.php
  * Author: Adam Beagle
  *
  * Displays user data, and tips authored by user.
  */
  require_once '../inc/config.php';
  require_once "$root/inc/auth.php";
  require_once "$root/inc/db.php";
  require_once "$root/inc/util.php";

  $db = new WDWTipsDB();
  $auth = new AuthHandler();

  $id_req = get_from_request('id');
  $sort_req = get_from_request('sort', 'best');

  // Try to get user if id supplied
  if (isset($id_req)) {
    $user = $db->get_user($id_req);
    
    // Check if user exists
    if (is_null($user)) {
      $err_msg = "User '$id_req' not found.";
    }
    else {
      // Get user success; get tips and other data
      $tips = $db->iter_tips_by_user($user['uname'], $sort_req, $auth->get_user());
      $tip_count = $db->get_user_tip_count($user['uname']);
      $user_total_score = $db->calculate_user_score($user['uname']);
    }
  }
  else {
    $err_msg = 'Username not provided.';
  }

  $page = 'user';
  $page_title = 'User' . isset($err_msg) ? '' : ": $user[uname]";
  require "$root/inc/partials/header.php";
?>
    <main>
<?php
  if (isset($err_msg)) {
?>
      <p class="err"><strong>Error: <?= $err_msg ?></strong></p>
<?php
  }
  else {
    $disable_author = true;
    include "$root/inc/partials/tiplist.php";
  }
?>
      
    </main>
<?php
  if (isset($user)) {
?>
    <aside>
      

      <div class="avatar">
<?php
    if (file_exists("$root/static/images/avatars/$user[uname].jpg")) {
?>
        <img src="<?= STATIC_ROOT ?>images/avatars/<?= $user['uname'] ?>.jpg">
<?php
    }
    else {
?>
        <img src="<?= STATIC_ROOT ?>images/avatars/default.jpg">
<?php
    }
?>
      </div>
      
      <h1>
        <?= $user['uname'] ?>
      </h1>
      
      <section class="stats">
        <table>
<?php
  if (isset($tip_count)) {
?>
          <tr>
            <th>Tips</th>
            <td><?= $tip_count ?></td>
          </tr>
<?php
    }
    
    if (isset($user_total_score)) {
?>
          <tr>
            <th>Points</th>
            <td><?= $user_total_score ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <th>Joined</th>
            <td><?= $user['joined'] ?></td>
          </tr>
        </table>
      </section>
    </aside>
<?php
  }
?>
<?php
  require "$root/inc/partials/footer.php";
?>