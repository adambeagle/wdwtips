<?php
  /**
   * inc/partials/redirect_message.php
   * Author: Adam Beagle
   *
   * Shows a message based on url parameter designating that 
   * some action occurred before a redirect.
   *
   * Available actions (names are url keys; values don't matter):
   *  'del_tip'   - tip deleted
   *  'logout'    - user logged out successfully
   */

  if (array_key_exists('del_tip', $_REQUEST)) {
    $redirect_msg = 'Tip successfully deleted';
  }
  else if (array_key_exists('logout', $_REQUEST)) {
    $redirect_msg = 'Logged out successfully';
  }

  if (isset($redirect_msg)) {
?>
      <p class="redirect-msg"><?= $redirect_msg ?></p>
<?php
  }
?>