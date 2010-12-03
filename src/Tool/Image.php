<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

/**
 * Image manipulation class for scaling, cropping, adding borders, etc.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 *
 * @todo  Add support for additional formats.
 */
class Image implements Renderable {

  ########################################
  # Constants: Formats {{{

  /**
   * The GIF image format extension.
   *
   * @var  string
   */
  const EXT_GIF = '.gif';

  /**
   * The JPEG image format extension.
   *
   * @var  string
   */
  const EXT_JPG = '.jpg';

  /**
   * The PNG image format extension.
   *
   * @var  string
   */
  const EXT_PNG = '.png';

  /**
   * The GIF image format MIME-type.
   *
   * @var  string
   */
  const MIME_GIF = 'image/gif';

  /**
   * The JPEG image format MIME-type.
   *
   * @var  string
   */
  const MIME_JPG = 'image/jpeg';

  /**
   * The PNG image format MIME-type.
   *
   * @var  string
   */
  const MIME_PNG = 'image/png';

  # }}} Constants: Formats
  ########################################

  ########################################
  # Constants: Defaults {{{

  /**
   * The default height.
   *
   * @var  int
   */
  const DEFAULT_HEIGHT = 200;

  /**
   * The default width.
   *
   * @var  int
   */
  const DEFAULT_WIDTH = 200;

  /**
   * The default file extension.
   *
   * @var  string
   */
  const DEFAULT_EXTENSION = self::EXT_PNG;

  /**
   * The default file MIME-type.
   *
   * @var  string
   */
  const DEFAULT_MIME = self::MIME_PNG;

  # }}} Constants: Defaults
  ########################################

  ########################################
  # Fields: Dimensions {{{

  /**
   * The maximum image height.
   *
   * @var  int
   */
  protected $maxh = self::DEFAULT_HEIGHT;

  /**
   * The maximum image width.
   *
   * @var  int
   */
  protected $maxw = self::DEFAULT_WIDTH;

  /**
   * The source image height.
   *
   * @var  int
   */
  protected $srch = self::DEFAULT_HEIGHT;

  /**
   * The source image width.
   *
   * @var  int
   */
  protected $srcw = self::DEFAULT_WIDTH;

  /**
   * The destination image height.
   *
   * @var  int
   */
  protected $dsth = self::DEFAULT_HEIGHT;

  /**
   * The destination image width.
   *
   * @var  int
   */
  protected $dstw = self::DEFAULT_WIDTH;

  # }}} Fields: Dimensions
  ########################################

  ########################################
  # Fields: Files {{{

  /**
   * The destination image path.
   *
   * @var  string
   */
  protected $dst_path = null;

  /**
   * The source image path.
   *
   * @var  string
   */
  protected $src_path = null;

  /**
   * The destination image extension.
   *
   * @var  string
   */
  protected $dst_ext = self::DEFAULT_EXTENSION;

  /**
   * The source image extension.
   *
   * @var  string
   */
  protected $src_ext = null;

  /**
   * The destination image MIME-type.
   *
   * @var  string
   */
  protected $dst_mime = self::DEFAULT_MIME;

  /**
   * The source image MIME-type.
   *
   * @var  string
   */
  protected $src_mime = null;

  # }}} Fields: Files
  ########################################

  ########################################
  # Fields: Resources {{{

  /**
   * A handle to the destination image resouce.
   *
   * @var  resource
   */
  protected $dst = null;

  /**
   * A handle to the source image resouce.
   *
   * @var  resource
   */
  protected $src = null;

  # }}} Fields: Resources
  ########################################

  /**
   * The manipulation queue containing actions to be performed on the image.
   *
   * @todo  Use PHP 5.3 SplQueue.
   *
   * @var  array
   */
  protected $manipulations = array();

  /**
   * Contains information about the supported features of image manipulation 
   * libraries/extensions.
   *
   * @var  array
   */
  protected static $support = array();

  /**
   * Creates a new image manipulation object.
   */
  public function __construct() {
    # Force support array to be populated.
    self::checkSupport();
  }

  /**
   * Destroys the image manipulation object.
   *
   * Performs cleanup of allocated resources.
   *
   * @see  self::cleanup()
   */
  public function __destruct() {
    $this->cleanup();
  }

