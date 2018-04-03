<?php
  /**
   * db.php
   * Author: Adam Beagle
   *
   * Database-related classes and functions for wdw-tips
   */
  require_once 'config.php';
  require_once 'tips.php';
  require_once 'pagination.php';

  class TipSorter {
    /**
     * Appends appropriate ORDER BY clauses to tip selects.
     *
     * Implemented sorts are: 
     *  'best'   - score, then timestamp, both descending
     *  'recent' - timestamp descending
     *
     * Use either sort name above as $sort parameter in
     * call to TipSorter::sort_query() to do that sort.
     */
    
    static function sort_query($sql, $sort, $qualifier='') {
      /**
       * $sql is query string to which ORDER BY clause will be appended.
       *
       * $sort is sort name. See class docstring.
       *
       * $qualifier is optional table reference name to prepend to columns.
       */
      
      if ($sort == 'best') {
        return self::get_sort_best($sql, $qualifier);
      }
      else if ($sort == 'recent') {
        return self::get_sort_recent($sql, $qualifier);
      }
    }
    
    private static function get_sort_best($sql, $qualifier) {
      if (empty($qualifier)) {
        $append = '
          ORDER BY
            score DESC, timestamp DESC';
      }
      else {
        $append = "
          ORDER BY
            $qualifier.score DESC, $qualifier.timestamp DESC";
      }
      
      return $sql.$append;
    }
    
    private static function get_sort_recent($sql, $qualifier) {
      if (empty($qualifier)) {
        $append = '
          ORDER BY
            timestamp DESC';
      }
      else {
        $append = "
          ORDER BY
            $qualifier.timestamp DESC";
      }
      
      return $sql.$append;
    }
  } // End TipSorter

  class MysqlHappyFunTime {
    /**
     * Base class to facilitate operations on MySQL databases.
     *
     * Note creating an instance of this class automatically connects to the database.
     *
     * Assumes the following constants are set appropriately elsewhere:
     *  DB_NAME  - database name
     *  DB_HOST  - database host
     *  DB_USER  - database username
     *  DB_PASS  - database password
     *
     * Pagination available by default, using the Paginator class from pagination.php.
     * To disable pagination, set PAGINATE to false.
     *
     * To use pagination on a query, set $paginated paramater to 
     * true on calls to prep_and_exec().
     */
    
    const PAGINATE = true;
    
    public $db;
    protected $error_msg;
    protected $error_info;
    protected $row_count;
    protected $paginator;
    
    function __construct($paginator=null) {
      $this->error_msg = '';
      $this->error_info = '';
      $this->db = $this::connect();
      $this->row_count = null;
      
      if ($paginator instanceof Paginator) {
        $this->paginator = $paginator;
      }
      else {
        $this->paginator = new Paginator();
      }
    }
    
    function clear_error() {
      $this->error_msg = '';
    }
    
    function had_error() {
      /**
       * Return boolean showing whether error_msg is set, i.e. if * an error occurred at some point since instantiation or 
       * the last call to clear_error.
       */
      return (bool) $this->error_msg;
    }
    
    function get_error_info() {
      /** 
       * Return detailed information about the most recent error.
       *
       * Typically contains result of Exception::getMessage() or PDO::errorInfo().
       *
       * This should generally not be displayed to the end user.
       */
      return $this->error_info;
    }
    
    function get_error_message() {
      /**
       * Return end user-friendly/safe message about most recent error.
       */
      return $this->error_msg;
    }
    
    function get_paginator() {
      return $this->paginator;
    }
    
    function get_row_count() {
      /**
       * Return rowCount() of most recent prep_and_exec() call
       */
      return $this->row_count;
    }
    
    static function iter_results($results) {
      /**
       * Generator. Yields rows of query result set $results, as assoc arrays.
       */
      if (!$results) {
        return null;
      }
      
      while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        yield $row;
      }
    }
    
    function prep_and_exec($sql, $params=null, $paginated=false, $fail_msg=null) {
      try {
        if (self::PAGINATE and $paginated) {
          $sql = $this->paginator->paginate_query($sql);
        }
        $ps = $this->db->prepare($sql);
        $result = $ps->execute($params);
        
        if ($result) {
          $this->row_count = $ps->rowCount();
        }
      }
      catch (PDOException $e) {
        $this->error_msg = "A problem occurred querying the database.";
        $this->error_info = $e->getMessage();
        $result = null;
      }
      
      $this->populate_error($result, $fail_msg);
      
      return $ps ? $ps : null;
    }
    
    function query($query, $fail_msg=null, $append='') {
      /**
       * Return result of $query if no exceptions.
       * If exception thrown, set err_msg and return null;
       */

      try {
        $result = $this->db->query($query . " $append");
      }
      catch (PDOException $e) {
        $this->error_msg = "A problem occurred querying the database.";
        $this->error_info = $e->getMessage();
        $result = null;
      }
      
      $this->populate_error($result, $fail_msg);
      return $result;
    }
    
    private function populate_error($result, $fail_msg=null) {
      if (!$result) {
        $this->error_info = $this->db->errorInfo();
        
        if (isset($fail_msg)) {
          $this->error_msg = $fail_msg;
        }
        else {
          $this->error_msg = "A database error occurred.";
        }
      }
    }
    
    private static function connect() {
      /**
       * Connect to database.
       * Return PDO object if connection successful.
       * If exception thrown, store message in error_msg and return null.
       */
      try {
        $db = new PDO(
          "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
          DB_USER, DB_PASS
        );
      }
      catch (Exception $e) {
        $this->error_msg = "There was a problem connecting to the database." . $e->getMessage();
        $db = null;
      }

      return $db;
    }
  }

  class WdwTipsDB extends MysqlHappyFunTime {
    /**
     * Helper class to facilitate operations on the wdwtips database.
     *
     * Extends MysqlHappyFunTime with functions specific to the application.
     *
     * Note creating an instance of this class automatically connects to the database.
     */
    
    const SELECT_TIPS = '
      SELECT
        id, 
        UNIX_TIMESTAMP(timestamp) as timestamp, 
        author, title, score, topic
      FROM
        tips';
    
    const SELECT_TIPS_WITH_VOTES = '
      SELECT
        t.id,
        UNIX_TIMESTAMP(t.timestamp) AS timestamp,
        t.author,
        t.title,
        t.score,
        t.topic,
        v.type as vote
      FROM
        tips t
      LEFT JOIN votes v ON
        (v.user_id = ?) AND (v.tip_id = t.id)';
    
    const TIP_COLUMNS = '
        id,
        UNIX_TIMESTAMP(timestamp) AS timestamp,
        author,
        title,
        score,
        topic';

    
    /*****************************
    * PUBLIC FUNCTIONS
    *****************************/
    function add_tip($title, $author, $body, $topic) {
      /**
       * Add tip described by arguments to db.
       *
       * $title will have HTML special characters escaped.
       */
      $title = htmlspecialchars($title);
      
      $sql = '
        INSERT INTO
          tips (author, title, body_source, topic)
        VALUES
          (?, ?, ?, ?)';
      
      $res = $this->prep_and_exec($sql, [$author, $title, $body, $topic]);
      
      // Get auto-inc'd id of insert
      $id = $res ? $this->db->lastInsertId() : null;
      
      // If insert successful, add automatic upvote by author on submission
      if ($id) {
        $this->insert_vote($id, $author, 1);
      }
      
      return $id;
    }
    
    function associate_tag($tag_id, $tip_id) {
      /**
       * Associate tag $tag_id with tip $tip_id
       */
      $sql = '
        INSERT INTO
          tips_tags (tip_id, tag_id)
        VALUES
          (?, ?)';
      
      return $this->prep_and_exec($sql, [$tip_id, $tag_id]);
    }
    
    function cancel_vote($id, $user, $type) {
      /**
       * Cancel vote by $user of tip $id. 
       * If $type truthy, assumed to be upvote, otherwise downvote.
       *
       * In addition to deleting vote record, sets score of tip appropriately.
       *
       * Return bool indicating whether both queries succeeded.
       */
      $inc_dec = $type ? '+' : '-';
      
      $del_sql = '
        DELETE FROM
          votes
        WHERE
          tip_id = ?
          AND
          user_id = ?
      ';
      
      $upd_sql = "
        UPDATE
          tips
        SET
          score = score $inc_dec 1
        WHERE
          id = ?
      ";
      
      $del_res = $this->prep_and_exec($del_sql, [$id, $user]);
      $upd_res = $this->prep_and_exec($upd_sql, [$id]);
      
      return ($del_res and $upd_res);
    }
    
    function cleanup_tags() {
      /**
       * Delete any unused tags
       */
      $sql = '
        DELETE FROM
          tags
        WHERE NOT EXISTS (
          SELECT NULL FROM tips_tags
            WHERE
              tags.id = tips_tags.tag_id
        )';
      
      return $this->prep_and_exec($sql);
    }
    
    function create_tag($tag_id, $tip_id) {
      /**
       * Create tag with $tag_id, and assign it to tip with $tip_id.
       *
       * Return bool indicating whether both queries succeeded.
       */
      $rel_res = false;
      
      if (!$tag_id) {
        return null;
      }
      
      $sql = '
        INSERT INTO
          tags (id)
        VALUES
          (?)';
      
      // Note tag insert is always attempted, but may have no effect
      // if tag with $tag_id already exists
      $tag_res = $this->prep_and_exec($sql, [$tag_id]);
      
      if ($tag_res) {
        $rel_res = $this->associate_tag($tag_id, $tip_id);
      }
  
      return ($tag_res and $rel_res);
    }
    
    function create_user($uname, $pwhash) {
      $sql = '
        INSERT INTO
          users (uname, pwhash, joined)
        VALUES
          (?, ?, ?)';
      
      return $this->prep_and_exec(
        $sql, 
        [$uname, $pwhash, date('Y-m-d')]
      );
    }
    
    function delete_tag_if_unused($id) {
      $sql = '
        DELETE FROM
          tags
        WHERE
          id = :id
          AND NOT
          EXISTS (
            SELECT * FROM tips_tags WHERE tag_id = :id
          )';
      
      return $this->prep_and_exec($sql, [':id'=>$id]);
    }
    
    function delete_tip($id) {
      $sql = '
        DELETE FROM
          tips
        WHERE
          id = ?';
      
      if ($res = $this->prep_and_exec($sql, [$id])) {
        $this->cleanup_tags();
      }
      
      return $res;
    }
    
    function disassociate_tag($tag_id, $tip_id) {
      /**
       * Disassociate tag $tag_id from tip $tip_id.
       * If tag no longer used on any tips, delete it from tags.
       */
      $del_rel_result = false;
      
      $sql = '
        DELETE FROM
          tips_tags
        WHERE
          tag_id = ? AND tip_id = ?';
      
      $del_tag_result = $this->prep_and_exec($sql, [$tag_id, $tip_id]);
      
      if ($del_tag_result and $del_tag_result->rowCount() > 0) {
        $del_rel_result = $this->delete_tag_if_unused($tag_id);
      }
      
      return ($del_tag_result and $del_rel_result);
    }
    
    function edit_tip_body($id, $source) {
      $sql = '
        UPDATE
          tips
        SET
          body_source = ?,
          edit_timestamp = CURRENT_TIMESTAMP
        WHERE
          id = ?';
      
      return $this->prep_and_exec($sql, [$source, $id]);
    }
    
    function get_canonical_username($user) {
      /**
       * Return canonical (i.e. as originally capitalized) version of username
       * for user $user.
       *
       * If db failure or other problem, defaults to $user.
       */
      $sql = '
        SELECT
          uname
        FROM
          users
        WHERE
          uname=?
      ';
      
      $res = $this->prep_and_exec($sql, [$user]);

      return $res ? $res->fetch(PDO::FETCH_NUM)[0] : $user;
    }
    
    function get_tip($id, $uname=null) {
      /**
       * Return Tip object for tip with id $id, 
       * or null if not found or db error.
       * (See get_error_info() or get_error_message() for more in that case).
       *
       * If $uname set (assumed to be logged in user), also return vote data.
       */

      if (isset($uname)) {
        $sql = '
          SELECT
            t.id,
            UNIX_TIMESTAMP(t.timestamp) AS timestamp,
            t.edit_timestamp,
            t.author,
            t.title,
            t.body_source,
            t.score,
            t.topic,
            v.type as vote
          FROM
            tips t
          LEFT JOIN votes v ON
            (v.user_id = ?) AND (v.tip_id = t.id)
          WHERE
            t.id = ?';
          
        $result = $this->prep_and_exec($sql, [$uname, $id]);
      }
      else {
        $sql = '
          SELECT
            id, 
            UNIX_TIMESTAMP(timestamp) as timestamp, 
            author, 
            title, 
            body_source, 
            topic,
            score
          FROM
            tips
          WHERE
            id = ?';
        
        $result = $this->prep_and_exec($sql, [$id]);
      }

      // Return null if db error
      if (!$result) {
        return null;
      }
      
      // Else fetch row
      $row = $result->fetch(PDO::FETCH_ASSOC);
      
      // Set error_msg if no tip with $id found
      if (!$row) {
        $this->error_msg = "Tip '$id' not found.";
        return null;
      }

      // Else if row populated, return Tip object
      return self::tip_from_row($row);
    }
    
    function get_popular_tags($limit=10) {
      /**
       * Return popular tags, i.e. tags with higest frequency of appearance
       * in tips_tags. Up to &limit tags are returned.
       *
       * Result rows have keys 'id' (tag id) and 'count' (number of uses)
       */
      
      // There is an issue with prepared statements where integer
      // parameters are treated as strings and quoted, which breaks
      // LIMIT clauses (result is syntax error).
      //
      // So, $limit is explicitly type-checked to prevent injection
      // and concated to query
      if (!is_int($limit)) {
        $this->error_info = '$limit must be an integer';
        return null;
      }
      
      $sql = '
        SELECT 
          tag_id as id, COUNT(tag_id) as count
        FROM 
          tips_tags 
        GROUP BY 
          tag_id
        ORDER BY 
          count DESC
        LIMIT ' . $limit;
      
      return $this->prep_and_exec($sql);
    }
    
    function get_user($uname) {
      $sql = '
        SELECT * FROM
          users
        WHERE
          uname = ?';
      
      $result = $this->prep_and_exec($sql, [$uname]);
      
      // Return null if db error
      if (!$result) {
        return null;
      }
      
      // Else fetch row
      $row = $result->fetch(PDO::FETCH_ASSOC);
      
      // Set error_msg if no user with $uname found
      if (!$row) {
        $this->error_msg = "User '$uname' not found.";
        return null;
      }

      // Else if row populated, return it
      return $row;
    }
    
    function iter_all_tags() {
      /**
       * Generator. Yield all tags, or null on error
       */
      
      $sql = '
        SELECT 
          id
        FROM
          tags';
      
      return self::iter_results($this->prep_and_exec($sql));
    }
    
    function iter_tags_by_tip($tip_id) {
      $sql = '
        SELECT
          tag_id as id
        FROM
          tips_tags
        WHERE
          tip_id = ?';
      
      return self::iter_results(
        $this->prep_and_exec($sql, [$tip_id])
      );
    }

    function iter_tips($sort='best', $uname=null) {
      /**
       * Yields Tip objects for all tips, ordered by score (desc), then age (newest first).
       *
       * If $uname provided, will also populate vote data for 
       * each tip for given user.
       */
      
      if (isset($uname)) {
        $result = $this->get_all_tips_with_votes($uname, $sort);
      }
      else {
        $result = $this->get_all_tips($sort);
      }
      
      // On DB failure, return null
      if (!$result) {
        return null;
      }
      
      // Else yield Tip objects
      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        yield self::tip_from_row($row);
      }
    }
    
    function iter_tips_by_tag($tag_id, $sort='best', $uname=null) {
      /**
       * Yield tips (with vote data, if $uname provied) for tag
       * $topic, and any subtopics.
       */
      
      // Join with vote data if logged in
      if (isset($uname)) {
        $res = $this->get_tips_by_tag_with_votes($tag_id, $uname, $sort);
      }
      // Else just get tips
      else {
        $res = $this->get_tips_by_tag($tag_id, $sort);
      }
      
      if (!$res) {
        return null;
      }
      
      // Else yield Tip objects
      while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        yield self::tip_from_row($row);
      }
    }
    
    function iter_tips_by_topic($topic, $sort='best', $uname=null) {
      /**
       * Yield tips (with vote data, if $uname provied) for topic
       * $topic, and any subtopics.
       */
      $topic = "$topic%";
      
      // Join with vote data if logged in
      if (isset($uname)) {
        $res = $this->get_tips_by_topic_with_votes($topic, $sort, $uname);
      }
      // Else just get tips
      else {
        $res = $this->get_tips_by_topic($topic, $sort);
      }
      
      if (!$res) {
        return null;
      }
      
      // Else yield Tip objects
      while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        yield self::tip_from_row($row);
      }
    }
    
    function iter_tips_by_user($uname, $sort='best', $auth_uname=null) {
      /**
       * Yield tips for user $uname, or null on error.
       *
       * If $auth_uname supplied (logged-in user), vote data
       * included in results.
       */
      
      if (empty($auth_uname)) {
        $result = $this->get_tips_by_user($uname, $sort);
      }
      else {
        $result = $this->get_tips_by_user_with_votes($uname, $sort, $auth_uname);
      }
      
      if (!$result) {
        return null;
      }
      
      // Else yield Tip objects
      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        yield self::tip_from_row($row);
      }
    }
    
    function calculate_user_score($uname) {
      /**
       * Calculates user score as DB operation (SUM) on tips
       */
      $sql = '
        SELECT
          SUM(score)
        FROM
          tips
        WHERE
          author = ?';
      
      $res = $this->prep_and_exec($sql, [$uname]);
      
      return $res ? $res->fetch(PDO::FETCH_NUM)[0] : null;
    }
    
    function get_user_tip_count($uname) {
      /**
       * Return number of tips by user $uname, or null if
       * error/nonexistant user
       */
      $sql = '
        SELECT
          COUNT(*)
        FROM
          tips
        WHERE
          author=?';
      
      $res = $this->prep_and_exec($sql, [$uname]);
      
      return $res ? $res->fetch(PDO::FETCH_NUM)[0] : null;
    }
    
    function user_exists($username) {
      /**
       * Return boolean indication whether user with username 
       * $username exists in db.
       *
       * Return null on DB error.
       */
      $sql = '
        SELECT EXISTS(
          SELECT
            *
          FROM
            users
          WHERE
            uname=?
        )';
      
      $res = $this->prep_and_exec($sql, [$username]);
      
      if (!$res) {
        return null;
      }
      
      return (bool) $res->fetch(PDO::FETCH_NUM)[0];
    }
    
    function user_owns_tip($tip_id, $uname) {
      /**
       * Return boolean indicating whether user $uname is author of tip $tip_id.
       *
       * Return null on DB error or other problem.
       */
      $sql = '
        SELECT EXISTS(
          SELECT
            id
          FROM
            tips
          WHERE
            id = ? AND author = ?
        )';
      
      $res = $this->prep_and_exec($sql, [$tip_id, $uname]);
      
      if (!$res) {
        return null;
      }
      
      return (bool) $res->fetch(PDO::FETCH_NUM)[0];
    }
    
    function vote_tip_up($id, $uname) {
      /**
       * Vote tip with id $id up and register vote
       * by user $uname.
       *
       * Return boolean indicating whether all db operations succeeded.
       */
      $sql = '
        UPDATE
          tips
        SET
          score = score + 1
        WHERE
          id=?
      ';
      
      $upd_res = $this->prep_and_exec($sql, [$id]);
      $ins_res = $this->insert_vote($id, $uname, true);
      
      return ($upd_res && $ins_res);
    }
    
    function vote_tip_down($id, $uname) {
      /**
       * Vote tip with id $id down and register vote
       * by user $uname.
       *
       * Return boolean indicating whether all db operations succeeded.
       */
      $sql = '
        UPDATE
          tips
        SET
          score = score - 1
        WHERE
          id=?
      ';
      
      $upd_res = $this->prep_and_exec($sql, [$id]);
      $ins_res = $this->insert_vote($id, $uname, false);
      
      return ($upd_res && $ins_res);
    }
    
    /*****************************
    * PRIVATE FUNCTIONS
    *****************************/
    private static function add_where($sql, $condition) {
      /**
       * Append condition string $condition as a WHERE clause
       * to SQL.
       *
       * No escaping occurs here. $condition is appended as-is.
       */
      
      return $sql . "
        WHERE
          $condition";
    }
    
    private function get_all_tips($sort='best') {    
      $sql = TipSorter::sort_query(self::SELECT_TIPS, $sort);

      return $this->prep_and_exec($sql, null, true);
    }
    
    private function get_all_tips_with_votes($uname, $sort) {
      $sql = TipSorter::sort_query(
        self::SELECT_TIPS_WITH_VOTES, $sort, 't'
      );
        
      return $this->prep_and_exec($sql, [$uname], true);
    }
    
    private function get_tips_by_tag($tag, $sort) {
      $sql = '
        SELECT
          ' . self::TIP_COLUMNS . '
        FROM
          tips
        INNER JOIN
          tips_tags
        ON
          tips.id = tips_tags.tip_id
        WHERE
          tips_tags.tag_id = ?';
      
      $sql = TipSorter::sort_query($sql, $sort);
      
      return $this->prep_and_exec($sql, [$tag], true);
    }
    
    private function get_tips_by_tag_with_votes($tag, $uname, $sort) {
      $sql = '
        SELECT
          ' . self::TIP_COLUMNS . ', v.type as vote
        FROM
          tips
        INNER JOIN
          tips_tags
        ON
          tips.id = tips_tags.tip_id
        LEFT JOIN votes v ON
          (v.user_id = ?) AND (v.tip_id = tips_tags.tip_id)
        WHERE
          tips_tags.tag_id = ?';
      
      $sql = TipSorter::sort_query($sql, $sort);
      
      return $this->prep_and_exec($sql, [$uname, $tag], true);
    }
    
    private function get_tips_by_topic($topic, $sort) {
      $sql = TipSorter::sort_query(
        self::add_where(self::SELECT_TIPS, 'topic LIKE ?'), 
        $sort
      );

      return $this->prep_and_exec($sql, [$topic], true);
    }
    
    private function get_tips_by_topic_with_votes($topic, $sort, $uname) {
      $sql = TipSorter::sort_query(
        self::add_where(self::SELECT_TIPS_WITH_VOTES, 'topic LIKE ?'), 
        $sort, 
        't'
      );
      
      return $this->prep_and_exec($sql, [$uname, $topic], true);
    }
    
    private function get_tips_by_user($uname, $sort) {
      $sql = TipSorter::sort_query(
        self::add_where(self::SELECT_TIPS, 'author = ?'), 
        $sort
      );

      return $this->prep_and_exec($sql, [$uname], true);
    }
    
    private function get_tips_by_user_with_votes($uname, $sort, $auth_uname) {
      $sql = TipSorter::sort_query(
        self::add_where(self::SELECT_TIPS_WITH_VOTES, 'author = ?'), 
        $sort, 
        't'
      );
      
      return $this->prep_and_exec($sql, [$auth_uname, $uname], true);
    }
    
    private function insert_vote($id, $uname, $type) {
      // normalize $type to sql boolean
      $type = (bool) $type;
      
      $sql = '
        INSERT INTO
          votes (tip_id, user_id, type)
        VALUES
          (?, ?, ?)';
      
      return $this->prep_and_exec($sql, [$id, $uname, $type]);
    }
    
    private static function tips_from_query_result($qRes) {
      $tips = [];
      
      while ($row = $qRes->fetch(PDO::FETCH_ASSOC)) {
        $tips[] = self::tip_from_row($row);
      }
        
      return $tips;
    }
    
    private static function tip_from_row($row) {
      return new Tip(
        $row['id'],
        $row['title'],
        isset($row['author']) ? $row['author'] : null,
        isset($row['body_source']) ? $row['body_source'] : null,
        $row['timestamp'],
        isset($row['edit_timestamp']) ? $row['edit_timestamp'] : null,
        $row['score'],
        $row['topic'],
        isset($row['vote']) ? $row['vote'] : null
      );
    }
  } // End WDWTipsDB
