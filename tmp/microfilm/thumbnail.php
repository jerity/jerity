<?php
  /**
   * Thumbnail Image Generator
   * Microfilm -- An Image Gallery Plugin for Joomla! 1.5.x
   *
   * @Version   0.1.0
   * @author    Nick Pope <microfilm [at] nickpope [dot] me [dot] uk>
   * @copyright Copyright (c) 2008 Nick Pope
   *
   * @todo Output error image on failure [?]
   * @todo Allow customisable browser cache expiry time
   * @todo Add option to allow image to expand larger than original dimensions
   * @todo Add option to allow positioning within cropped image.
   */

  # -------------
  # Requirements:
  # -------------
  # Joomla! 1.5.x (required for the gallery, not this script)
  # PHP 5.x
  # GD 2.x

  define('DEBUG', 0);

  if (DEBUG) error_reporting(E_ALL | E_STRICT);

  function dump($header = null) {
    global $method, $maxw, $maxh, $imgsize, $dstx, $dsty, $srcx, $srcy, $dstw, $dsth, $srcw, $srch;
    echo "<pre>\n";
    if ($header) echo "<strong>$header</strong>\n";
    highlight_string(<<<EOT
<?php

  \$method     = '$method';

  \$maxw       = $maxw;
  \$maxh       = $maxh;
  \$imgsize[0] = $imgsize[0];
  \$imgsize[1] = $imgsize[1];

  \$dstx       = $dstx;
  \$dsty       = $dsty;
  \$srcx       = $srcx;
  \$srcy       = $srcy;

  \$dstw       = $dstw;
  \$dsth       = $dsth;
  \$srcw       = $srcw;
  \$srch       = $srch;

?>
EOT
    );
    echo "</pre>\n";
  }

  // Methods
  define('METHOD_SCALE'          , 'scale');
  define('METHOD_SCALE_CONSTRAIN', 'scale_constrain');
  define('METHOD_CROP'           , 'crop');
  define('METHOD_CROP_CONSTRAIN' , 'crop_constrain');

  // Default values to fallback to if invalid value passed in.
  define('DEFAULT_HEIGHT' , 200);
  define('DEFAULT_WIDTH'  , 200);
  define('DEFAULT_QUALITY', 80);
  define('DEFAULT_METHOD', 'scale');

  // Paths
  define('RELATIVE_PATH', '../../../');
  define('CACHE_PATH', '../../../cache/microfilm/');

  // Only resample if the source image had dimensions less than this value.
  // This is to prevent too much CPU overhead.
  define('MAX_RESAMPLE_DIMENSION', 4000);

  // Default background colour
  $background = array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 127);

  // Cache Status
  define('CACHE_IGNORE'    , 0);
  define('CACHE_RETRIEVE'  , 1);
  define('CACHE_REGENERATE', 2);

  // Resizing methods
  $valid_methods = array(METHOD_SCALE, METHOD_SCALE_CONSTRAIN, METHOD_CROP, METHOD_CROP_CONSTRAIN);

  // General options
  ini_set('memory_limit', '64M');

  /**
   * Makes a path absolute on the assumption it is relative to the current
   * script.
   *
   * @param string $path
   * @return string
   */
  function path_absolute($path) {
    $path = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $path;
    return path_tidy($path);
  }

  /**
   * Makes a path tidy by removing current and parent directory
   * components.
   *
   * @param string $path
   * @return string
   */
  function path_tidy($path) {
    // Clean up any additional slashes
    $path = preg_replace('#//+#', '/', $path);
    // Remove '.'
    $path = preg_replace('#/(\./)+#', '/', $path);
    // Remove '..'
    while (strpos($path, '/../'))
      $path = preg_replace('#/[^/]+/\.\./#', '/', $path);
    return $path;
  }

  // Get image location
  if (isset($_GET['img'])) {
    $img = urldecode($_GET['img']);
    $img = RELATIVE_PATH . '/' . $img;
    $img = path_absolute($img);
    if (!(is_file($img) && is_readable($img))) exit;
  } else {
    // Error: No valid image file to generate thumbnail from.
    exit;
  }

  // Get image information.
  $imgsize = getimagesize($img);
  if (!$imgsize[0]) exit;

  // Get image quality
  if (isset($_GET['quality'])) {
    $quality = min(100, max(0, intval($_GET['quality'])));
  } else {
    $quality = DEFAULT_QUALITY;
  }
  // PNG images have a compression factor of 0-9.  Scale appropriately:
  if ($imgsize['mime'] === 'image/png') {
    if ($quality < 100) {
      $quality = min(9, (int) floor((100 - $quality) / 10));
    } else {
      $quality = 0;
    }
  }

  // Get maximum dimensions
  if (isset($_GET['width']) && $_GET['width'] > 0) {
    $dstw = $maxw = intval($_GET['width']);
  } else {
    $dstw = $maxw = DEFAULT_WIDTH;
  }
  if (isset($_GET['height']) && $_GET['height'] > 0) {
    $dsth = $maxh = intval($_GET['height']);
  } else {
    $dsth = $maxh = DEFAULT_HEIGHT;
  }

  // Get image information.
  $imgsize = getimagesize($img);
  if (!$imgsize[0]) exit;

  // Get thumbnail method
  if (isset($_GET['method']) && in_array($_GET['method'], $valid_methods)) {
    $method = $_GET['method'];
  } else {
    $method = DEFAULT_METHOD;
  }

  // Generate cache name
  $imgcache = CACHE_PATH . '/' . md5($img.$maxw.$maxh.$quality.$method) . strtolower(substr($img, strrpos($img, '.')));
  $imgcache = path_absolute($imgcache);

  // Ensure cache directory exists
  $cachedir = dirname($imgcache);
  if (!file_exists($cachedir) && !mkdir($cachedir, 0777, true)) {
    // Error: Couldn't create cache directory.
    exit;
  }
  unset($cachedir);

  // We get from cache by default.  Check if the cached file is wanted and exists.
  if (!isset($_GET['cache']) || (isset($_GET['cache']) && ((int) $_GET['cache']) <> 0)) {
    if (file_exists($imgcache) && is_readable($imgcache)) {
      if (filemtime($imgcache) > filemtime($img)){
        $cache = CACHE_RETRIEVE;
      } else {
        unlink($imgcache);
        $cache = CACHE_REGENERATE;
      }
    } else {
      $cache = CACHE_REGENERATE;
    }
  } else {
    $cache = CACHE_IGNORE;
  }

  if ($cache <> CACHE_RETRIEVE) {
    // Generate required function names
    $type = split('\/', $imgsize['mime']);
    $imgfrom = 'imagecreatefrom' . $type[1];
    $imgout = 'image' . $type[1];
    if ($imgsize[0] < MAX_RESAMPLE_DIMENSION && $imgsize[1] < MAX_RESAMPLE_DIMENSION) {
      $imgresize = 'imagecopyresampled';
    } else {
      $imgresize = 'imagecopyresized';
    }
    function imgout_wrapper(&$image, $filename, $qual) {
      global $imgsize, $imgout;
      switch (true) {
        case ($imgsize['mime'] === 'image/gif'):
        case ($imgsize['mime'] === 'image/png' && version_compare(PHP_VERSION, '5.1.2', '<')):
          $imgout($image, $filename);
          break;
        default:
          $imgout($image, $filename, $qual);
      }
    }

    // Create source image resource
    $src = $imgfrom($img);

    $constrain = 1; // TODO: ADD AS AN OPTION!

    // Determine dimensions and offsets
    $dstx = 0;
    $dsty = 0;
    $srcx = 0;
    $srcy = 0;
    $srcw = imagesx($src);
    $srch = imagesy($src);

    if (DEBUG) echo "<h1>Thumbnail Generator | Debugging View</h1>\n";

    if (DEBUG) dump('BEFORE');

    switch ($method) {
      case METHOD_SCALE:
        $dstx = $dsty = $srcx = $srcy = 0;
        switch (true) {
          case ($srcw <= $maxw && $srch <= $maxh):
            $dstw = $srcw;
            $dsth = $srch;
            break;
          case ($srcw > $maxw && $srch > $maxh && ($srcw - $maxw > $srch - $maxh)): # TODO: FIX
          case ($srcw > $maxw && $srch <= $maxh):
            $dstw = $maxw;
            $dsth = ceil($srch * $dstw / $srcw);
            break;
          case ($srcw > $maxw && $srch > $maxh && ($srcw - $maxw <= $srch - $maxh)): # TODO: FIX
          case ($srcw <= $maxw && $srch > $maxh):
            $dsth = $maxh;
            $dstw = ceil($srcw * $dsth / $srch);
            break;
        }
        break;
      case METHOD_SCALE_CONSTRAIN:
        $srcx = $srcy = 0;
        switch (true) {
          case ($srcw <= $maxw && $srch <= $maxh):
            $dstw = $maxw;
            $dsth = $maxh;
            $dstx = ceil(($maxw - $srcw) / 2);
            $dsty = ceil(($maxh - $srch) / 2);
            break;
          case ($srcw > $maxw && $srch > $maxh && ($srcw - $maxw > $srch - $maxh)):
          case ($srcw > $maxw && $srch <= $maxh):
            $dstw = $maxw;
            $dsth = ceil($srch * $dstw / $srcw);
            $dstx = 0;
            $dsty = ceil(($maxh - $dsth) / 2);
            break;
          case ($srcw > $maxw && $srch > $maxh && ($srcw - $maxw <= $srch - $maxh)):
          case ($srcw <= $maxw && $srch > $maxh):
            $dstw = ceil($srcw * $dsth / $srch);
            $dsth = $maxh;
            $dstx = ceil(($maxw - $dstw) / 2);
            $dsty = 0;
            break;
        }
        break;
      case METHOD_CROP:
        $dstx = $dsty = 0;
        switch (true) {
          case ($srcw <= $maxw && $srch <= $maxh):
            $dstw = $srcw;
            $dsth = $srch;
            $srcx = 0;
            $srcy = 0;
            break;
          case ($srcw > $maxw && $srch > $maxh):
            if ($srcw - $maxw > $srch - $maxh) {
              $dsth = $maxh;
              $dstw = ceil($srcw * $dsth / $srch);
              $srcx = ceil(($dstw - $maxw) / 2 * $srcw / $dstw);
              $srcy = 0;
            } else {
              $dstw = $maxw;
              $dsth = ceil($srch * $dstw / $srcw);
              $srcx = 0;
              $srcy = ceil(($dsth - $maxh) / 2 * $srch / $dsth);
            }
            break;
          case ($srcw > $maxw && $srch <= $maxh):
            $dstw = $srcw;
            $dsth = $srch;
            $srcx = ceil(($srcw - $maxw) / 2);
            $srcy = 0;
            break;
          case ($srcw <= $maxw && $srch > $maxh):
            $dstw = $srcw;
            $dsth = $srch;
            $srcx = 0;
            $srcy = ceil(($srch - $maxh) / 2);
            break;
        }
        break;
      case METHOD_CROP_CONSTRAIN:
        switch (true) {
          case ($srcw <= $maxw && $srch <= $maxh):
            $dstw = $maxw;
            $dsth = $maxh;
            $dstx = ceil(($maxw - $srcw) / 2);
            $dsty = ceil(($maxh - $srch) / 2);
            $srcx = 0;
            $srcy = 0;
            break;
          case ($srcw > $maxw && $srch > $maxh):
            if ($srcw - $maxw > $srch - $maxh) {
              $dsth = $maxh;
              $dstw = ceil($srcw * $dsth / $srch);
              $dstx = 0;
              $dsty = 0;
              $srcx = ceil(($dstw - $maxw) / 2 * $srcw / $dstw);
              $srcy = 0;
            } else {
              $dstw = $maxw;
              $dsth = ceil($srch * $dstw / $srcw);
              $dstx = 0;
              $dsty = 0;
              $srcx = 0;
              $srcy = ceil(($dsth - $maxh) / 2 * $srch / $dsth);
            }
            break;
          case ($srcw > $maxw && $srch <= $maxh):
            $dstw = $srcw;
            $dsth = $srch;
            $dstx = 0;
            $dsty = ceil(($maxh - $srch) / 2);
            $srcx = ceil(($srcw - $maxw) / 2);
            $srcy = 0;
            break;
          case ($srcw <= $maxw && $srch > $maxh):
            $dstw = $srcw;
            $dsth = $srch;
            $dstx = ceil(($maxw - $srcw) / 2);
            $dsty = 0;
            $srcx = 0;
            $srcy = ceil(($srch - $maxh) / 2);
            break;
        }
        break;
      default: # [should not get here]
    }

    // Create destination image resource
    switch ($method) {
      case METHOD_CROP:
        switch (true) {
          case ($srcw <= $maxw && $srch <= $maxh):
            $dst = imagecreatetruecolor($dstw, $dsth);
            break;
          case ($srcw > $maxw && $srch > $maxh):
            $dst = imagecreatetruecolor($maxw, $maxh);
            break;
          case ($srcw > $maxw && $srch <= $maxh):
            $dst = imagecreatetruecolor($maxw, $dsth);
            break;
          case ($srcw <= $maxw && $srch > $maxh):
            $dst = imagecreatetruecolor($dstw, $maxh);
            break;
        }
        break;
      case METHOD_SCALE:
      default:
        $dst = imagecreatetruecolor($dstw, $dsth);
        break;
      case METHOD_CROP_CONSTRAIN:
      case METHOD_SCALE_CONSTRAIN:
        $dst = imagecreatetruecolor($maxw, $maxh);
        break;
    }

    $bg = imagecolorallocatealpha($dst, $background['r'], $background['g'], $background['b'], $background['a']);
    imagefill($dst, 0, 0, $bg);

    // Set additional image properties
    switch ($imgsize['mime']) {
      case 'image/gif':
        imagepalettecopy($dst, $src);
        break;
      case 'image/jpeg':
        break;
      case 'image/png':
        imagepalettecopy($dst, $src);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        break;
      default:
        break;
    }

    if (DEBUG) dump('AFTER');

    // Do image copying/resizing
    if ($srcw < $maxw && $srch < $maxh) {
      imagecopy($dst, $src, $dstx, $dsty, $srcx, $srcy, $srcw, $srch);
    } else {
      $imgresize($dst, $src, $dstx, $dsty, $srcx, $srcy, $dstw, $dsth, $srcw, $srch);
    }

  } // [end if cache not equal to cache_retrieve]

  if (!DEBUG) {
    // Output the headers
    header('Content-Type: ' . $imgsize['mime']);
    header('Content-Disposition: inline; filename=' . basename($imgcache));
    header('Content-Description: auto-generated thumbnail image');
    switch ($cache) {
      case CACHE_RETRIEVE:
        header('Expires: ' . date('r', strtotime('+1 week')));
        header('Last-Modified: ' . date('r', filemtime($imgcache)));
        break;
      case CACHE_REGENERATE:
        header('Expires: ' . date('r', strtotime('+1 week')));
        header('Last-Modified: ' . date('r'));
        break;
      case CACHE_IGNORE:
        header('Expires: ' . date('r', strtotime('-1 week')));
        header('Last-Modified: ' . date('r'));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        break;
    }
  }
  if (DEBUG) ob_start();
  // Output the image
  switch ($cache) {
    case CACHE_RETRIEVE:
      @readfile($imgcache);
      break;
    case CACHE_REGENERATE:
      if (is_writable(dirname($imgcache))) {
        imgout_wrapper($dst, $imgcache, $quality);
      }
    case CACHE_IGNORE:
      imgout_wrapper($dst, null, $quality);
  }
  if (DEBUG) {
    $img64 = base64_encode(ob_get_contents());
    ob_end_clean();
?>
<style type="text/css">
body { background-color: #ddd; }
img  { border: dashed 1px #090; display: block; margin: 0 auto; }
pre  { background-color: #fff; border: dashed 1px #090; display: inline; float: left; margin: 0 2em; padding: 10px; width: 20em; }
h1   { font-family: monospace; font-size: 1.4em; }
</style>
<img src="data:<?php echo $imgsize['mime']; ?>;base64,<?php echo $img64; ?>">
<?php
  }

  // Clean up resources
  imagedestroy($src);
  imagedestroy($dst);

  ini_restore('memory_limit');

  # vim:nowrap:ts=2:et
?>
