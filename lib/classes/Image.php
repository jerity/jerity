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
 */
class Image {

  /**
   *
   */
  const METHOD_SCALE = 0;

  /**
   *
   */
  const METHOD_CROP  = 1;


  /**
   *
   */
  const BEHAVIOUR_NORMAL  = 0;

  /**
   *
   */
  const BEHAVIOUR_EXPAND  = 1;

  /**
   *
   */
  const BEHAVIOUR_FIXED   = 2;


  /**
   *
   */
  const CACHE_MODE_IGNORE     = 0;

  /**
   *
   */
  const CACHE_MODE_RETRIEVE   = 1;

  /**
   *
   */
  const CACHE_MODE_REGENERATE = 2;


  /**
   *
   */
  const MIME_GIF = 'image/gif';

  /**
   *
   */
  const MIME_JPG = 'image/jpeg';

  /**
   *
   */
  const MIME_PNG = 'image/png';


  /**
   *
   */
  const DEFAULT_HEIGHT     = 200;

  /**
   *
   */
  const DEFAULT_WIDTH      = 200;

  /**
   *
   */
  const DEFAULT_QUALITY    = 80;

  /**
   *
   */
  const DEFAULT_METHOD     = self::METHOD_SCALE;

  /**
   *
   */
  const DEFAULT_BEHAVIOUR  = self::BEHAVIOUR_NORMAL;

  /**
   *
   */
  const DEFAULT_CACHE_MODE = self::CACHE_RETRIEVE;


  /**
   *
   */
  const MAX_RESAMPLE_DIMENSION = 4000;


  /**
   *
   */
  protected $quality    = self::DEFAULT_QUALITY;

  /**
   *
   */
  protected $method     = self::DEFAULT_METHOD;

  /**
   *
   */
  protected $behaviour  = self::DEFAULT_BEHAVIOUR;

  /**
   *
   */
  protected $cache_mode = self::DEFAULT_CACHE_MODE;

  /**
   *
   */
  protected $maxh = self::DEFAULT_HEIGHT;

  /**
   *
   */
  protected $maxw = self::DEFAULT_WIDTH;

  /**
   *
   */
  protected $srch = self::DEFAULT_HEIGHT;

  /**
   *
   */
  protected $srcw = self::DEFAULT_WIDTH;

  /**
   *
   */
  protected $dsth = self::DEFAULT_HEIGHT;

  /**
   *
   */
  protected $dstw = self::DEFAULT_WIDTH;

  /**
   *
   */
  protected $dst = null;

  /**
   *
   */
  protected $src = null;

  /**
   *
   */
  protected $cache_file = null;

  /**
   *
   */
  protected $background = array();

  /**
   *
   */
  protected $generated = false;

  /**
   *
   */
  public function __construct() {
  }

  /**
   *
   */
  public function __destruct() {
    $this->cleanup();
  }

  /**
   *
   */
  public static function create() {
    return new self();
  }

  /**
   *
   */
  public function getHeight() {
    return $this->maxh;
  }

  /**
   *
   */
  public function setHeight($h) {
    if (is_int($h) && $h > 0)
      $this->maxh = $h;
    else
      throw new ImageException('Height must be a positive integer in pixels.', ImageException::INVALID_PARAMETER);
    return $this;
  }

  /**
   *
   */
  public function getWidth() {
    return $this->maxw;
  }

  /**
   *
   */
  public function setWidth($w) {
    if (is_int($w) && $w > 0)
      $this->maxw = $w;
    else
      throw new ImageException('Width must be a positive integer in pixels.', ImageException::INVALID_PARAMETER);
    return $this;
  }

  /**
   *
   */
  public function getQuality() {
    return $this->quality;
  }

  /**
   *
   */
  public function setQuality($q) {
    if (is_int($q) && $q >= 0 && $q <= 100)
      $this->quality = $q;
    else
      throw new ImageException('Quality must be an integer in the range 0-100.', ImageException::INVALID_PARAMETER);
    return $this;
  }

  /**
   *
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   *
   */
  public function setMethod($m) {
    switch ($m) {
      case self::METHOD_SCALE:
      case self::METHOD_CROP:
        $this->method = $m;
        break;
      default:
        throw new ImageException('Method could not be set.', ImageException::INVALID_PARAMETER);
    }
    return $this;
  }

  /**
   *
   */
  public function getBehaviour() {
    return $this->behaviour;
  }

  /**
   *
   */
  public function setBehaviour($b) {
    switch ($b) {
      case self::BEHAVIOUR_NORMAL:
      case self::BEHAVIOUR_EXPAND:
      case self::BEHAVIOUR_FIXED:
        $this->behaviour = $b;
        break;
      default:
        throw new ImageException('Behaviour could not be set.', ImageException::INVALID_PARAMETER);
    }
    return $this;
  }

  /**
   *
   */
  public function getCacheMode() {
    return $this->cache_mode;
  }

  /**
   *
   */
  public function setCacheMode($c) {
    switch ($c) {
      case self::CACHE_MODE_IGNORE:
      case self::CACHE_MODE_RETRIEVE:
      case self::CACHE_MODE_REGENERATE:
        $this->cache_mode = $c;
        break;
      default:
        throw new ImageException('Cache mode could not be set.', ImageException::INVALID_PARAMETER);
    }
    return $this;
  }

