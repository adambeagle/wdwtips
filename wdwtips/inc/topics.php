<?php
  require_once 'config.php';

  class Topic {
    public $slug;
    public $name;
    
    function __construct($xmlEl) {
      $this->slug = $xmlEl->getName();
      $this->name = $xmlEl->name;
    }
  }

  class TopicManager {
    /**
     * Interface to topics XML
     */
    private $root;
    
    function __construct() {
      global $root;
      $this->root = simplexml_load_file("$root/res/topics.xml");
    }
    
    function iter_top_level_topics() {
      foreach ($this->root->children() as $topic) {
        yield $topic;
      }
    }
    
    function get_subtopics($query) {
      $el = $this->root->xpath(str_replace('+', '//', $query));
      
      if (!$el) {
        return [];
      }
      
      $el = $el[0];
      $subtopics = [];
      
      if (isset($el->topics)) {
        foreach($el->topics->children() as $sub) {
          $subtopics[] = new Topic($sub);
        }
      }
      
      return $subtopics;
    }
    
    function is_topic($query) {
      /** 
       * Return boolean indicating whether $query is an existing topic
       */
      return $this->root->xpath(str_replace('+', '//', $query));
    }
  }
?>