<?php
  if (isset($db)) {
    $tags = $db::iter_results($db->get_popular_tags());
  }
  
  // Note if db error etc., $tags will be set to null in iter_results
  if (!isset($tags)) {
    $tags = [];
  }
?>
      <section class="popular-tags">
        <h3>Popular Tags</h3>
<?php
  foreach ($tags as $tag) {
?>
        <a class="btn tag" href="<?= BASEURL ?>tag/?id=<?= $tag['id'] ?>"><?= $tag['id'] ?></a>
<?php
  }
?>
      </section>