<?php
  /**
   * api/vote/index.php
   * Author: Adam Beagle
   *
   * API endpoint for voting
   */
  require_once '../../inc/config.php';
  require_once "$root/inc/db.php";
  require_once "$root/inc/util.php";
  require_once "$root/inc/auth.php";

  $tip_id = get_from_get('tip');
  $up = array_key_exists('up', $_GET);
  $down = array_key_exists('down', $_GET);
  $cancel = array_key_exists('cancel', $_GET);
  
  // Validate basic request requirements
  // (tip provided, either up or down specified)
  if (isset($tip_id) and ($up xor $down)) {
    $auth = new AuthHandler();
    $db = new WDWTipsDB();
  
    // Vote up (or cancel vote up)
    if ($up) {
      if ($cancel) {
        $db->cancel_vote($tip_id, $auth->get_user(), 1);
      }
      else {
        $db->vote_tip_up($tip_id, $auth->get_user());
      }
    }
    // Else vote down (or cancel vote down)
    else {
      if ($cancel) {
        $db->cancel_vote($tip_id, $auth->get_user(), 0);
      }
      else {
        $db->vote_tip_down($tip_id, $auth->get_user());
      }
    }
  }
  
  // else malformed request
  // TODO error state
?> 