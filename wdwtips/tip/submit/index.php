<?php
  /**
   * tip/submit/index.php
   * Author: Adam Beagle
   *
   * Submit-a-tip form and its validation, etc.
   */
  require_once '../../inc/config.php';
  require_once "$root/inc/db.php";
  require_once "$root/inc/auth.php";
  require_once "$root/inc/util.php";
  require_once "$root/inc/tips.php";
  require_once "$root/inc/tags.php";
  require_once "$root/inc/topics.php";
  
  $db = new WDWTipsDB();
  $auth = new AuthHandler();
  $tm = new TopicManager();

  // On form submit, validate request and attempt to insert new tip
  if (form_submitted() && $auth->am_logged_in()) {
    $title_req = get_from_request('title');
    $details_req = get_from_request('details');
    $topic_req = get_from_request('topic');
    $tags_req = get_from_request('tags');
    
    // Validate required fields
    if (is_null($title_req)) {
      $err_msg = 'Title required.';
    }
    else if (is_null($topic_req)) {
      $err_msg = 'Topic required.';
    }
    else if (!$tm->is_topic($topic_req)) {
      $err_msg = 'Invalid topic.';
    }
    else if(strlen($title_req) > Tip::MAX_TITLE_LENGTH) {
      $err_msg = 'Title must be fewer than ' . Tip::MAX_TITLE_LENGTH . ' characters.';
    }
    else if (!TagManager::is_valid_request($tags_req)) {
      var_dump($tags_req);
      $err_msg = 'Invalid tags.';
    }
    
    // Else attempt insert
    else {
      $insert_id = $db->add_tip(
        $title_req,
        $auth->get_user(),
        $details_req,
        $topic_req
      );
      
      // If insert successful, attempt to add tags, then redirect to tip page
      if (isset($insert_id)) {
        
        foreach(TagManager::request_to_array($tags_req) as $tag) {
          $db->create_tag($tag, $insert_id);
        }
        
        header("Location: " . BASEURL . "tip/?id=$insert_id" );
      }
      // Else show error message
      else {
        include "$root/inc/partials/header.php";
        echo '<p class="err">Sorry! An error occurred while submitting this tip.</p>';
        include "$root/inc/partials/footer.php";
      }
    }
  }
  else if (form_submitted() && !$auth->am_logged_in()) {
    $err_msg = 'Must be logged in to submit a tip.';
  }

  // If no form submitted, show form
  $page = 'submit-tip';
  $page_title = 'Submit a Tip';
  require "$root/inc/partials/header.php";
?>
    <main>
      <h1>Submit a Tip</h1>
<?php
  if (isset($err_msg)) {
?>
      <p class="err"><strong>Error: <?= $err_msg ?></strong></p>
<?php
  }
  else if(!$auth->am_logged_in()) {
?>
      <p><strong>Note: You must be <a href="<?= BASEURL ?>login/">logged in</a> to submit a tip.</strong></p>
<?php
  }
?>
      <form class="tip submit-tip" method="post">
        <div class="form-group">
          <label for="title">
            Title <small>(required)</small>
          </label>
          <input type="text" name="title" maxlength="<?= Tip::MAX_TITLE_LENGTH ?>" value="<?= get_from_request('title', '') ?>" required autofocus <?= $auth->am_logged_in() ? '' : 'disabled' ?>>
<!--          <p class="title-len success">(0 / <?= Tip::MAX_TITLE_LENGTH ?>)</p>-->
          <p id="maxlen-widget"></p>
        </div>

        <div class="form-group">
          <label for="details">Details</label>
          <textarea name="details" <?= $auth->am_logged_in() ? '' : 'disabled' ?>><?= get_from_request('details', '') ?></textarea>
          <p><small>Note: In this field you may use <a href="https://daringfireball.net/projects/markdown/syntax" target="_blank">Markdown</a> for formatting.</small></p>
        </div>
        
        <div class="form-group">
          <label for="topic">Topic <small>(required &ndash; subtopics are optional)</small></label>
          <div class="topic-selects">
          <select class="topics" name="topic" <?= $auth->am_logged_in() ? '' : 'disabled' ?> required>
              <option></option>
<?php
  foreach ($tm->iter_top_level_topics() as $topic) {
?>
              <option value="<?= $topic->getName() ?>"><?= $topic->name ?></option>
<?php
  }
?>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label for="add-tag">Tags</label>
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
          </div>
        </div>
        
        <button type="submit" name="submit" <?= $auth->am_logged_in() ? '' : 'disabled' ?>>Submit</button>
      </form>
    </main>
    <aside>
<?php
  include "$root/inc/partials/aside_tip_guidelines.php";
?>
    </aside>
<?php
  require "$root/inc/partials/footer.php";
?>