  /**
   *
   */
  public function output() {
    $this->initialise();
    $this->generate();
    $this->outputHeader();
    $this->outputContent();
    $this->cleanup();
  }

  /**
   *
   */
  protected function initialise() {
    # Set a high memory limit for this script.
    ini_set('memory_limit', '64M');
    # Make sure that we have the correct quality if PNG
    $this->transformQuality();
  }

  /**
   *
   */
  protected function cleanup() {
    # Clean up outstanding resources.
    if ($this->dst) imagedestroy($this->dst);
    if ($this->src) imagedestroy($this->src);
    # Restore the original memory limit.
    ini_restore('memory_limit');
  }

  /**
   *
   */
  protected function transformQuality() {
    if ($this->dst_mime !== self::MIME_PNG) return;
    if ($this->quality < 100) {
      $this->quality = min(9, floor((100 - $this->quality) / 10));
    } else {
      $this->quality = 0;
    }
  }

  /**
   *
   */
  protected function generate() {
    if ($this->cache_mode === self::CACHE_MODE_RETRIEVE) return;
    # Create source image resource
    $this->src = $imgfrom($img); #TODO:FIX
    # Initialise dimensions and offsets
    $this->dstx = 0;
    $this->dsty = 0;
    $this->srcx = 0;
    $this->srcy = 0;
    $this->srcw = imagesx($this->src);
    $this->srch = imagesy($this->src);
    # Create destination image resource
    if ($this->dst) imagedestroy($this->dst);
    switch ($this->behaviour) {
      case self::BEHAVOUR_NORMAL:
        switch ($this->method) {
          case METHOD_CROP:
            $this->dst = imagecreatetruecolor(($srcw > $maxw ? $maxw : $dstw), ($srch > $maxh ? $maxh : $dsth));
            break;
          case METHOD_SCALE:
            $this->dst = imagecreatetruecolor($dstw, $dsth);
            break;
          default: # [should not get here]
            throw new ImageException("Invalid resize method.");
        }
        break;
      case self::BEHAVOUR_EXPAND:
        # TODO: Implement this.
        throw new ImageException("Selected resize behaviour not implemented.");
        break;
      case self::BEHAVIOUR_FIXED:
        $this->dst = imagecreatetruecolor($this->maxw, $this->maxh);
        break;
      default: # [should not get here]
        throw new ImageException("Invalid resize behaviour.");
    }
    # Set the background colour of the destination image.
    $bg = imagecolorallocatealpha($this->dst, $this->background['r'], $this->background['g'], $this->background['b'], $this->background['a']);
    imagefill($this->dst, 0, 0, $bg);
    # Set additional image properties.
    switch ($this->dst_mime) {
      case self::MIME_GIF:
        imagepalettecopy($this->dst, $this->src);
        break;
      case self::MIME_JPG:
        break;
      case self::MIME_PNG:
        imagepalettecopy($this->dst, $this->src);
        imagealphablending($this->dst, false);
        imagesavealpha($this->dst, true);
        break;
      default: # [should not get here]
        throw new ImageException("Invalid output mime type: '{$this->dst_mime}'.");
    }
    $this->generated = true;
  }

  /**
   *
   */
  protected function outputHeader() {
    header('Content-Type: ' . $this->dst_mime);
    header('Content-Disposition: inline; filename=' . basename($this->cache_file));
    header('Content-Description: auto-generated image');
    switch ($this->cache_mode) {
      case self::CACHE_MODE_IGNORE:
        header('Expires: ' . date('r', strtotime('-1 week')));
        header('Last-Modified: ' . date('r'));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        break;
      case self::CACHE_MODE_RETRIEVE:
        header('Expires: ' . date('r', strtotime('+1 week')));
        header('Last-Modified: ' . date('r', filemtime($this->cache_file)));
        break;
      case self::CACHE_MODE_REGENERATE:
        header('Expires: ' . date('r', strtotime('+1 week')));
        header('Last-Modified: ' . date('r'));
        break;
      default: # [should not get here]
        throw new ImageException('Invalid cache mode.');
    }
  }

  /**
   *
   */
  public function outputContent() {
    if (!$this->generated) $this->generate();
    switch ($this->cache_mode) {
      case CACHE_MODE_RETRIEVE:
        if (is_readable($this->cache_file))
          @readfile($this->cache_file);
        else
          throw new ImageException('Cache file cannot be read.', ImageException::PERMISSIONS_ERROR);
        break;
      case CACHE_MODE_REGENERATE:
        if (is_writable(dirname($this->cache_file)))
          imgout_wrapper($this->dst, $this->cache_file, $this->quality);
        else
          throw new ImageException('Cache directory cannot be written to.', ImageException::PERMISSIONS_ERROR);
        # [fall through to display the image]
      case CACHE_MODE_IGNORE:
        imgout_wrapper($this->dst, null, $this->quality);
        break;
      default: # [should not get here]
        throw new ImageException('Invalid cache mode.');
    }
  }

}
