<?php
  /**
   * tags.php
   * Author: Adam Beagle
   *
   * Utility classes/functions related to tags
   */

  require_once 'db.php';

  class TagManager {
    const LIMIT_PER_TIP = 10;
    
    static function is_valid_request($s) {
      if (!$s) {
        return true;
      }
      
      return preg_match('/^[a-z0-9-]+(?:[+ ][a-z0-9-]+)*$/i', $s);
    }
    
    static function request_to_array($req) {
      /**
       * Given tags in request (of form e.g. 'tag-1 tag2'),
       * return array of normalized individual tags
       */
      $ret = [];
      
      foreach (explode(' ', str_replace('+', ' ', $req)) as $tag) {
        $ret[] = self::normalize($tag);
      }
      
      return $ret;
    }
    
    static function normalize($tag) {
      /**
       * Return $tag lowercased and with invalid characters 
       * (anything non-alphanumeric or hyphen) stripped.
       */
      return strtolower(preg_replace('/[^a-z0-9-]/i', '', $tag));
    }
    
    static function update_tip_tags($tip_id, $tags_req, $current_tags=null, $db=null) {
      $success = true;
      
      $db = self::get_db($db);
      
      if (!isset($current_tags)) {
        $current_tags = iter_tags_by_tip($tip_id);
      }
      
      $requested_tags = self::request_to_array($tags_req);
      
      // Compare current to request
      foreach ($current_tags as $row) {
        $key = array_search($row['id'], $requested_tags);
          
        // If in current but not request, disassociate
        if ($key === false) {
          $success = $success and $db->disassociate_tag($row['id'], $tip_id);
        }
        // Else if present in both, ignore
        else {
          unset($requested_tags[$key]);
        }
      }
      
      // Anything left in request should be added
      foreach ($requested_tags as $tag) {
        $success = $success and $db->create_tag($tag, $tip_id);
      }
      
      return $success;
    }
    
    private static function get_db($db=null) {
      return ($db instanceof WDWTipsDB) ? $db : new WDWTipsDB();
    }
  }
?>