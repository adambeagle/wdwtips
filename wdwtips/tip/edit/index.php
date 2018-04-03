<?php
  /**
   * tip/edit/index.php
   * Author: Adam Beagle
   *
   * Edit-a-tip form and its validation, etc.
   * Only body text is editable.
   */
  require_once '../../inc/config.php';
  require_once "$root/inc/db.php";
  require_once "$root/inc/auth.php";
  require_once "$root/inc/util.php";
  require_once "$root/inc/tags.php";

  $auth = new AuthHandler();
  $id_req = get_from_request('id');
  $details_req = get_from_request('details');
  $tags_req = get_from_request('tags');

  // Validate required field id exists
  if (is_null($id_req)) {
    $err_msg_fatal = 'Tip not found (no ID provided).';
  }
  // Validate login
  else if (!$auth->am_logged_in()) {
    $err_msg_fatal = 'You must be logged in to edit a tip.';
  }
  // If no problems, get tip from db
  else {
    $db = new WDWTipsDB();
    $tip = $db->get_tip($id_req);
    $tags = $db->iter_tags_by_tip($id_req);
    
    // Validate tip exists
    if (!$tip) {
      $err_msg_fatal = 'Tip not found.';
    }
    // Validate tip belongs to user
    else if ($tip->author != $auth->get_user()) {
      $err_msg_fatal = 'This tip was submitted by another user. You do not have permission to edit it.';
    }
    // If form submitted, perform db update and redirect
    // to tip page if successful
    else if (form_submitted()) {
      $edit_success = $db->edit_tip_body($id_req, $details_req);
      $tag_success = TagManager::update_tip_tags($id_req, $tags_req, $tags, $db);
      
      if ($edit_success && $tag_success) {
        header("Location: " . BASEURL . "tip/?id=$id_req" );
      }
      else {
        $err_msg = 'Something went wrong when updating the tip. Sorry!';
      }
    }
  }

  $page = 'edit-tip';
  $page_title = 'Edit Tip';
  require "$root/inc/partials/header.php";
?>
    <main>
      <h1>Edit Tip</h1>
<?php
  // If fatal state, only print message and footer
  if (isset($err_msg_fatal)) {
?>
      <p class="err"><strong>Error: <?= $err_msg_fatal ?></strong></p>
<?php
  }
  // Else show form (and optional non-fatal err_msg)
  else {
    if (isset($err_msg)) {
?>
      <p class="err"><strong>Error: <?= $err_msg_fatal ?></strong></p>
<?php
    }
?>
      <form class="tip edit-tip" method="post">
        <div class="form-group">
          <label for="title">
            Title <small>(required)</small>
          </label>
          <input type="text" name="title" value="<?= $tip->title ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="details">Details</label>
          <textarea name="details" autofocus><?= $tip->content->get_source() ?></textarea>
          <p>Note: In this field you may use Markdown for formatting.</p>
        </div>
        
        <div class="form-group">
          <label for="topic">Topic</label>
          <input type="text" name="topic" value="<?= str_replace('+', '.', $tip->topic) ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="tag">Tags</label>
          <div class="form-group-nested-inline">
            <div class="invalid-key-msg">
              &#x2757; Please use only a-z, 0-9, hyphen (-)
            </div>
            <input class="add-tag"<?= $auth->am_logged_in() ? '' : 'disabled' ?>>
            <button class="add-tag" aria-label="Add tag">Add</button>
          </div>
          <p><small>Note: <span class="max-tags">Maximum <?= MAX_TAGS_PER_TIP ?> tags.</span> Only alphanumeric characters and hypens (-) allowed. </small></p>
          <input name="tags" type="hidden">
          <div class="added-tags">
<?php
    if (isset($tags)) {
      foreach($tags as $tag) {
?>
            <a class="btn tag">
              <?= $tag['id'] ?>
              <button class="remove" aria-label="Remove tag">✖</button>
            </a>
            
<?php
      }
    }
?>
          </div>
        </div>
        
        <button type="submit" name="submit">Submit</button>
      </form>
    </main>
    <aside>
<?php
  include "$root/inc/partials/aside_tip_guidelines.php";
?>
    </aside>
<?php
  }
?>
<?php
  require "$root/inc/partials/footer.php";
?>