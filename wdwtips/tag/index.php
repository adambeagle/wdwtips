<?php
  /**
  * tag/index.php
  * Author: Adam Beagle
  *
  * Index of tips by tag, defined by param 'id'.
  */

  require_once '../inc/config.php';
  require_once "$root/inc/auth.php";
  require_once "$root/inc/db.php";
  require_once "$root/inc/util.php";

  $db = new WDWTipsDB();
  $auth = new AuthHandler();

  $id_req = get_from_request('id');
  $sort_req = get_from_request('sort', 'best');

  if (isset($id_req)) {
    $tips = $db->iter_tips_by_tag($id_req, $sort_req, $auth->get_user());
    
    if (is_null($tips)) {
      $err_msg = "Tag '$id_req' not found.";
    }
  }
  else {
    $err_msg = 'No tag specified.';
  }

  $page = 'tag';
  $page_title = isset($id_req) ? "$id_req" : 'Tag';
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
  ?>
      <h1>Tigs tagged <span class="tag-name">&lt;<?= $id_req ?>&gt;</span></h1>
  <?php
    include "$root/inc/partials/tiplist.php";
  }
?>
    </main>
    <aside>

      <section>
<?php
  include "$root/inc/partials/aside_submit_tip.php";
?>
        <h3>Related Tags</h3>
        <p>[None yet]</p>
      </section>
   
<?php
  include "$root/inc/partials/aside_popular_tags.php";
?>
      
      <section>
        <h3>About Tags</h3>
        <p>Tags are used to further organize tips beyond topic, with more specific information. They are user-submitted.</p>
      </section>
      
    </aside>
<?php
  require "$root/inc/partials/footer.php";
?>