<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.util
 */

namespace Jerity\Util;

use \Jerity\Core\RenderContext;
use \Jerity\Core\Tag;

/**
 * String utility class.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.util
 */
class String {

  /**
   * This is a non-instantiable utility class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Escapes the provided text for (X)HTML output. If required, a full encoding can be done, which will encode
   * all entities rather than just the five special ones (<kbd>< > ' " &</kbd>).
   *
   * @param  string   $text           The string to be made safe.
   * @param  boolean  $full_encode    Whether to encode all special characters.
   * @param  boolean  $double_encode  Whether to allow an entity to be encoded twice (e.g. "&amp;" -> "&amp;amp;")
   *
   * @return  string
   */
  public static function escapeHTML($text, $full_encode = false, $double_encode = true) {
    if ($full_encode) {
      return htmlentities($text, ENT_QUOTES, 'UTF-8', $double_encode);
    } else {
      return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', $double_encode);
    }
  }

  /**
   * Escapes the provided text for XML output. If required, a full encoding can
   * be done, which will encode all entities numerically, rather than just the
   * five special ones (<kbd>< > ' " &</kbd>) to their named counterparts.
   *
   * @param  string   $text           The string to be made safe.
   * @param  boolean  $full_encode    Whether to encode all special characters.
   * @param  boolean  $double_encode  Whether to allow an entity to be encoded twice (e.g. "&amp;" -> "&amp;amp;")
   *
   * @return  string
   */
  public static function escapeXML($text, $full_encode = false, $double_encode = true) {
    if ($full_encode) {
      # TODO: Need to convert table to return numeric entities.
      #       For now just output bare minimal, i.e. don't do anything
      # http://uk.php.net/manual/en/function.get-html-translation-table.php#54927
      return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', $double_encode);
    } else {
      return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', $double_encode);
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
      return strtr(
        $text,
        array("\n" => "\\n", "\r" => "\\r", '"' => '\\"', "'" => "\\'", '\\' => "\\\\", '</' => "<\\/" )
      );
    } else {
      return strtr(
        $text,
        array("'" => "\\'", '\\' => "\\\\", '</' => "<\\/")
      );
    }
  }

  /**
   * Escapes the provided text according to the type of content being output.
   * The is the option to override to encode for a specific content type.  If
   * required, a full encoding can be done, which in the case of HTML/XHTML
   * will use {@link htmlentities()} instead of {@link htmlspecialchars()}.
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
      $contentType = RenderContext::get()->getContentType();
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
          throw new \InvalidArgumentException('Invalid content type: '.$override);
        }
    }

    return $text;
  }

  /**
   * Makes a string safe for use as a filename.
   *
   * Converts the following characters to an underscore: < > : " \ / | ? *
   * Removes non-printing characters in the range [0..31].
   *
   * @param  string   $filename     The filename to make safe.
   * @param  array    $extra_rules  Character --> Replacement
   * @param  boolean  $reduce       Reduce multiple replaced characters to one.
   *
   * @return  string
   *
   * @todo  Add parameter to select target OS/filsystem.
   */
  public static function escapeFilename($filename, array $extra_rules = array(), $reduce = true) {
    $reserved = str_split('<>:"\\/|?*');
    $nonprint = array_map('chr', range(0, 31));
    $exclude = array_merge($reserved, array_keys($extra_rules), $nonprint);
    $replace = array_merge(array_fill(0, count($reserved), '_'), array_values($extra_rules));
    $filename = str_replace($exclude, $replace, $filename);
    if ($reduce) {
      return preg_replace(array_map(create_function('$c', 'return "/([{$c}])+/";'), array_unique($replace)), '$1', $filename);
    } else {
      return $filename;
    }
  }

  /**
   * Makes a string safe for use as a path.
   *
   * Currently this is just a synonym for escapeFilename().
   *
   * @see  String::escapeFilename()
   *
   * @param  string   $filename     The filename to make safe.
   * @param  array    $extra_rules  Character --> Replacement
   * @param  boolean  $reduce       Reduce multiple replaced characters to one.
   *
   * @return  string
   *
   * @todo  Add parameter to select target OS/filsystem.
   */
  public static function escapePath($path, array $extra_rules = array(), $reduce = true) {
    return self::escapeFilename($path, $extra_rules, $reduce);
  }

