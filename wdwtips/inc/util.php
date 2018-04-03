<?php
  /**
   * inc/util.php
   * Author: Adam Beagle
   *
   * Misc. utility functions for wdwtips
   */
  require_once 'config.php';

  $one_time_params = ['logout', 'tip_del'];

  function form_submitted($key='submit') {
    /**
     * Return true if param $key ('submit' by default) is in $_REQUEST
     */
    return array_key_exists($key, $_REQUEST);
  }

  function get_from_array(&$arr, $key, $default=null, $sanitize=false) {
    /**
     * Return value of $arr[key] if present and not empty, otherwise $default.
     * If $sanitized set, sanitize array value (not $default) 
     * with FILTER_SANITIZE_STRING
     */
    if (isset($arr[$key]) && $arr[$key]) {
      if ($sanitize) {
        return filter_var($arr[$key], FILTER_SANITIZE_STRING);
      }
      else {
        return $arr[$key];
      }
    }
    else {
      return $default;
    }
  }

  function get_from_get($key, $default=null) {
    /**
     * Return sanitized value of $_GET[$key] if present and not empty, otherwise $default
     */
    return get_from_array($_GET, $key, $default, true);
  }

  function get_from_request($key, $default=null) {
    /**
     * Return sanitized value of $_REQUEST[$key] if present and not empty, otherwise $default
     */
    return get_from_array($_REQUEST, $key, $default, true);
  }

  function retain_params($key, $val, $blacklist=null) {
    /**
     * Return a query string with '$key=$val' added appropriately, retaining
     * any other parameters that are present in $_GET.
     *
     * Params in iterable $blacklist are not retained. 
     */
    $getCopy = $_GET;
    $getCopy[$key] = $val;
    
    if (is_null($blacklist)) {
      global $one_time_params;
      $blacklist = $one_time_params;
    }
    
    foreach($blacklist as $bk) {
      unset($getCopy[$bk]);
    }
    
    return http_build_query($getCopy);
  }

  function time_since($umc) {
    /**
     * Return human-readable time difference string (e.g. '12 hours ago') 
     * between current time and UserMarkdownContent object $umc.
     */
    $posted = new DateTime('@'.$umc->get_timestamp());
    $now = new DateTime();
    $diff = $now->diff($posted);
    $unit = 'second';
    $num = 0;
    
    if ($diff->y) {
      $num = $diff->y;
      $unit = 'year';
    }
    else if ($diff->m) {
      $num = $diff->m;
      $unit = 'month';
    }
    else if ($diff->d) {
      $num = $diff->d;
      $unit = 'day';
    }
    else if ($diff->h) {
      $num = $diff->h;
      $unit = 'hour';
    }
    else if ($diff->i) {
      $num = $diff->i;
      $unit = 'minute';
    }
    else if ($diff->s) {
      $num = $diff->s;
      $unit = 'second';
    }
    
    if ($num != 1) {
      $unit .= 's';
    }
    
    return "$num $unit ago";
  }

  function terse_msg_and_die($msg) {
    /**
     * Echo barebones site (i.e. header and footer) with $msg for content,
     * then die().
     *
     * Intended only for use with fatal errors in PHP block before 
     * ANY html output.
     */
    include 'header.php';
?>
  <main>
    <p class="err"><?= $msg ?></p>
  </main>
<?php
    include 'footer.php';
    die();
  }
?>