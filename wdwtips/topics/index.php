<?php 
/**
 * topics/index.php
 * Author: Adam Beagle
 *
 * Index of all topics (if accessed with no params) or tips by topic if 'topic' param provided.
 */
  require_once "../inc/config.php";
  require_once "$root/inc/db.php";
  require_once "$root/inc/auth.php";
  require_once "$root/inc/util.php";

  function get_topic_url($el) {
    $ancestors = [];
    $name = $el->getName();
    $url = BASEURL . 'topics/?topic=';
    
    while ($parent = $el->xpath('../..')) {
      $ancestors[] = $parent[0]->getName();
      $el = $parent[0];
    }
    
    for ($i = sizeof($ancestors) - 1; $i >= 0; $i--) {
      $url .= $ancestors[$i] . '+';
    }
    
    $url .= $name;
    
    return $url;
  }

  function get_breadcrumbs($el) {
    $parent = $el->xpath('../..');
    
    if ($parent) {
      return $parent[0]->name . ' <span class="bc-arrow"">&gt;</span> ' . $el->name;
    }
    
    return $el->name;
  }

  // TODO shared base url for subtopic pages
  function list_topics($el, $children_only=false) {
    echo '<ul>';
    
    foreach ($el->children() as $topic) {
      echo '<li><a class="btn" href="' . get_topic_url($topic) . '">' . $topic->name . '</a>';

      if (isset($topic->topics) and !$children_only) {
        list_topics($topic->topics);
      }
      echo '</li>';
    }
    
    echo '</ul>';
  }

  // Get XML topic data
  $all_topics = simplexml_load_file("$root/res/topics.xml");
  $topic_req = get_from_request('topic'); 
  $sort_req = get_from_request('sort', 'best');
  
  // If topic requested, get it from XML, and get tips for topic from db
  if (isset($topic_req)) {
    $topic_req = str_replace(' ', '+', $topic_req);
    $topic = $all_topics->xpath(str_replace('+', '//', $topic_req));
    
    // Set topic to result if present, else null
    $topic = sizeof($topic) > 0 ? $topic[0] : null;

    $db = new WDWTipsDB();
    $auth = new AuthHandler();
    
    $tips = $db->iter_tips_by_topic($topic_req, $sort_req, $auth->get_user());
  }

  $page = isset($topic) ? 'topic' : 'all-topics';
  $page_title = isset($topic) ? $topic->name : 'All Topics';
  include "$root/inc/partials/header.php";
?>
    <main>
      <h1>
<?php
  if (isset($topic)) {
    $breadcrumb = get_breadcrumbs($topic);
    
    if (strpos($breadcrumb, '>')) {
      $h1_text = $breadcrumb;
    }
    else {
      $h1_text = 'Topic &ndash; ' . $breadcrumb;
    }
  }
  else {
    $h1_text = 'All Topics';
  }
?>
        <?= $h1_text ?>
      </h1>
      
<?php
  // If topic specified
  if (isset($topic)) {
    // If topic contains subtopics
    if (isset($topic->topics)) {
      
?>
      <h2 class="subtopics">Subtopics</h2>
<?php
      list_topics($topic->topics, true);
    }
    include "$root/inc/partials/tiplist.php";
  }
  // Else only list topics
  else {
    list_topics($all_topics);
  }
?>
      <script>
        $('.topic>main>ul').attr('id', 'accordion');
      
      </script>
    </main>
    <aside>
      <section class="topics">
<?php
  include "$root/inc/partials/aside_submit_tip.php";
?>
        <h3>About Topics</h3>
        
        <p>This page breaks down tips into broad, pre-defined categories so you can quickly find tips about your particular resort, or whatever else you're interested in.</p>
        
        <p>All topics are links. For example, click 'Parks' to see tips for <em>all</em> the parks.</p>
        
        <p>Note that in addition to these topics, tips can be tagged by users to add further specificity outside of these topic categories.</p>
      </section>
    </aside>
<?php
  include "$root/inc/partials/footer.php";
?>