  /**
   * Create a natural conjunction of a list of items.
   *
   * Examples (with oxford_comma = <kbd>null</kbd>):<ul>
   *   <li><kbd>array('one')</kbd> --> 'one'</li>
   *   <li><kbd>array('one', 'two')</kbd> --> 'one and two'</li>
   *   <li><kbd>array('one', 'two', 'three')</kbd> --> 'one, two, and three'</li>
   *   <li><kbd>array('one', 'two', 'three', 'four')</kbd> --> 'one, two, three, and four'</li>
   * </ul>
   *
   * Examples (with oxford_comma = <kbd>true</kbd>):<ul>
   *   <li><kbd>array('one')</kbd> --> 'one'</li>
   *   <li><kbd>array('one', 'two')</kbd> --> 'one, and two'</li>
   *   <li><kbd>array('one', 'two', 'three')</kbd> --> 'one, two, and three'</li>
   *   <li><kbd>array('one', 'two', 'three', 'four')</kbd> --> 'one, two, three, and four'</li>
   * </ul>
   *
   * Examples (with oxford_comma = <kbd>false</kbd>):<ul>
   *   <li><kbd>array('one')</kbd> --> 'one'</li>
   *   <li><kbd>array('one', 'two')</kbd> --> 'one and two'</li>
   *   <li><kbd>array('one', 'two', 'three')</kbd> --> 'one, two and three'</li>
   *   <li><kbd>array('one', 'two', 'three', 'four')</kbd> --> 'one, two, three and four'</li>
   * </ul>
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
   * @param  string   $text       The text to truncate.
   * @param  integer  $length     The amount of text to show.
   * @param  boolean  $boundary   Truncate at word boundary
   * @param  boolean  $ellipsis   Whether to show an ellipsis.
   * @param  boolean  $extension  If a file, do we want to keep the extension.
   *
   * @return  string
   *
   * @todo  Preserve HTML tags and entities.
   * @todo  Split into multiple functions: truncate, truncateFilename, truncatePath, ...
   */
  public static function truncate($text, $length, $boundary = true, $ellipsis = true, $extension = false) {
    $ctx = RenderContext::get();

    $text = trim($text);
    if (mb_strlen($text) < $length) return $text;
    if ($extension) {
      $pos = mb_strrpos($text, '.');
      if ($pos !== false) {
        $extn = mb_substr($text, $pos);
        $length -= mb_strlen($extn);
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

    $text = mb_substr($text, 0, $length);
    if ($boundary) {
      $pos = mb_strrpos($text, ' ');
      if ($pos !== false) $text = mb_substr($text, 0, $pos);
    }

    $text .= $tail;
    if ($extension && isset($extn)) $text .= $extn;

    return $text;
  }

  /**
   * Split a camel-cased string into an array of its components.
   *
   * For example, splits "ThisCamelString" into ('This', 'Camel', 'String')
   * and "ACamelString" into ('A', 'Camel', 'String'). Note that this will
   * also cause "renderAsHTML" to be split into ('render', 'As', 'H', 'T',
   * 'M', 'L').
   *
   * @param   string  $str  The camel-cased string to be split.
   *
   * @return  array   The set of components, in order.
   */
  public static function splitCamelCase($str) {
    return preg_split('/(?<=\\w)(?=[A-Z])/', $str);
  }

  /**
   * Split a split-cased string into an array of its components.
   *
   * For example, splits "this_split_string" into ('this', 'split', 'string')
   * and "A_SPLIT_STRING" into ('A', 'SPLIT', 'STRING').
   *
   * @param   string  $str  The split-cased string to be split.
   * @return  array         The set of components, in order.
   */
  public static function splitSplitCase($str) {
    $output = preg_split('/_+/', $str);
    $output = array_merge(array_filter($output, create_function('$a', 'return $a!=="";')));
    if (!count($output)) $output = array('');
    return $output;
  }

  /**
   * Checks whether the string given is an integer.
   *
   * @param  string  $str  The string to check.
   *
   * @return  bool
   */
  public static function isInteger($str) {
    return ($str == (string)(int)$str);
  }

  /**
   * Checks whether the string given is a floating point number.
   *
   * @param  string  $str  The string to check.
   *
   * @return  bool
   */
  public static function isFloat($str) {
    return ($str == (string)(float)$str);
  }

  /**
   * Checks whether the string given is lowercase (i.e. has no uppercase 
   * characters).
   *
   * @param  string  $str  The string to check.
   *
   * @return  bool
   */
  public static function isLower($str) {
    return !preg_match('/\p{Lu}/', $str);
  }

  /**
   * Checks whether the string given is uppercase (i.e. has no lowercase 
   * characters).
   *
   * @param  string  $str  The string to check.
   *
   * @return  bool
   */
  public static function isUpper($str) {
    return !preg_match('/\p{Ll}/', $str);
  }

  /**
   * Checks whether the string given is in title case.
   *
   * @param  string  $str  The string to check.
   *
   * @return  bool
   */
  public static function isTitleCase($str) {
    return !preg_match('/\b\p{Ll}|\b\p{Lu}{2,}|\b\p{Lu}\p{Ll}+\p{Lu}/', $str);
  }

  /**
   * Returns a string with all spaces and non-word characters converted to hyphens
   * (by default), accented characters converted to non-accented characters,
   * and non-word characters removed.
   *
   * @param   string  $string       The string to convert to slug form
   * @param   string  $replacement  The replacement for non-word characters
   *
   * @return  string
   */
  public static function slugify($string, $replacement = '-') {
    $map = array(
      '/à|á|å|â/'   => 'a',
      '/è|é|ê|ẽ|ë/' => 'e',
      '/ì|í|î/'     => 'i',
      '/ò|ó|ô|ø/'   => 'o',
      '/ù|ú|ů|û/'   => 'u',
      '/ä|æ/'       => 'ae',
      '/ö|œ|ø/'     => 'oe',
      '/ü/'         => 'ue',
      '/Ä|Æ/'       => 'Ae',
      '/Ü/'         => 'Ue',
      '/Ö|Œ|Ø/'     => 'Oe',
      '/ß/'         => 'ss',
      '/ç/'         => 'c',
      '/ñ/'         => 'n',
      '/[^\w]+/'    => $replacement,
      '/(?:'.preg_quote($replacement, '/').'){2,}$/' => $replacement,
    );
    return preg_replace(array_keys($map), array_values($map), $string);
  }

  /**
   * Wraps a string in paragraphs.  Wraps double line breaks with <p> and </p>,
   * and replaces single line breaks with <br>.
   *
   * @param   string  $string  The string to convert to paragraphs
   *
   * @return  string
   *
   * @todo  Correctly handle blank lines with horizontal whitespace!
   */
  public static function nl2p($string) {
    $string = '<p>' . trim($string) . '</p>';
    $string = preg_replace("/(?<![\n\r])(?:\r|\n|\r\n)(?![\n\r])/", Tag::br(), $string);
    $string = preg_replace("/(?<![\n\r])[\n\r]{2,}(?![\n\r])/", "</p>\n<p>", $string);
    return $string;
  }

  /**
   * Takes a string or phrase, and pluralizes the last word.  If there is a 
   * number in the phrase, conditionally pluralize the last word based on the 
   * number.
   *
   * @see http://thecodingway.com/blog/robs-tips-and-tricks/php-pluralizing-with-style/
   *
   * @param   string  $string  The string to pluralize.
   *
   * @return  string
   */
  public static function pluralize($string) {
    # Regular expression for matching textual number.
    static $regex0 = '/(?:^|)((?<!point )(?:minus )?one(?! point)).*?(\pL+)\PL*$/';
    # Regular expression for matching normal number.
    static $regex1 = '/(?:^|)(-?(?<!(?:\.|\d))1(?:\.0+)?)?\PN*?(\pL+)\PL*$/';
    # Reguular expression for word replacement.
    static $regex2 = '/(\PL|one |^)(\pL+)(\PL*)$/';
    if (!preg_match($regex0, $string, $match)) {
      if (!preg_match($regex1, $string, $match)) {
        # We had an error matching the phrase, assume we cannot pluralize it.
        return $string;
      }
    }
    array_shift($match);
    list($amount, $word) = $match;
    if ($word && !$amount) {
      $word = Inflector::pluralize($word);
      $string = preg_replace($regex2, "$1$word$3", $string);
    }
    return $string;
  }

  /**
   * Converts the value provided into a formatted string specifying the value 
   * in the desired multiples of bits.
   *
   * @param  mixed  $n       The value to format.
   * @param  bool   $si      Whether to output SI units.
   * @param  int    $dp      How many decimal places to output.
   * @param  int    $prefix  Force a prefix, e.g. 'K', 'M' (null = automatic)
   * @param  bool   $symbol  Whether to output symbols or names of unit.
   *
   * @return  string  The formatted bit value.
   */
  public static function formatBits($n, $si = false, $dp = 0, $prefix = null, $symbol = true) {
    return self::formatBytesOrBits(false, $n, $si, $dp, $prefix, $symbol);
  }

  /**
   * Converts the value provided into a formatted string specifying the value 
   * in the desired multiples of bytes.
   *
   * To force a specific prefix to be returned, and because they vary depending
   * on whether SI units are used or not, the $prefix parameter must be one of 
   * the following values: '', 'K', 'M', 'G', 'T', 'P', 'E', 'Z'.
   *
   * @param  mixed   $n       The value to format.
   * @param  bool    $si      Whether to output SI units.
   * @param  int     $dp      How many decimal places to output.
   * @param  string  $prefix  Force a prefix, e.g. 'K', 'M' (null = automatic)
   * @param  bool    $symbol  Whether to output symbols or names of unit.
   *
   * @return  string  The formatted byte value.
   */
  public static function formatBytes($n, $si = false, $dp = 0, $prefix = null, $symbol = true) {
    return self::formatBytesOrBits(true, $n, $si, $dp, $prefix, $symbol);
  }

  /**
   * Converts the value provided into a formatted string specifying the value 
   * in the desired multiples of bytes or bits.
   *
   * To force a specific prefix to be returned, and because they vary depending
   * on whether SI units are used or not, the $prefix parameter must be one of 
   * the following values: '', 'K', 'M', 'G', 'T', 'P', 'E', 'Z'.
   *
   * @param  bool    $bytes   Whether to format as bytes or bits.
   * @param  mixed   $n       The value to format.
   * @param  bool    $si      Whether to output SI units.
   * @param  int     $dp      How many decimal places to output.
   * @param  string  $prefix  Force a prefix, e.g. 'K', 'M' (null = automatic)
   * @param  bool    $symbol  Whether to output symbols or names of unit.
   *
   * @return  string  The formatted byte value.
   */
  protected static function formatBytesOrBits($bytes, $n, $si = false, $dp = 0, $prefix = null, $symbol = true) {
    $s = '';
    $exponent = null;
    # Parse the value provided first.
    $n = ($bytes ? Number::parseBytes($n) : Number::parseBits($n));
    # Check decimal place is valid.
    $dp = min(max(0, $dp), ini_get('precision'));
    # Get unit prefix list.
    $prefixes = ($si
      ? ($symbol ? Number::$SI_PREFIX_SYMBOL  : Number::$SI_PREFIX_NAME)
      : ($symbol ? Number::$IEC_PREFIX_SYMBOL : Number::$IEC_PREFIX_NAME)
    );
    # Get unit postfix.
    $postfix = ($bytes ? ($symbol ? 'B' : 'byte') : ($symbol ? 'b' : 'bit'));
    # Determine whether to automatically determine the prefix.
    if (!is_null($prefix)) {
      $exponent = array_search(strtoupper($prefix), array('', 'K', 'M', 'G', 'T', 'P', 'E', 'Z'));
      if ($exponent === false) {
        $exponent = null; # Invalid, so just automatically choose.
      }
    }
    # Calculate the correct numeric value.
    $base = $si ? 1000 : 1024;
    if (!is_null($exponent)) {
      $n /= pow($base, $exponent);
    } else {
      $exponent = 0;
      while ($n >= $base && $exponent < count($prefixes)-1) {
        $n /= $base;
        $exponent++;
      }
    }
    # Generate string.
    $n = number_format($n, $dp, nl_langinfo(RADIXCHAR), nl_langinfo(THOUSEP));
    $s = sprintf('%s %s', $n, $prefixes[$exponent].$postfix);
    if (!$symbol) $s = self::pluralize($s);
    return $s;
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
