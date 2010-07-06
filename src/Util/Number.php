<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.util
 */

namespace Jerity\Util;

/**
 * Number utility class.
 *
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.util
 */
class Number {

  /**
   * SI prefix symbols for units of information.
   *
   * @var  array
   */
  public static $SI_PREFIX_SYMBOL = array('', 'k', 'M', 'G', 'T', 'P', 'E', 'Z');

  /**
   * SI prefix names for units of information.
   *
   * @var  array
   */
  public static $SI_PREFIX_NAME = array('', 'kilo', 'mega', 'giga', 'tera', 'peta', 'exa', 'zetta');

  /**
   * SI multiplier for units of information SI prefixes.
   *
   * @var  array
   */
  public static $SI_MULTIPLIER = array(
    0 => 1e0,  # 10^0  == 1000^0 (2^00 == 1024^0)
    1 => 1e3,  # 10^3  == 1000^1 (2^10 == 1024^1)
    2 => 1e6,  # 10^6  == 1000^2 (2^20 == 1024^2)
    3 => 1e9,  # 10^9  == 1000^3 (2^30 == 1024^3)
    4 => 1e12, # 10^12 == 1000^4 (2^40 == 1024^4)
    5 => 1e15, # 10^15 == 1000^5 (2^50 == 1024^5)
    6 => 1e18, # 10^18 == 1000^6 (2^60 == 1024^6)
    7 => 1e21  # 10^21 == 1000^7 (2^70 == 1024^7)
  );