  /**
   * Checks various image-related extensions to determine what is supported.
   *
   * @param  bool  $recheck  Whether to recheck for support or not.  By default 
   *                         we only look up data once.
   *
   * @return  array  Information about supported extensions.
   */
  public static function checkSupport($recheck = false) {
    if (!$recheck && self::$support) return self::$support;
    # Check GD extension
    $gd = extension_loaded('gd');
    if ($gd) {
      $gd = array();
      $v = gd_info();
      $gd['bundled'] = (bool) GD_BUNDLED;
      # Version
      if (version_compare(PHP_VERSION, '5.2.4', '>=')) {
        $gd['version']['string']  = GD_VERSION;
        $gd['version']['major']   = GD_MAJOR_VERSION;
        $gd['version']['minor']   = GD_MINOR_VERSION;
        $gd['version']['release'] = GD_RELEASE_VERSION;
        $gd['version']['extra']   = GD_EXTRA_VERSION;
      } elseif (isset($v['GD Version'])) {
        preg_match('/^.*\(((\d+)\.(\d+)\.(\d+)) .*\).*/', $v['GD Version'], $m);
        $gd['version']['string']  = (isset($m[1]) ? $m[1] : null);
        $gd['version']['major']   = (isset($m[2]) ? intval($m[2]) : null);
        $gd['version']['minor']   = (isset($m[3]) ? intval($m[3]) : null);
        $gd['version']['release'] = (isset($m[4]) ? intval($m[4]) : null);
        $gd['version']['extra']   = null;
      }
      # Format Support
      $gd['formats']['gif']  = (bool) (imagetypes() & IMG_GIF);
      $gd['formats']['jpg']  = (bool) (imagetypes() & IMG_JPG);
      $gd['formats']['png']  = (bool) (imagetypes() & IMG_PNG);
      $gd['formats']['wbmp'] = (bool) (imagetypes() & IMG_WBMP);
      $gd['formats']['xpm']  = (bool) (imagetypes() & IMG_XPM);
      $gd['formats']['xbm']  = (isset($v['XBM Support']) ? $v['XBM Support'] : false);
      # Font Support
      $gd['fonts']['freetype'] = (isset($v['FreeType Support']) ? $v['FreeType Support'] : false);
      $gd['fonts']['type1']    = (isset($v['T1Lib Support']) ? $v['T1Lib Support'] : false);
      $gd['fonts']['jis']      = (isset($v['JIS-mapped Japanese Font Support']) ? $v['JIS-mapped Japanese Font Support'] : false);
    }
    # Check ImageMagick extension
    $im = extension_loaded('imagick');
    if ($im) {
      $im = array();
      $imagick = new Imagick();
      $v = $imagick->getVersion();
      preg_match('/^[^ ]* ((\d+)\.(\d+)\.(\d+)(?:-(\d+))?) .*/', $v['versionString'], $m);
      $im['version']['string']  = (isset($m[1]) ? $m[1] : null);
      $im['version']['major']   = (isset($m[2]) ? intval($m[2]) : null);
      $im['version']['minor']   = (isset($m[3]) ? intval($m[3]) : null);
      $im['version']['release'] = (isset($m[4]) ? intval($m[4]) : null);
      $im['version']['extra']   = (isset($m[5]) ? intval($m[5]) : null);;
      $v = $imagick->queryFormats();
      $im['formats']['gif']  = in_array('GIF',  $v);
      $im['formats']['jpg']  = in_array('JPG',  $v);
      $im['formats']['png']  = in_array('PNG',  $v);
      $im['formats']['wbmp'] = in_array('WBMP', $v);
      $im['formats']['xpm']  = in_array('XPM',  $v);
      $im['formats']['xbm']  = in_array('XBM',  $v);
      # XXX: What about other formats?
      if ($imagick) $imagick->destroy();
    }
    # Check ImageMagick extension
    $exif = extension_loaded('exif');
    # Done
    self::$support = array('gd' => $gd, 'im' => $im, 'exif' => $exif);;
    return self::$support;
  }

  /*
   * Create a new image in a fluent API manner.
   *
   * @return  Image
   * @see     self::__construct()
   *
   * @todo  Replace with PHP 5.3 late static binding support?
   */
  public static function create() {
    return new Image();
  }

  /**
   * Clean up image resources and restore modified settings.
   */
  protected function cleanup() {
    # Clean up outstanding resources.
    if (is_resource($this->dst)) imagedestroy($this->dst);
    if (is_resource($this->src)) imagedestroy($this->src);
    # Restore the original memory limit.
    ini_restore('memory_limit');
  }

  ########################################
  # Functions: Configuration {{{

  /**
   * Gets the desired format of the image.
   */
  public function getFormat() {
    return $this->dst_mime;
  }

  /**
   * Sets the desired format of the image.
   *
   * @return  Image  Fluent API
   *
   * @todo
   */
  public function setFormat($f) {
    //if () {
    //  $this->dst_ext =;
    //  $this->dst_mime =;
    //} else {
    //  throw new ImageException('Invalid or unsupported file format.', ImageException::INVALID_PARAMETER);
    //}
    return $this;
  }

