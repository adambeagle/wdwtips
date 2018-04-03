<?php
  /**
  * tip/index.php
  * Author: Adam Beagle
  *
  * Display a single tip whose id was passed by 'id' URL param.
  */
  require_once '../inc/config.php';
  require_once "$root/inc/db.php";
  require_once "$root/inc/auth.php";
  require_once "$root/inc/util.php";
  
  $auth = new AuthHandler();
  $db = new WDWTipsDB();
  $id_req = get_from_request('id');
  $tip = $db->get_tip($id_req, $auth->get_user());

  if (isset($tip)) {
    $tags = $db->iter_tags_by_tip($tip->id);
  }

  $page = 'tip';
  $page_title = '';

  if (is_null($tip)) {
    include "$root/inc/partials/header.php";
    echo '<p class="err">Tip not found.</p>';
    include "$root/inc/partials/footer.php";
    die();
  }

  require "$root/inc/partials/header.php";
?>
    <main>
      <article class="tip">
<?php
  include "$root/inc/partials/vote.php";
?>
        <div class="content">
          <header>
            <h1><a href="<?= BASEURL . "tip/?id=$tip->id" ?>"><?= $tip->title ?></a></h1>
            <span class="info">
              Posted
              <span><?= time_since($tip) ?></span>
              in
              <a class="topic" href="<?= BASEURL ?>topics/?topic=<?= $tip->topic ?>">[<?= str_replace('+', '.', $tip->topic) ?>]</a>
              by
              <a href="<?= BASEURL ?>user/?id=<?= $tip->author ?>"><?= $tip->author ?></a>
            </span>
          </header>
          <div class="body">
            <?= $tip->content->get_html() ?>
<?php
  if (isset($tip->edit_timestamp)) {
?>
            <p class="last-edit">
              Last edited <?= $tip->edit_timestamp ?>
            </p>
<?php
  }
?>
          </div>
<?php if ($tip->author == $auth->get_user()) {
?>
          <a class="tip-control edit" href="<?= BASEURL ?>tip/edit/?id=<?= $id_req ?>">[Edit]</a>
          <a class="tip-control delete" href="<?= BASEURL ?>tip/delete/?id=<?= $id_req ?>">[Delete]</a>
<?php
  }
?>
<?php
  if ($db->get_row_count() > 0) {
?>
        <div class="tags">
          <h2>Tags</h2>
<?php
  foreach ($tags as $tag) {
?>
          <a class="btn tag" href="<?= BASEURL ?>tag/?id=<?= $tag['id'] ?>"><?= $tag['id'] ?></a>
<?php
    }
?> 
        </div>
<?php
  }
?>
        </div> <!-- end content -->
      </article>  

      <section class="comments">
        <h2>Comments</h2>
        
        <form>
          <textarea name="comment"></textarea>
          <button type="submit">Submit</button>
        </form>
        
        <!-- TODO remove when comments implemented -->
        <script>
          $('.comments form').on('submit', function(evt) {
            evt.preventDefault();
          })
        </script>
        
        <p><em>No comments yet</em></p>
        
        <!--<div class="comment">
          <header>
            <a class="user" href="#">pooh-for-president</a>
            <span class="posted">3 hours ago</span>
          </header>
          <div>
            This is a good tip.
          </div>
        </div>-->
      </section>
  </main>
  <aside>
<?php
  include "$root/inc/partials/aside_submit_tip.php";
?>
    <section>
      <h3>Related Tags</h3>
      <p>[None yet]</p>
    </section>
    <section>
      <h3 id="guidelines">Comment Guidelines</h3>
      <p>
        Have something to add? Great! First, please read through these guidelines.
      </p>
      
      <ol>
        <li>
          Please keep comments on-topic and constructive. Comments here are for information, not conversation.
        </li>
        <li>
          Do not comment simply to say you agree &mdash; vote the tip up instead. If you disagree with the tip, feel it is inaccurate, or have a conflicting anecdotal experience, explain in a comment.
        </li>
        <li>
          Do not comment to share an opinion that is not <em>directly</em> related to the tip itself.
        </li>
      </ol>
    </section>
  </aside>
<?php
  require "$root/inc/partials/footer.php";
?>
