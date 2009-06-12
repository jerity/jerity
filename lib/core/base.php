<?php
// ensure we get all errors
$__er = error_reporting(E_ALL | E_STRICT | E_NOTICE);

/**
 * @package JerityCore
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */


/**
 * Represents a renderable item.
 *
 * @package JerityCore
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
interface Renderable {
  /**
   * Render the item using the current global rendering context, and return it
   * as a string.
   *
   * @return string
   */
  public function render();
}

/**
 * Rendering context information.
 *
 * @package JerityCore
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
class RenderContext {
  const LANG_FBJS  = 'fbjs';
  const LANG_FBML  = 'fbml';
  const LANG_HTML  = 'html';
  const LANG_JSON  = 'json';
  const LANG_MHTML = 'mhtml';
  const LANG_TEXT  = 'text';
  const LANG_WML   = 'wml';
  const LANG_XHTML = 'xhtml';
  const LANG_XML   = 'xml';

  const DIALECT_FRAMESET     = 'frameset';
  const DIALECT_MOBILE       = 'mobile';
  const DIALECT_STRICT       = 'strict';
  const DIALECT_TRANSITIONAL = 'transitional';

  const CONTENT_ATOM     = 'application/atom+xml';
  const CONTENT_CSS      = 'text/css';
  const CONTENT_HTML     = 'text/html';
  const CONTENT_JS       = 'application/javascript';
  const CONTENT_JSON     = 'application/json';
  const CONTENT_RSS      = 'application/rss+xml';
  const CONTENT_TEXT     = 'text/plain';
  const CONTENT_WML      = 'application/vnd.wap.wml';
  const CONTENT_XHTML    = 'application/xhtml+xml';
  const CONTENT_XHTML_MP = 'application/vnd.wap.xhtml+xml';
  const CONTENT_XML      = 'application/xml';

  const TYPE_HTML4_FRAMESET      = 'html-4.01-frameset';
  const TYPE_HTML4_STRICT        = 'html-4.01-strict';
  const TYPE_HTML4_TRANSITIONAL  = 'html-4.01-transitional';
  const TYPE_HTML5               = 'html-5';
  const TYPE_XHTML1_FRAMESET     = 'xhtml-1.0-frameset';
  const TYPE_XHTML1_MOBILE       = 'xhtml-1.0-mobile';
  const TYPE_XHTML1_STRICT       = 'xhtml-1.0-strict';
  const TYPE_XHTML1_TRANSITIONAL = 'xhtml-1.0-transitional';
  const TYPE_XHTML1_1            = 'xhtml-1.1';
  const TYPE_XHTML1_1_MOBILE     = 'xhtml-1.1-mobile';
  const TYPE_XHTML1_2_MOBILE     = 'xhtml-1.2-mobile';

  /**
   * The shared global rendering context.
   *
   * @var RenderContext
   */
  static protected $globalContext = array();

  /**
   * The language that should be used for rendering.
   *
   * @var string
   */
  protected $language = self::LANG_HTML;

  /**
   * The version of the language that should be used for rendering.
   *
   * @var mixed
   */
  protected $version  = 4.01;

  /**
   * The dialect of the language that should be used for rendering, for example
   * "strict" or "transitional".
   *
   * @var string
   */
  protected $dialect  = self::DIALECT_STRICT;

  public function __construct() {
  }

  /**
   * Return the global shared rendering context.
   *
   * @return RenderContext
   */
  public static function getGlobalContext() {
    if (count(self::$globalContext)) {
      return self::$globalContext[0];
    } else {
      return null;
    }
  }

  /**
   * Set the global shared rendering context.
   *
   * @param RenderContext $context The new global rendering context.
   * @return RenderContext
   */
  public static function setGlobalContext(RenderContext $context) {
    return (self::$globalContext = array($context));
  }

  /**
   * Push a new rendering context onto the global shared rendering context
   * stack.
   *
   * @param RenderContext $context The new global rendering context.
   * @return RenderContext
   */
  public static function pushGlobalContext(RenderContext $context) {
    array_unshift(self::$globalContext, $context);
    return $context;
  }

  /**
   * Pop a rendering context from the global shared rendering context stack and
   * return it.
   *
   * @return RenderContext
   */
  public static function popGlobalContext(RenderContext $context) {
    return array_shift(self::$globalContext);
  }

  /**
   * Generate one of a number of common rendering contexts.
   *
   * @param string $type One of the TYPE_* class constants.
   * @return RenderContext
   *
   * @throws InvalidArgumentException
   */
  public static function makeContext($type) {
    // standard context factory
    $ctx = new RenderContext();
    switch ($type) {
      case self::TYPE_HTML4_STRICT:
        $ctx->setLanguage(self::LANG_HTML);
        $ctx->setVersion(4.01);
        $ctx->setDialect(self::DIALECT_STRICT);
        break;
      case self::TYPE_HTML4_TRANSITIONAL:
        $ctx->setLanguage(self::LANG_HTML);
        $ctx->setVersion(4.01);
        $ctx->setDialect(self::DIALECT_TRANSITIONAL);
        break;
      case self::TYPE_HTML4_FRAMESET:
        $ctx->setLanguage(self::LANG_HTML);
        $ctx->setVersion(4.01);
        $ctx->setDialect(self::DIALECT_FRAMESET);
        break;
      case self::TYPE_XHTML1_STRICT:
        $ctx->setLanguage(self::LANG_XHTML);
        $ctx->setVersion(1.0);
        $ctx->setDialect(self::DIALECT_STRICT);
        break;
      case self::TYPE_XHTML1_TRANSITIONAL:
        $ctx->setLanguage(self::LANG_XHTML);
        $ctx->setVersion(1.0);
        $ctx->setDialect(self::DIALECT_TRANSITIONAL);
        break;
      case self::TYPE_XHTML1_FRAMESET:
        $ctx->setLanguage(self::LANG_XHTML);
        $ctx->setVersion(1.0);
        $ctx->setDialect(self::DIALECT_FRAMESET);
        break;
      case self::TYPE_HTML5:
        $ctx->setLanguage(self::LANG_HTML);
        $ctx->setVersion(5);
        $ctx->setDialect('');
        break;
      case self::TYPE_XHTML1_1:
        $ctx->setLanguage(self::LANG_XHTML);
        $ctx->setVersion(1.1);
        $ctx->setDialect('');
        break;
      default:
        throw new InvalidArgumentException('Unrecognised context type: '.$type);
    }

    return $ctx;
  }

  /**
   * Generate the doctype for the current rendering context, if applicable.
   *
   * If there is no known doctype for the language, then an empty string will
   * be returned. If the version is not recognised, or the version supports
   * dialects and the dialect is not recognised, an exception will be thrown.
   *
   * @return string
   *
   * @throws InvalidArgumentException
   */
  public function getDoctype() {
    if ($this->language == self::LANG_HTML) {
      switch ($this->version) {
        case 2:
          return '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">';
        case 3.2:
          return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">';
        case 4.01:
          switch ($this->dialect) {
            case self::DIALECT_STRICT:
              return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
            case self::DIALECT_TRANSITIONAL:
              return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
            case self::DIALECT_FRAMESET:
              return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
            default:
              throw new InvalidArgumentException('Unrecognised HTML 4.01 dialect '.$this->dialect.'; cannot build doctype');
          }
        case 5:
          return '<!DOCTYPE html>';
        default:
          throw new InvalidArgumentException('Unrecognised HTML version '.$this->version.'; cannot build doctype');
      }
    } elseif ($this->language == self::LANG_XHTML) {
      if ($this->dialect === self::DIALECT_MOBILE) {
        if ($this->version==1.0) {
          return '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">';
        } else {
          return '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile '.$this->version.'//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile'.(10*$this->version).'.dtd">';
        }
      } else {
        switch ($this->version) {
          case 1.0:
            return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 '.ucfirst($this->dialect).'//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-'.$this->dialect.'.dtd">';
          case 1.1:
            return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
          default:
            throw new InvalidArgumentException('Unrecognised XHTML version '.$this->version.'; cannot build doctype');
        }
      }
    } else {
      return '';
    }
  }

  /**
   * Render any content that should come before the document (such as doctype,
   * XML declaration, etc) as appropriate to the context.
   *
   * @return string
   */
  public function renderPreContent() {
    $output = '';
    if ($this->language == self::LANG_XML || $this->language == self::LANG_XHTML) {
      $output .= '<'.'?xml version="1.0" encoding="utf-8" ?'.'>'."\n";
    }
    $output .= $this->getDoctype()."\n";
    return $output;
  }

  /**
   * Return the language for this context.
   *
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * Set the language for this context.
   *
   * @param string $language The new language
   * @return string
   */
  public function setLanguage($language) {
    return ($this->language = $language);
  }

  /**
   * Return the version of the language for this context.
   *
   * @return mixed
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * Set the version of the language for this context.
   *
   * @param mixed $version The new language version
   * @return mixed
   */
  public function setVersion($version) {
    return ($this->version = $version);
  }

  /**
   * Return the language dialect for this context (such as "transitional" or
   * "strict").
   *
   * @return string
   */
  public function getDialect() {
    return $this->dialect;
  }

  /**
   * Set the language dialect for this context (such as "transitional" or "strict").
   *
   * @param string $dialect The new language dialect
   * @return string
   */
  public function setDialect($dialect) {
    return ($this->dialect = $dialect);
  }
}


/* ******************** START: defaults and end-of-file ******************* */

// default global render context: HTML 4.01 strict
RenderContext::setGlobalContext(RenderContext::makeContext(RenderContext::TYPE_HTML4_STRICT));

// reset error reporting
error_reporting($__er);
