<?php
  /**
   * inc/classes.php
   * Author: Adam Beagle
   *
   * Utility classes for wdwtips
   */
  require_once 'third-party/Parsedown.php';

  class MarkdownException extends Exception {}

  class MarkdownContent {
    /**
     * Represents piece of content with Markdown formatting.
     * Handles parsing of Markdown to HTML
     */
    protected $parsedown;
    protected $source;
    protected $html;
    
    function __construct($source, $parsedown=null) {
      // Set $this->parsedown as existing instance if argument valid, else new instance.
      if (is_a($parsedown, 'Parsedown')) {
        $this->parsedown = $parsedown;
      }
      else if (isset($parsedown)) {
        throw new InvalidArgumentException(
          '$parsedown is not instance of Parsedown. Got: ' . var_dump($parsedown)
        );
      }
      else {
        $this->parsedown = new Parsedown();
      }

      $this->set_content($source);
    }
    
    function get_html() {
      return $this->html;
    }
    
    function get_source() {
      return $this->source;
    }
    
    function set_content($source) {
      $this->source = $source;
      $this->html = $this->parsedown->text($source);
    }
  }

  class MarkdownContentUnsafe extends MarkdownContent {
    /**
     * Represents piece of potentially unsafe (e.g. user-generated)
     * content with Markdown formatting.
     * Handles parsing of Markdown to HTML
     */
    function __construct($source) {
      $parsedown = new Parsedown();
      $parsedown->setSafeMode(true);
      parent::__construct($source, $parsedown);
    }
  }

  class UserMarkdownContent {
    /**
     * Represents piece of markdown content which belongs to a user.
     */
    public $author;
    public $content;
    protected $timestamp;
    
    function __construct($author, $source, $timestamp=null) {
      $this->author = $author;
      $this->set_content($source);
      $this->timestamp = isset($timestamp) ? $timestamp : time();
    }
    
    function set_content($md) {
      /**
       * md expects Markdown source as string.
       */
      $this->content = new MarkdownContentUnsafe($md);
    }
    
    function get_timestamp($format=null) {
      if (isset($format)) {
        return date($format, $this->timestamp);
      }
      else {
        return $this->timestamp;
      }
    }
  }

  class Tip extends UserMarkdownContent {
    /**
     * Represents single tip.
     *
     * Handles parsing of Markdown source to HTML.
     */
    const MAX_TITLE_LENGTH = 300;
    public $id;
    public $title;
    public $score;
    public $vote;
    public $topic;
    public $edit_timestamp;
    private $comments;
    
    function __construct($id, $title, $author, $content, $timestamp, $edit_timestamp, $score, $topic, $vote=null, $comments=null) {
      $this->id = $id;
      $this->title = $title;
      $this->score = $score;
      $this->topic = $topic;
      $this->edit_timestamp = $edit_timestamp;
      $this->comments = [];
      $this->vote = isset($vote) ? (bool) $vote : null;
      
      if (is_array($comments)) {
        foreach ($comments as $comment) {
          $this->comments[] = $comment;
        }
      }
      
      parent::__construct($author, $content, $timestamp);
    }
    
    function get_comments() {
      return $this->comments;
    }
  }
?>