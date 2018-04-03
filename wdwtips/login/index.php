<?php
  /**
  * login/index.php
  * Author: Adam Beagle
  *
  * If not logged in, show login form.
  * If login form data sent, attempt login and redirect to landing page on success.
  */
  require_once '../inc/config.php';
  require_once "$root/inc/auth.php";
  require_once "$root/inc/util.php";
  
  $auth = new AuthHandler();

  // Uncomment for testing
  // $auth->logout();

  $user_req = get_from_request('username');
  $pw_req = get_from_request('password');

  // Attempt login if requested
  if (form_submitted() && $user_req) {
    if (!$auth->am_logged_in()) {
      $login_result = $auth->login($user_req, $pw_req);
    }
  }

  // Redirect to landing page if logged in
  // Note this happens if page is visited when already logged in,
  // not only on form submit
  if ($auth->am_logged_in()) {
    header('Location: ' . BASEURL);
  }

  $page = 'login';
  $page_title = 'Login';
  require "$root/inc/partials/header.php";
?>
    <main>
<?php
  // Show login form if not logged in
  if (!$logged_in) {
?>
      <h1>Log In</h1>
      
      <p>Don't have an account? <a href="<?= BASEURL ?>create-account/">Click here</a> to create one.</p>
      
      <form class="login" method="post">
        <div class="form-group">
          <label for="username">Username</label>
          <input name="username" type="text" value="<?= isset($user_req) ? $user_req : '' ?>" autofocus>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input name="password" type="password">
        </div>
        
        <div class="form-group">
          <button type="submit" name="submit">Go</button>
<?php
    // Show error message if login attempt failed
    if (isset($login_result) && !$login_result) {
?>
          <p class="err">
            Error: Username or password incorrect.
          </p>
<?php
    }
?>
        </div>
      </form>
    
<?php
  }
  // This message shouldn't actually be seen if redirect above occurs as expected.
  else {
?>
      <p class="success">
        Successfully logged in as <?= $auth->get_user() ?>
      </p>
<?php
  }
?>
    </main>
<?php
  require "$root/inc/partials/footer.php";
?>