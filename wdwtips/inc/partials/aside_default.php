<?php
  /**
   * aside_default.php
   * Author: Adam Beagle
   *
   * Content for default right aside
   */
?>
      <section class="intro<?= $auth->am_logged_in() ? '' : ' show-lg' ?>">
        <h2>Welcome to WDW-Tips!</h2>
        <p>This site is here to provide tips and advice for your Walt Disney World vacation!</p>
        <p>Tips are organized <a href="<?= BASEURL ?>topics/">by topic</a> so you can find the tips most relevant to your particular visit.</p>
<?php
  if (!$logged_in) {
?>
        <p>To start voting and submitting tips, <a href="<?= BASEURL ?>create-account/">click here to create an account</a>.</p>
<?php
  }
        
  include 'aside_submit_tip.php';
?>
      </section>

<?php
  include 'aside_popular_tags.php';
?>