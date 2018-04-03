<?php
  /**
   * api/tags/index.php
   * Author: Adam Beagle
   *
   * API endpoint for retrieving tag data.
   * Currently returns all tags only
   */
  require_once '../../inc/config.php';
  require_once "$root/inc/db.php";
  require_once "$root/inc/third-party/json.php";
  
  use \Simple\json;

  $json = new json();
  $db = new WDWTipsDB();
  $tag_gen = $db->iter_all_tags();
  $tags = [];

  foreach($tag_gen as $tag) {
    $tags[] = $tag['id'];
  }

  if (isset($tags)) {
    $json->tags = $tags;
    $json->send();
  }
?>