  /**
   * Gets the desired height of the image.
   */
  public function getHeight() {
    return $this->maxh;
  }

  /**
   * Sets the desired height of the image.
   *
   * @return  Image  Fluent API
   */
  public function setHeight($h) {
    if (is_int($h) && $h > 0) {
      $this->maxh = $h;
    } else {
      throw new ImageException('Height must be a positive integer in pixels.', ImageException::INVALID_PARAMETER);
    }
    return $this;
  }

  /**
   * Gets the desired width of the image.
   */
  public function getWidth() {
  }

  /**
   * Sets the desired width of the image.
   *
   * @return  Image  Fluent API
   */
  public function setWidth($w) {
    if (is_int($w) && $w > 0) {
      $this->maxw = $w;
    } else {
      throw new ImageException('Width must be a positive integer in pixels.', ImageException::INVALID_PARAMETER);
    }
    return $this;
  }

  # }}} Functions: Configuration
  ########################################

  ########################################
  # Functions: Manipulation {{{

  /**
   * Adds a resize operation to the manipulation queue.
   *
   * @return  Image  Fluent API
   *
   * @todo
   */
  public function resize() {
    //$this->manipulations[] =;
    return $this;
  }

  /**
   * Adds a crop operation to the manipulation queue.
   *
   * @return  Image  Fluent API
   *
   * @todo
   */
  public function crop() {
    //$this->manipulations[] =;
    return $this;
  }

  /**
   * Adds a rotate operation to the manipulation queue.
   *
   * @return  Image  Fluent API
   *
   * @todo
   */
  public function rotate() {
    //$this->manipulations[] =;
    return $this;
  }

  # }}} Functions: Manipulation
  ########################################

  ########################################
  # Functions: Output {{{

  /**
   * Causes the image to be generated and saves the image to a file.
   *
   * @param  string  $f  The path to save the file at.
   *
   * @todo
   */
  public function save($f) {
  }

  /**
   * Causes the image to be generated and outputs the file to the current 
   * output buffer.
   *
   * @todo
   */
  public function render() {
    $this->outputHeaders();
    $this->outputContent();
  }

  /**
   * Outputs the HTTP headers to send with the image content.
   *
   * @todo
   */
  protected function outputHeaders() {
    if (headers_sent()) throw new ImageException('Headers already sent!');
    header('Content-Type: ' . $this->dst_mime);
    //header('Content-Disposition: inline; filename=' . basename($this->cache_file));
    header('Content-Description: auto-generated image');
    //switch ($this->cache_mode) {
    //  case self::CACHE_MODE_IGNORE:
    //    header('Expires: ' . date('r', strtotime('-1 week')));
    //    header('Last-Modified: ' . date('r'));
    //    header('Cache-Control: no-store, no-cache, must-revalidate');
    //    header('Cache-Control: post-check=0, pre-check=0', false);
    //    header('Pragma: no-cache');
    //    break;
    //  case self::CACHE_MODE_RETRIEVE:
    //    header('Expires: ' . date('r', strtotime('+1 week')));
    //    header('Last-Modified: ' . date('r', filemtime($this->cache_file)));
    //    break;
    //  case self::CACHE_MODE_REGENERATE:
    //    header('Expires: ' . date('r', strtotime('+1 week')));
    //    header('Last-Modified: ' . date('r'));
    //    break;
    //  default: # [should not get here]
    //    throw new ImageException('Invalid cache mode.');
    //}
  }

  /**
   * Outputs the image content.
   *
   * @todo
   */
  protected function outputContent() {
    //if (!$this->generated) $this->generate();
    //switch ($this->cache_mode) {
    //  case CACHE_MODE_RETRIEVE:
    //    if (is_readable($this->cache_file)) {
    //      Error::warnOnSuppression(false);
    //      @readfile($this->cache_file);
    //    } else {
    //      throw new ImageException('Cache file cannot be read.', ImageException::PERMISSIONS_ERROR);
    //    }
    //    break;
    //  case CACHE_MODE_REGENERATE:
    //    if (is_writable(dirname($this->cache_file))) {
    //      imgout_wrapper($this->dst, $this->cache_file, $this->quality);
    //    } else {
    //      throw new ImageException('Cache directory cannot be written to.', ImageException::PERMISSIONS_ERROR);
    //    }
    //    # [fall through to display the image]
    //  case CACHE_MODE_IGNORE:
    //    imgout_wrapper($this->dst, null, $this->quality);
    //    break;
    //  default: # [should not get here]
    //    throw new ImageException('Invalid cache mode.');
    //}
  }

  # }}} Functions: Output 
  ########################################

}
