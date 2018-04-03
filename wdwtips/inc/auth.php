<?php
  /**
   * auth.php
   * Author: Adam Beagle
   *
   * Handles user authorization.
   */
  require_once 'db.php';

  class AuthHandler {
    /**
     * Handles user authorization.
     *
     * For now, toy implementation using $_SESSION
     */
    private $user;
    
    function __construct() {
      session_start();
      
      if (isset($_SESSION['user'])) {
        $this->user = $_SESSION['user'];
      }
      else {
        $this->user = null;
      }
    }
    
    function create_user($uname, $pw, $db) {
      /**
       * Assumes $uname has already been chcked for uniqueness.
       */
      $result = $db->create_user(
        $uname, 
        password_hash($pw, PASSWORD_DEFAULT)
      );
      
      if (!$result) {
        return false;
      }
      
      $this->set_user($uname);
      return true;
    }
    
    function get_user() {
      return $this->user;
    }
    
    function login($uname, $pw, $db=null) {
      /**
       * Attempt login of user $uname with password $pw.
       *
       * Return true if login successful, false otherwise.
       *
       * If already connected to the DB, it may be passed as $db, otherwise it will be connected to automatically.
       */
      // Connect to DB if needed
      if (is_null($db)) {
        $db = new WDWTipsDB();
      }
      
      // Get user from db
      $user = $db->get_user($uname);
      
      // Validate login
      if (isset($user) and self::check_password($user, $pw, $db)) {
        $this->set_user($user['uname']);
        return true;
      }
    
      return false;
    }
    
    function logout() {
      $this->user = null;
      unset($_SESSION['user']);
    }
    
    function am_logged_in() {
      return isset($this->user);
    }
    
    private static function check_password($user, $pw, $db) {
      /**
       * User expects row from `users` db table.
       *
       * Return true if password verified, false otherwise.
       *
       * Automatically rehashes password if needed, and updates user db record.
       */
      $hash = $user['pwhash'];
      
      // See if password matches
      if (password_verify($pw, $hash)) {
        
        // Check if password needs rehashing
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
          $db->update_user_pwhash(
            $user['uname'], password_hash($pw, PASSWORD_DEFAULT)
          );
        }
        
        return true;
      }
      
      return false;
    }
    
    private function set_user($uname) {
      $this->user = $uname;
      $_SESSION['user'] = $uname;
    }
  }
?>