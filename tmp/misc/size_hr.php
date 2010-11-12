<?php
function getSizeHR($size) {
  $kb = 1024;         // Kilobyte
  $mb = 1024 * $kb;   // Megabyte
  $gb = 1024 * $mb;   // Gigabyte
  $tb = 1024 * $gb;   // Terabyte
  if($size < $kb) {
    $filesize = $size." B";
  } else if($size < $mb) {
    $filesize = round($size/$kb,2)."Kb";
  } else if($size < $gb) {
    $filesize = round($size/$mb,2)."Mb";
  } else if($size < $tb) {
    $filesize = round($size/$gb,2)."Gb";
  } else {
    $filesize = round($size/$tb,2)."Tb";
  }
  return $filesize;
}


/**
 * Convert an integer to a human-readable number of bytes.
 *
 * Warning: Uses SI units (power of 1000) by default, which people may find
 * counter-intuitive. This is for compatability with existing code.
 *
 * @param int  $int      The number to use.
 * @param bool $si       Use SI units (powers of 1000) instead of powers of
 *                       1024.
 * @param bool $si_text  Use correct SI notation for powers of 1024 (KiB
 *                       being the SI form for 1024 bytes, as opposed to the
 *                       more common KB).
 * @param bool|int $round  Number of digits after the decimal point, or false
 *                         for no rounding
 * @return string
 */
public static function humanReadableInt($int, $si = true, $si_text = false, $round = false) {
  $int = round($int);
  $base = $si ? 1000 : 1024;
  $unit_base = ($si_text && !$si) ? 'iB' : 'B';
  switch (true) {
  case ($int >= pow($base, 3)):
    $val = ($int / pow($base, 3));
    $suffix = 'G'.$unit_base;
    break;
  case ($int >= pow($base, 2)):
    $val = ($int / pow($base, 2));
    $suffix = 'M'.$unit_base;
    break;
  case ($int >= $base):
    $val = ($int / $base);
    $suffix = 'K'.$unit_base;
    break;
  case ($int < $base):
  default:
    $val = $int;
    $suffix = ' byte'.($val==1?'':'s');
  }
  if ($round !== false) $val = round($val, $round);
  return $val.$suffix;
}


/**
 * Convert a string containing a human-readable number of bytes (in the form
 * "100K" or "42M", i.e. no "B" or "iB" suffix) to an integer.
 *
 * Warning: Uses non-SI units (power of 1024) by default, which differs from
 * ::humanReadableInt(). This is for compatability with existing code.
 *
 * @param string $val The number to use.
 * @param bool   $si  Use SI units (powers of 1000) instead of powers of 1024.
 * @return string
 */
public static function humanReadableStrToInt($val, $si = false) {
  $val = trim($val);
  $last = strtolower(substr($val, -1));
  // doubles can handle larger numbers than ints
  $val = (double)$val;
  $base = $si ? 1000 : 1024;
  switch ($last) {
    // The 'G' modifier is available since PHP 5.1.0
    case 'g':
      $val *= $base;
    case 'm':
      $val *= $base;
    case 'k':
      $val *= $base;
  }
  return $val;
}
