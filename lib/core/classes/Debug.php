<?php
/**
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Debugging class providing useful methods for diagnosing problems and
 * performance.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Debug {

  /**
   * If true, debugging output is enabled.
   *
   * @var  boolean
   */
  protected static $enabled = false;

  /**
   * If true, the redirects will be paused if debugging is enabled.
   *
   * @var  boolean
   */
  protected static $pause_redirect = false;

  /**
   * Non-instantiable class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() {
  }
  // @codeCoverageIgnoreEnd

  ##############################################################################
  # debugging control {{{

  /**
   * Checks if debugging is enabled.
   *
   * @return  boolean
   */
  public static function isEnabled() {
    return self::$enabled;
  }

  /**
   * Enables or disables debugging.  This includes:
   *
   *  - Toggling xdebug
   *  - Toggling error display
   *
   * @param  boolean  $enabled  true to enable debugging, false to disable.
   */
  public static function setEnabled($enabled) {
    self::$enabled = (boolean) $enabled;
    if ($enabled) {
      if (extension_loaded('xdebug')) xdebug_enable();
      ini_set('display_errors', 'on');
    } else {
      if (extension_loaded('xdebug')) xdebug_disable();
      ini_set('display_errors', 'off');
    }
  }

  /**
   * Checks or sets whether to pause on redirect.
   *
   * @param  bool  $pause  Whether to pause on redirect.
   *
   * @return  bool
   */
  public static function pauseOnRedirect($pause = null) {
    if (!is_null($pause) && is_bool($pause)) {
      self::$pause_redirect = $pause;
    }
    return self::$pause_redirect;
  }

  # }}} debugging control
  ##############################################################################

  ##############################################################################
  # logging/message tools {{{

  /**
   * Outputs a comment based on the current render context into the document.
   *
   * @todo  Allow single line comments with // and #
   *
   * @param  string  $text  The debugging text to output.
   */
  public static function comment($text) {
    if (!self::$enabled) return;
    $ctx = RenderContext::get();
    switch ($ctx->getLanguage()) {
      case RenderContext::LANG_FBML:
      case RenderContext::LANG_HTML:
      case RenderContext::LANG_MHTML:
      case RenderContext::LANG_WML:
      case RenderContext::LANG_XHTML:
      case RenderContext::LANG_XML:
        $comment_open  = '<!--';
        $comment_close = '-->';
        break;
      case RenderContext::LANG_FBJS:
      case RenderContext::LANG_JSON:
        $comment_open  = '/*';
        $comment_close = '*/';
        break;
      case RenderContext::LANG_TEXT:
      default:
        # Don't know how to handle this...
        return;
    }
    $multiline = (strpos($text, PHP_EOL) !== false);
    echo $comment_open, ($multiline ? PHP_EOL : ' ');
    echo $text;
    echo ($multiline ? PHP_EOL : ' '), $comment_close, PHP_EOL;
  }

  /**
   * Outputs a block containing the data into the document.
   *
   * @todo  Tidy this up.
   * @todo  Formatting and highlighting without xdebug.
   *
   * @param  mixed    $data       The debugging data to output.
   * @param  boolean  $highlight  Whether the data should be highlighted.
   * @param  boolean  $collapsed  Should the debug block be collapsed initially
   */
  public static function out($data, $highlight = true, $collapsed = false) {
    if (!self::$enabled) return;
    static $count = 0;
    $id = '__debug'.$count;
    echo PHP_EOL;
    echo '<div id="'.$id.'" style="background: #fed; border: solid 2px #edc; font-size: 12px; margin: 1em; padding: 0.3em; width: auto;">';
    echo '<div style="background: #edc; overflow: hidden; padding: 0.3em;">';
    echo '<span style="font-weight: bold;">Debug</span>';
    echo '<div style="float: right; font-size: 10px;">( ';
    $style = 'cursor: pointer; text-decoration: underline;';
    if (!$highlight) {
      echo '<span style="'.$style.'" onclick="document.getElementById(\''.$id.'_data\').select();">Select All</span> | ';
    }
    echo '<span style="'.$style.'" onclick="var e = document.getElementById(\''.$id.'_data\'); if (e.style.display == \'none\') { e.style.display = \'block\'; this.innerHTML = \'Collapse\'; } else { e.style.display = \'none\'; this.innerHTML = \'Expand\'; }">'.($collapsed ? 'Expand' : 'Collapse').'</span> | ';
    echo '<span style="'.$style.'" onclick="var e = document.getElementById(\''.$id.'\'); e.parentNode.removeChild(e);">Remove</span>';
    echo ' )</div>';
    if (extension_loaded('xdebug')) {
      printf('<span style="display: block; margin-top: 0.3em; white-space: nowrap;">%s:%s in %s::%s()</span>',
        str_replace($_SERVER['DOCUMENT_ROOT'].'/', '', xdebug_call_file()),
        xdebug_call_line(),
        xdebug_call_class(),
        xdebug_call_function()
      );
    } else {
      # TODO: Formatting and highlighting without xdebug.
    }
    echo '</div>';
	echo '<pre style="background: none; border: none; margin: none; padding: none;">';
    $style = 'background: none; border: none; margin-top: 0.3em; max-height: 150px; width: 100%;';
    if ($collapsed) $style .= ' display: none;';
    if ($highlight) {
      echo '<div id="'.$id.'_data" style="'.$style.' font-family: monospace; max-height: 150px; overflow: auto; white-space: pre;">';
	  # TODO: Need to escape data without clobbering highlight/xdebug modifications.
      var_dump($data);
      echo '</div>';
    } else {
      echo '<textarea cols="80" rows="8" id="'.$id.'_data" style="'.$style.'">';
      ob_start();
      var_dump($data);
      $data = ob_get_clean();
      echo strip_tags($data);
      echo '</textarea>';
    }
	echo '</pre>';
    echo '</div>';
    echo PHP_EOL, PHP_EOL;
    $count++;
  }

  /**
   * Outputs a message to a log file.
   *
   * @todo  Implement this method...
   *
   * @param  mixed  $message  The debugging message to log.
   */
  public static function log($message) {
    if (!self::$enabled) return;
    # TODO: Implement...
  }

  # }}} logging/message tools
  ##############################################################################

  ##############################################################################
  # profiling tools {{{

  /**
   * When the currently running profile was started.
   *
   * @var  double  $profile_start
   */
  protected static $profile_start = null;

  /**
   * Starts profiling the page generation from this point.
   *
   * @todo  Support improved profiling - currently only outputs - need to store.
   */
  public static function startProfiling() {
    if (!self::$enabled) return;
    if (!is_null(self::$profile_start)) return;
    self::$profile_start = microtime(true);
    self::comment(__CLASS__.': Started Profiling ~ Date: '.date('r'));
  }

  /**
   * Stops profiling the page generation at this point.
   */
  public static function stopProfiling() {
    if (!self::$enabled) return;
    $profile_stop = microtime(true);
    if (is_null(self::$profile_start)) return;
    $profile_start = self::$profile_start;
    self::$profile_start = null;
    $elapsed = $profile_stop - $profile_start;
    self::comment(__CLASS__.': Stopped Profiling ~ Time Elapsed: '.$elapsed.'s');
  }

  /**
   * When profiling the page generation, mark an additional measurement at this
   * point.
   */
  public static function mark() {
    if (!self::$enabled) return;
    $profile_mark = microtime(true);
    if (is_null(self::$profile_start)) return;
    $profile_start = self::$profile_start;
    $elapsed = $profile_mark - $profile_start;
    self::comment(__CLASS__.': Profiling Mark ~ Time Elapsed: '.$elapsed.'s');
  }

  # }}} profiling tools
  ##############################################################################

}
