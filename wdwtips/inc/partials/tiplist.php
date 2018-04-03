<?php
  /**
   * tiplist.php
   * Author: Adam Beagle
   *
   * Generic tip index partial.
   *
   * Assumes existence of iterable named $tips which contains Tip objects to display.
   */
  require_once "$root/inc/pagination.php";

  $disable_author = isset($disable_author) ? $disable_author : false;
  $sort_req = get_from_request('sort');
  $page_req = get_from_request('page', 1);
  $tip_count = 0;

  foreach ($tips as $tip) {
    $tip_count += 1;
?>
      <article class="tip">
<?php
  include 'vote.php';
?>
        <div class="content">
          <header>
            <h1><a href="<?= BASEURL . "tip/?id=$tip->id" ?>"><?= $tip->title ?></a></h1>
            <span class="info">
              Posted
              <span><?= time_since($tip) ?></span>
              in
              <a class="topic" href="<?= BASEURL ?>topics/?topic=<?= $tip->topic ?>">[<?= str_replace('+', '.', $tip->topic) ?>]</a>
<?php
  if (!$disable_author) {
?>
              by
              <a href="<?= BASEURL ?>user/?id=<?= $tip->author ?>"><?= $tip->author ?></a>
<?php
  }
?>
            </span>
            
          </header>
        </div>
      </article>
<?php
  }

  // Only show controls if at least one tip present 
  if ($tip_count > 0) {
?>
      <div class="controls">
        <div class="pagination">
<?php
    // Only show prev link if not on first page
    if ($page_req > 1) {
?>
          <a class="prev" href="?<?= retain_params('page', $page_req - 1) ?>"> &lt;&lt; Prev</a>
<?php
    }
    // Only show next link if pagination limit reached
    // (note page could be 'exact fit' and next page may not contain any tips)
    if ($tip_count == Paginator::PAGINATE_LIMIT) {
?>
          <span class="next">
            <a class="next" href="?<?= retain_params('page', $page_req + 1) ?>">Next &gt;&gt;</a>
          </span>
<?php
    }
?>
        </div>
        <div class="sort">
          Sort by:
          <a href="?<?= retain_params('sort', 'best') ?>" class="<?= $sort_req == 'best' ? 'inactive' : '' ?>">Best</a>
          |
          <a href="?<?= retain_params('sort', 'recent') ?>" class="<?= $sort_req == 'recent' ? 'inactive' : '' ?>">Recent</a>
        </div>
      </div>
<?php
  }
  else if ($page_req > 1 and $tip_count == 0) {
?>
      <p>
        No more tips! 
        <a href="?<?= retain_params('page', $page_req - 1) ?>">Back to previous page</a>
      </p>
<?php
  }
  else {
?>
      <p>
        No tips here yet. 
<?php
    if ($page != 'user') {
?>
        You can help by <a href="<?= BASEURL ?>tip/submit">submitting</a> one!
<?php
    }
?>
      </p>
<?php
  }
?>