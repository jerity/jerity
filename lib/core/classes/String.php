<?php
/**
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * String utility class.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class String {

  /**
   * This is a non-instantiable utility class.
   */
  protected function __construct() {
  }

  /**
   * Escapes the provided text for (X)HTML output. If required, a full encoding can be done, which will encode
   * all entities rather than just the five special ones (< > ' " &).
   *
   * @param  string   $text         The string to be made safe.
   * @param  boolean  $full_encode  Whether to encode all special characters.
   *
   * @return  string
   */
  public static function escapeHTML($text, $full_encode = false) {
    if ($full_encode) {
      return htmlentities($text, ENT_QUOTES, 'UTF-8', false);
    } else {
      return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
    }
  }

  /**
   * Escapes the provided text for XML output. If required, a full encoding can
   * be done, which will encode all entities numerically, rather than just the
   * five special ones (< > ' " &) to their named counterparts.
   *
   * @param  string   $text         The string to be made safe.
   * @param  boolean  $full_encode  Whether to encode all special characters.
   *
   * @return  string
   */
  public static function escapeXML($text, $full_encode = false) {
    if ($full_encode) {
      # TODO: Need to convert table to return numeric entities.
      #       For now just output bare minimal, i.e. don't do anything
      # http://uk.php.net/manual/en/function.get-html-translation-table.php#54927
      return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
    } else {
      return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
    }
  }

  /**
   * Escapes the provided text for JavaScript output. This will make the string
   * safe for inclusion between double quotes by default.
   *
   * @param  string   $text          The string to be made safe.
   * @param  boolean  $double_quote  Whether to make safe for single or double quotes.
   *
   * @return  string
   */
  public static function escapeJS($text, $double_quote = true) {
    if ($double_quote) {
      return str_replace(
        array("\n", "\r", '"', "'"),
        array('\\n', '\\r', '\\"', "\\'"),
        $text
      );
    } else {
      return str_replace(
        array("'"),
        array("\\'"),
        $text
      );
    }
  }


  /**
   * Escapes the provided text according to the type of content being output.
   * The is the option to override to encode for a specific content type.  If
   * required, a full encoding can be done, which in the case of HTML/XHTML
   * will use htmlentities() instead of htmlspecialchars().
   *
   * Note that this function will escape JavaScript for double quotes rather
   * than single.
   *
   * @param  string   $text         The string to be made safe.
   * @param  string   $override     A RenderContext content constant.
   * @param  boolean  $full_encode  Whether to encode all special characters.
   *
   * @return  string
   */
  public static function escape($text, $override = null, $full_encode = false) {
    if (is_null($override)) {
      $contentType = RenderContext::getGlobalContext()->getContentType();
    } else {
      $contentType = $override;
    }
    switch ($contentType) {
      case RenderContext::CONTENT_HTML:
      case RenderContext::CONTENT_XHTML:
        $text = self::escapeHTML($text, $full_encode);
        break;
      case RenderContext::CONTENT_XML:
      case RenderContext::CONTENT_RSS:
      case RenderContext::CONTENT_ATOM:
        $text = self::escapeXML($text, $full_encode);
        break;
      case RenderContext::CONTENT_JS:
      case RenderContext::CONTENT_JSON:
        $text = self::escapeJS($text);
        break;
      default:
        if (!is_null($override)) {
          // only throw an exception if we have an invalid content type explicitly specified
          throw new InvalidArgumentException('Invalid content type: '.$override);
        }
    }

    return $text;
  }

  /**
   * Create a natural conjunction of a list of items.
   *
   * Examples (with oxford_comma = null):
   *   array('one') --> 'one'
   *   array('one', 'two') --> 'one and two'
   *   array('one', 'two', 'three') --> 'one, two, and three'
   *   array('one', 'two', 'three', 'four') --> 'one, two, three, and four'
   *
   * Examples (with oxford_comma = true):
   *   array('one') --> 'one'
   *   array('one', 'two') --> 'one, and two'
   *   array('one', 'two', 'three') --> 'one, two, and three'
   *   array('one', 'two', 'three', 'four') --> 'one, two, three, and four'
   *
   * Examples (with oxford_comma = false):
   *   array('one') --> 'one'
   *   array('one', 'two') --> 'one and two'
   *   array('one', 'two', 'three') --> 'one, two and three'
   *   array('one', 'two', 'three', 'four') --> 'one, two, three and four'
   *
   * @param  array    $list          The list of items to join.
   * @param  boolean  $oxford_comma  Whether to force the "Oxford comma" on or off.
   * @param  string   $joiner        Word to use (default = 'and').
   *
   * @return  string
   */
  public static function naturalConjunction(array $list, $oxford_comma = null, $joiner = null) {
    if (count($list) == 1) return $list[0];

    if (is_null($oxford_comma)) { # hybrid style
      $oxford_comma = (count($list) != 2);
    }

    if (is_null($joiner)) {
      $joiner = 'and';
    }
    $joiner = ' '.trim($joiner).' ';

    $last_item = array_pop($list);

    return implode(', ', $list).($oxford_comma ? ',' : '').$joiner.$last_item;
  }

  /**
   * Truncates a string to a specified length.  Can choose whether to have an
   * ellipsis displayed. Can also preserve the extension of a filename, which
   * will force the ellipsis to prevent confusion.
   *
   * @todo  Preserve HTML tags and entities.
   *
   * @param  string   $text       The text to truncate.
   * @param  integer  $length     The amount of text to show.
   * @param  boolean  $boundary   Truncate at word boundary
   * @param  boolean  $ellipsis   Whether to show an ellipsis.
   * @param  boolean  $extension  If a file, do we want to keep the extension.
   *
   * @return  string
   */
  public static function truncate($text, $length, $boundary = true, $ellipsis = true, $extension = false) {
    $ctx = RenderContext::getGlobalContext();

    $text = trim($text);
    if (strlen($text) < $length) return $text;
    if ($extension) {
      $pos = strrpos($text, '.');
      if ($pos !== false) {
        $extn = substr($text, $pos);
        $length -= strlen($extn);
      }
    }

    $tail = '';
    if ($ellipsis || $extension) {
      switch ($ctx->getLanguage()) {
        case RenderContext::LANG_HTML:
        case RenderContext::LANG_XHTML:
        case RenderContext::LANG_XML:
          $length -= 1;
          $tail = '&#8230;';
          break;
        default:
          $length -= 3;
          $tail = '...';
      }
    }

    $text = substr($text, 0, $length);
    if ($boundary) {
      $pos = strrpos($text, ' ');
      if ($pos !== false) $text = substr($text, 0, $pos);
    }

    $text .= $tail;
    if ($extension && isset($extn)) $text .= $extn;

    return $text;
  }

}
