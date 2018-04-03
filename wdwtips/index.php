<?php
/**
 * index.php
 * Author: Adam Beagle
 *
 * Landing page for wdwtips site
 */
  require_once 'inc/config.php';
  require_once 'inc/db.php';
  require_once 'inc/auth.php';
  require_once 'inc/pagination.php';
  require_once 'inc/util.php';

  $db = new WDWTipsDB();
  $auth = new AuthHandler();

  $sort_req = get_from_request('sort', 'best');
  $tips = $db->iter_tips($sort_req, $auth->get_user());

  $page = 'index';
  $page_title = '';
  require 'inc/partials/header.php';
?>
    <main>
<?php
  include 'inc/partials/redirect_message.php';
      
  // Show intro message to those not logged in, so it's at the top of the screen on mobile
  if (!$logged_in) {
?>
      <div class="welcome hide-lg">
        <h1>Welcome to WDW-Tips!</h1>
        <p>This site is here to provide tips and advice for your Walt Disney World vacation!</p>
        <p>Tips are organized by topic so you can find the tips most relevant to your particular visit.</p>
        <p>To start voting and submitting tips, <a href="<?= BASEURL ?>create-account/"><strong>click here to create an account</strong></a>.</p>
      </div>
<?php
  }
      
  include 'inc/partials/tiplist.php';
?>
    </main>
    <aside>
<?php
  if (!$logged_in) {
?>
      <a class="btn submit-a-tip hide-lg" href="<?= BASEURL ?>tip/submit/">
        Submit a Tip
      </a>
<?php
  }

  include "inc/partials/aside_default.php";
?>
    </aside>
<?php
  require_once 'inc/partials/footer.php';
?>