<?php
  /**
   * api/topics/index.php
   * Author: Adam Beagle
   *
   * API endpoint for retrieving topic data.
   * Currently returns subtopics (direct children only) of topic specified by 'topic' parameter.
   */
  require_once '../../inc/config.php';
  require_once "$root/inc/topics.php";
  require_once "$root/inc/util.php";
  require_once "$root/inc/third-party/json.php";
  
  use \Simple\json;

  $topic_req = get_from_get('topic');
  $json = new json();
  $tm = new TopicManager();

  // Forge the JSON
  if (isset($topic_req)) {
    $json->subtopics = $tm->get_subtopics($topic_req);
  }
  else {
    $json->error_message = 'Missing topic parameter';
  }

  // Send the JSON
  $json->send();
?>