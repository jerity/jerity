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
class OldImage {

  /**
   * Method: Resize
   *
   * @var  int
   */
  const METHOD_SCALE = 0;

  /**
   * Method: Crop
   *
   * @var  int
   */
  const METHOD_CROP = 1;


  /**
   * Behaviour: Normal
   *
   * @var  int
   */
  const BEHAVIOUR_NORMAL = 0;

  /**
   * Behaviour: Expand
   *
   * @var  int
   */
  const BEHAVIOUR_EXPAND = 1;

  /**
   * Behaviour: Fixed
   *
   * @var  int
   */
  const BEHAVIOUR_FIXED = 2;


  /**
   * Cache Mode: Ignore
   *
   * @var  int
   */
  const CACHE_MODE_IGNORE = 0;

  /**
   * Cache Mode: Retrieve
   *
   * @var  int
   */
  const CACHE_MODE_RETRIEVE = 1;

  /**
   * Cache Mode: Regenerate
   *
   * @var  int
   */
  const CACHE_MODE_REGENERATE = 2;


  /**
   * The default quality.
   *
   * @var  int
   */
  const DEFAULT_QUALITY = 80;

  /**
   * The default method of transformation.
   *
   * @var  int
   */
  const DEFAULT_METHOD = self::METHOD_SCALE;

  /**
   * The default behaviour for resizing/cropping.
   *
   * @var  int
   */
  const DEFAULT_BEHAVIOUR = self::BEHAVIOUR_NORMAL;

  /**
   * The default caching mode.
   *
   * @var  int
   */
  const DEFAULT_CACHE_MODE = self::CACHE_MODE_RETRIEVE;


  /**
   * The maximum resampling dimensions.
   *
   * @var  int
   */
  const MAX_RESAMPLE_DIMENSION = 4000;


  /**
   * The quality or compression for the image.
   *
   * @var  int
   */
  protected $quality = self::DEFAULT_QUALITY;

  /**
   * The method of transformation.
   *
   * @var  int
   */
  protected $method = self::DEFAULT_METHOD;

  /**
   * The behaviour for resizing/cropping.
   *
   * @var  int
   */
  protected $behaviour = self::DEFAULT_BEHAVIOUR;

  /**
   * The caching mode in use for this image.
   *
   * @var  int
   */
  protected $cache_mode = self::DEFAULT_CACHE_MODE;

  /**
   * The path to the cached image file.
   *
   * @var  string
   */
  protected $cache_file = null;

  /**
   * The background color for this image.  The default background colour is
   * transparent/white.
   *
   * @var  array
   */
  protected $background = array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 127);

  /**
   * Whether this image has been generated.
   *
   * @var  bool
   */
  protected $generated = false;

  /**
   * Gets the desired quality of the image.
   */
  public function getQuality() {
    return $this->quality;
  }

  /**
   * Sets the desired quality of the image.
   *
   * @return  Image  Fluid API
   */
  public function setQuality($q) {
    if (is_int($q) && $q >= 0 && $q <= 100) {
      $this->quality = $q;
    } else {
      throw new ImageException('Quality must be an integer in the range 0-100.', ImageException::INVALID_PARAMETER);
    }
    return $this;
  }

  /**
   * Gets the transformation method.
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * Sets the transformation method.
   *
   * @return  Image  Fluid API
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
   * Gets the behaviour of the transformation.
   */
  public function getBehaviour() {
    return $this->behaviour;
  }

  /**
   * Sets the behaviour of the transformation.
   *
   * @return  Image  Fluid API
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
   * Gets the desired cache mode.
   */
  public function getCacheMode() {
    return $this->cache_mode;
  }

  /**
   * Sets the desired cache mode.
   *
   * @return  Image  Fluid API
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
   * Output the image.
   */
  public function output() {
    $this->initialise();
    $this->generate();
    $this->outputHeader();
    $this->outputContent();
    $this->cleanup();
    # TODO: Return status code.
  }

  /**
   * Initialise the class.
   */
  protected function initialise() {
    # Set a high memory limit for this script.
    ini_set('memory_limit', '64M');
    # Make sure that we have the correct quality if PNG
    $this->transformQuality();
  }

  /**
   * Translate between JPEG quality and PNG compression.
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
   * Generate the image.
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
    if (is_resource($this->dst)) imagedestroy($this->dst);
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

}