  /**
   * IEC binary prefix symbols for units of information.
   *
   * @var  array
   */
  public static $IEC_PREFIX_SYMBOL = array('', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei');

  /**
   * IEC binary prefix symbols for units of information.
   *
   * @var  array
   */
  public static $IEC_PREFIX_NAME = array('', 'kibi', 'mebi', 'gibi', 'tebi', 'pebi', 'exbi');

  /**
   * IEC multiplier for units of information IEC binary prefixes.
   *
   * @var  array
   */
  public static $IEC_MULTIPLIER = array(
    0 => 1,                  # 2^00 == 1024^0
    1 => 1024,               # 2^10 == 1024^1
    2 => 1048576,            # 2^20 == 1024^2
    3 => 1073741824,         # 2^30 == 1024^3
    4 => 1099511627776,      # 2^40 == 1024^4
    5 => 1125899906842624,   # 2^50 == 1024^5
    6 => 1152921504606846976 # 2^60 == 1024^6
  );

  /**
   * JEDEC memory standards prefixes for units of information.
   *
   * @var  array
   */
  public static $JEDEC_PREFIX_SYMBOL = array('', 'K', 'M', 'G');

  /**
   * JEDEC memory standards prefixes for units of information.
   *
   * @var  array
   */
  public static $JEDEC_PREFIX_NAME = array('', 'kilo', 'mega', 'giga');

  /**
   * JEDEC multiplier for units of information JEDEC memory standards prefixes.
   *
   * @var  array
   */
  public static $JEDEC_MULTIPLIER = array(
    0 => 1,         # 2^00 == 1024^0
    1 => 1024,      # 2^10 == 1024^1
    2 => 1048576,   # 2^20 == 1024^2
    3 => 1073741824 # 2^30 == 1024^3
  );

  /**
   * Mapping from from roman numerals to numbers.
   *
   * @var  array
   */
  protected static $ROMAN_NUMERAL_MAP = array(
    'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90,
    'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
  );

  /**
   * This is a non-instantiable utility class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Perform an integer comparison on two numbers.
   *
   * @param  int  $a  The first number to be compared.
   * @param  int  $b  The second number to be compared.
   *
   * @return  int  0 if they are equal, -1 if $a < $b, 1 if $a > $b
   */
  public static function intcmp($a, $b) {
    $a = intval($a);
    $b = intval($b);
    return ($a == $b) ? 0 : ( ($a < $b) ? -1 : 1);
  }

  /**
   * Perform a floating-point comparison on two numbers. Note that the epsilon
   * value used is currently 1.0e-8.
   *
   * @param  double  $a  The first number to be compared.
   * @param  double  $b  The second number to be compared.
   *
   * @return  int  0 if they are equal, -1 if $a < $b, 1 if $a > $b
   */
  public static function dblcmp($a, $b) {
    $EPSILON = 1.0e-8;
    $a = doubleval($a);
    $b = doubleval($b);
    return (abs($a - $b)<$EPSILON) ? 0 : ( ($a < $b) ? -1 : 1);
  }

  /**
   * Parses a string specifying a size of information and converts it to bits.
   *
   * @param  string  $s      The string to parse.
   * @param  bool    $jedec  Whether to prefer JEDEC over SI units.
   *
   * @return  mixed  The number of bits.
   *
   * @see  Number::parseBytes()
   */
  public static function parseBits($s, $jedec = false) {
    return self::parseBytes($s, $jedec) * 8;
  }

  /**
   * Parses a string specifying a size of information and converts it to bytes.
   * Expects a string with a value followed by a symbol or named unit with an
   * optional space in between.
   *
   * @param  string  $s      The string to parse.
   * @param  bool    $jedec  Whether to prefer JEDEC over SI units.
   *
   * @return  mixed  The number of bits.
   */
  public static function parseBytes($s, $jedec = false) {
    # Prepare regular expression.
    $symbol = '(?:([kKMGTPEZ])(i)?)?([Bb])?(?:ps)?';
    $name = '('.implode('|', array_unique(array_merge(
      self::$SI_PREFIX_NAME,
      self::$IEC_PREFIX_NAME,
      self::$JEDEC_PREFIX_NAME
    ))).')?(bytes?|bits?)?';
    # Attempt to match the string.
    if (!preg_match('/^(\d+(?:\.\d+)?) *(?:'.$symbol.'|'.$name.')$/', $s, $m)) {
      throw new Exception('Invalid string provided - unable to parse.');
    }
    # The value in the provided units.
    $n = $m[1];
    if (isset($m[5]) && $m[5] || isset($m[2]) && $m[2]) {
      # Check for prefix (by name).
      if (isset($m[5]) && $m[5]) {
        $k = strtolower($m[5]);
        if (in_array($k, self::$IEC_PREFIX_NAME)) {
          $a =& self::$IEC_PREFIX_NAME;
          $x =& self::$IEC_MULTIPLIER;
        } elseif (in_array($k, self::$JEDEC_PREFIX_NAME) && $jedec) {
          $a =& self::$JEDEC_PREFIX_NAME;
          $x =& self::$JEDEC_MULTIPLIER;
        } elseif (in_array($k, self::$SI_PREFIX_NAME)) {
          $a =& self::$SI_PREFIX_NAME;
          $x =& self::$SI_MULTIPLIER;
        }
      }
      # Check for prefix (by symbol).
      if (isset($m[2]) && $m[2]) {
        $k = $m[2];
        if (isset($m[3]) && $m[3] == 'i') {
          $a =& self::$IEC_PREFIX_SYMBOL;
          $x =& self::$IEC_MULTIPLIER;
          $k .= $m[3];
        } elseif ($jedec) {
          $a =& self::$JEDEC_PREFIX_SYMBOL;
          $x =& self::$JEDEC_MULTIPLIER;
        } else {
          $a =& self::$SI_PREFIX_SYMBOL;
          $x =& self::$SI_MULTIPLIER;
        }
      }
      # Find the correct multiplier and apply it.
      $i = array_search($k, $a, true);
      if ($i === false || !isset($x[$i])) {
        throw new Exception('Invalid multiplier: '.$k.' not one of '.implode(', ', $a).'.');
      }
      $n *= $x[$i];
    }
    # Check whether we were provided with bits or bytes - multiply if needed.
    if (isset($m[4]) && $m[4] == 'b' || isset($m[6])
      && substr(strtolower($m[6]), 0, 3) == 'bit') $n /= 8;
    # Return the value.
    return $n;
  }

  /**
   * Converts a number to roman numerals.
   *
   * @param  int  $n  Number to convert to roman numerals.
   *
   * @return  string  The number as roman numerals.
   *
   * @todo  Support bar numerals?
   * @todo  Support medieval numerals?
   * @todo  Support fractional values?
   */
  public static function toRoman($n, $medieval = false, $subtractive = true) {
    $n = intval($n);
    $s = '';
    foreach (self::$ROMAN_NUMERAL_MAP as $r => $v) {
      if (!$subtractive && strlen($r) > 1) continue;
      $c = intval($n / $v);
      $s .= str_repeat($r, $c);
      $n %= $v;
    }
    return $s;
  }

  /**
   * Converts roman numerals to a number.
   *
   * @param  string  $s  Roman numerals to convert to number.
   *
   * @return  int  The roman numerals as a number.
   *
   * @todo  Support bar numerals?
   * @todo  Support medieval numerals?
   * @todo  Support fractional values?
   * @todo  Nicer code?
   * @todo  http://pear.php.net/manual/en/package.numbers.numbers-roman.romantoarabic.php
   * @todo  http://en.wikipedia.org/wiki/Roman_numerals
   */
  public static function fromRoman($s, $medieval = false) {
    if ($s == '') return 0;
    $n = 0;
    $a = str_split(strtoupper($s));
    for ($i = 0; $i < count($a); $i++) {
      switch ($a[$i]) {
        //case 'N': case '_M': $n += 1000000; break;
        //case 'O': case '_D': $n += 500000; break;
        //case 'Q': case '_C': $n += 100000; break;
        //case 'P': case '_L': $n += 50000; break;
        //case 'R': case '_X': $n += 10000; break;
        //case 'S': case '_V': $n += 5000; break;
        case 'M': $n += 1000; break;
        case 'D': $n += 500; break;
        case 'C':
          switch ($a[$i+1]) {
            case 'M': $n += 900; break;
            case 'D': $n += 400; break;
            default:  $n += 100; break 2;
          }
          $i++; break;
        case 'L': $n += 50; break;
        case 'X':
          switch ($a[$i+1]) {
            case 'M': $n += 990; break;
            case 'D': $n += 490; break;
            case 'C': $n += 90; break;
            case 'L': $n += 40; break;
            default:  $n += 10; break 2;
          }
          $i++; break;
        case 'V': $n += 5; break;
        case 'I':
          switch ($a[$i+1]) {
            case 'M': $n += 999; break;
            case 'D': $n += 499; break;
            case 'C': $n += 99; break;
            case 'L': $n += 49; break;
            case 'X': $n += 9; break;
            case 'V': $n += 4; break;
            default:  $n += 1; break 2;
          }
          $i++; break;
        default:
          throw new JerityException('Invalid character in roman numeral.');
      }
    }
    return $n;
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
