<?php
  require_once 'util.php';

  class Paginator {
    /**
     * Handles common pagination tasks.
     *
     * On instantiation, sets page attribute automatically from
     * $_REQUEST. Retrieve it with get_page().
     *
     * paginate_query() aids with DB queries by appending 
     * appropriate LIMIT/OFFSET clauses to sql queries.
     *
     * Set PARAM to change which url parameter represents a page.
     * Default is 'page'. The value of this URL parameter will be normalizeed
     * to an integer to prevent SQL injection. 
     * A non-present parameter is effectively the same as the first page.
     *
     * Set PAGINATE_LIMIT to control how many items allowed per page 
     * by default.
     *
     * Paging assumed to be 1-based. 
     * Set ONE_BASED to false to make paging start at 0.
     */
    const PARAM = 'page';
    const PAGINATE_LIMIT = 8;
    const ONE_BASED = true;
    
    private $page;
    
    function __construct() {
      $this->page = self::normalize_page(
        get_from_request(self::PARAM)
      );
    }
    
    function get_page() {
      return $this->page;
    }
    
    function paginate_query($sql, $limit=self::PAGINATE_LIMIT) {
      $offset = $limit*$this->page;
      
      return $sql . "
        LIMIT
          $limit
        OFFSET
          $offset";
    }
    
    static function paginate_query_static($sql, $page, $limit=self::PAGINATE_LIMIT) {
      /**
       * Static version of paginate_query.
       * Page is supplied by argument rather than
       * retrieved from $_REQUEST.
       *
       * $page is normalized to always be an integer. 
       * It is safe to send a user-submitted value.
       */
      $page = self::normalize_page($page);
      $offset = $limit*$page;
      
      return $sql . "
        LIMIT
          $limit
        OFFSET
          $offset";
    }
    
    private static function normalize_page($page) {
      // TODO empty() call probably redundant
      if (empty($page) or !is_numeric($page) or $page < 0) {
        return 0;
      }

      return self::ONE_BASED ? $page - 1 : (int) $page;
    }
  }
?>