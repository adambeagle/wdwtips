<?php
  /**
  * create-account/index.php
  * Author: Adam Beagle
  *
  * Account creation form, and validation
  */
  require_once '../inc/config.php';
  require_once "$root/inc/auth.php";
  require_once "$root/inc/util.php";

  $db = new WDWTipsDB();
  $auth = new AuthHandler();

  $uname_req = get_from_request('username');
  $pw_req = get_from_request('password');

  // If form submitted, try to create account and redirect to user page on success
  if (form_submitted()) {
    // Verify username unique and set error message if not
    if ($db->user_exists($uname_req)) {
      $err_msg = "User '$uname_req' already exists. Please choose a different username.";
    }
    else if (strlen($uname_req) > MAX_UNAME_LENGTH) {
      // Note frontend validation should prevent this message from ever being reached.
      $err_msg = "Username length cannot exceed 24 characters.";
    }
    else {
      // Create user if no validation issues
      if (!$auth->create_user($uname_req, $pw_req, $db)) {
        $err_msg = 'An unknown error occurred when trying to create your account. Sorry!';
      }
      
      // User created successfully
      header('Location: ' . BASEURL);
    }
  }

  $page = 'create-account';
  $page_title = 'Create Account';
  require "$root/inc/partials/header.php";
?>
    <main>
      <h1>Create Account</h1>
      
<?php
    // Show error message if login attempt failed
    if (isset($err_msg)) {
?>
          <p class="err">
            <strong>Error: <?= $err_msg ?></strong>
          </p>
<?php
    }
?>
      <form method="post">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" name="username" maxlength="24" autofocus required>
          <p id="maxlen-widget"></p>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
          <label for="password-conf">Confirm Password</label>
          <input type="password" name="password-conf" required>
          <p class="no-match hidden"><small>Passwords do not match</small></p>
        </div>
        
        <div class="form-group">
          <button type="submit" name="submit">Submit</button>
        </div>
      </form>
    </main>

    <script>
      var pw1 = $('input[name="password"]'),
          pw2 = $('input[name="password-conf"]'),
          msg = $('.no-match');
      
      pw2.on('input', function() {
        if (pw1.val() && pw2.val()) {
          if (pw1.val() != pw2.val()) {
            msg.removeClass('hidden');
          }
          else {
            msg.addClass('hidden');
          }
        }
      });
      
      $('form').on('submit', function(evt) {
        if (pw1.val() != pw2.val()) {
          msg.css('font-weight',  'bold');
          msg.css('color',  '#800');
          evt.preventDefault();
        }
      })
    </script>
<?php
  require "$root/inc/partials/footer.php";
?>