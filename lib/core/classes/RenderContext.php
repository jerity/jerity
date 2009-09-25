<?php
/**
 * @package    JerityCore
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */

/**
 * Rendering context information.
 *
 * @package    JerityCore
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */
class RenderContext {
  /**#@+
   * Known {@link RenderContext} languages.
   */
  /** CSS */
  const LANG_CSS   = 'css';
  /** FBJS, Facebook's variant on JavaScript */
  const LANG_FBJS  = 'fbjs';
  /** FBML, Facebook's markup language */
  const LANG_FBML  = 'fbml';
  /** HTML */
  const LANG_HTML  = 'html';
  /** JavaScript */
  const LANG_JS    = 'javascript';
  /** JavaScript Object Notation */
  const LANG_JSON  = 'json';
  /** Mobile HTML (obsolete?) */
  const LANG_MHTML = 'mhtml';
  /** Plain text, or other text not covered here */
  const LANG_TEXT  = 'text';
  /** Wireless Markup Language, generally used by older WAP browsers */
  const LANG_WML   = 'wml';
  /** XHTML */
  const LANG_XHTML = 'xhtml';
  /** XML */
  const LANG_XML   = 'xml';
  /**#@-*/

  /**#@+
   * Known {@link RenderContext} language dialects, generally for (X)HTML.
   */
  /** Frameset (frame container page) */
  const DIALECT_FRAMESET     = 'frameset';
  /** Mobile (for XHTML) */
  const DIALECT_MOBILE       = 'mobile';
  /** No dialect/not applicable */
  const DIALECT_NONE         = '';
  /** Strict dialect; deprecated features disallowed */
  const DIALECT_STRICT       = 'strict';
  /** Transitional dialect; deprecated features allowed */
  const DIALECT_TRANSITIONAL = 'transitional';
  /**#@-*/

  /**#@+
   * Known {@link RenderContext} MIME content types.
   */
  /** Atom (similar to RSS but more powerful) */
  const CONTENT_ATOM     = 'application/atom+xml';
  /** CSS */
  const CONTENT_CSS      = 'text/css';
  /** HTML */
  const CONTENT_HTML     = 'text/html';
  /** JavaScript */
  const CONTENT_JS       = 'text/javascript'; # application/javascript
  /** JavaScript Object Notation (JSON) */
  const CONTENT_JSON     = 'application/json';
  /** RSS (Rich Site Summary/Syndication) */
  const CONTENT_RSS      = 'application/rss+xml';
  /** Plain text */
  const CONTENT_TEXT     = 'text/plain';
  /** Wireless Markup Language */
  const CONTENT_WML      = 'application/vnd.wap.wml';
  /** XHTML */
  const CONTENT_XHTML    = 'application/xhtml+xml';
  /** XHTML (mobile profile) */
  const CONTENT_XHTML_MP = 'application/vnd.wap.xhtml+xml';
  /** XML */
  const CONTENT_XML      = 'application/xml';
  /**#@-*/

  /**#@+
   * Common {@link RenderContext} types, incorporating language, version and dialect.
   */
  /** HTML 4.01 frameset */
  const TYPE_HTML4_FRAMESET      = 'html-4.01-frameset';
  /** HTML 4.01 strict */
  const TYPE_HTML4_STRICT        = 'html-4.01-strict';
  /** HTML 4.01 transitional */
  const TYPE_HTML4_TRANSITIONAL  = 'html-4.01-transitional';
  /** HTML 5 */
  const TYPE_HTML5               = 'html-5';
  /** XHTML 1.0 frameset */
  const TYPE_XHTML1_FRAMESET     = 'xhtml-1.0-frameset';
  /** XHTML 1.0 mobile profile */
  const TYPE_XHTML1_MOBILE       = 'xhtml-1.0-mobile';
  /** XHTML 1.0 strict */
  const TYPE_XHTML1_STRICT       = 'xhtml-1.0-strict';
  /** XHTML 1.0 transitional */
  const TYPE_XHTML1_TRANSITIONAL = 'xhtml-1.0-transitional';
  /** XHTML 1.1 */
  const TYPE_XHTML1_1            = 'xhtml-1.1';
  /** XHTML 1.1 mobile profile */
  const TYPE_XHTML1_1_MOBILE     = 'xhtml-1.1-mobile';
  /** XHTML 1.2 mobile profile */
  const TYPE_XHTML1_2_MOBILE     = 'xhtml-1.2-mobile';
  /**#@-*/

  /**
   * The shared global rendering context.
   *
   * @var  array
   */
  static protected $globalContext = array();

  /**
   * The language that should be used for rendering.
   *
   * @var  string
   */
  protected $language = self::LANG_HTML;

  /**
   * The version of the language that should be used for rendering.
   *
   * @var  mixed
   */
  protected $version  = 4.01;

  /**
   * The dialect of the language that should be used for rendering, for example
   * "strict" or "transitional".
   *
   * @var  string
   */
  protected $dialect  = self::DIALECT_STRICT;

  /**
   * The MIME content type of the render context.
   *
   * @var  string
   */
  protected $contentType  = null;

  /**
   * Create a new rendering context.
   *
   * @param  string  $language  The language for the new context.
   * @param  mixed   $version   The language version.
   * @param  string  $dialect   The language dialect.
   */
  public function __construct($language=null, $version=null, $dialect=null) {
    if (!is_null($language)) {
      $this->setLanguage($language);
      if (!is_null($version)) {
        $this->setVersion($version);
      }
      if (!is_null($dialect)) {
        $this->setDialect($dialect);
      }
    }
  }

  /**
   * Return the global shared rendering context.
   *
   * @return  RenderContext
   */
  public static function get() {
    if (count(self::$globalContext)) {
      return self::$globalContext[0];
    } else {
      return null;
    }
  }

  /**
   * (Deprecated) Return the global shared rendering context.
   *
   * @return  RenderContext
   */
  public static function getGlobalContext() {
    trigger_error('Deprecated in favour of RenderContext::get()', E_USER_WARNING);
    return self::get();
  }

  /**
   * Set the global shared rendering context.
   *
   * @param   RenderContext  $context  The new global rendering context.
   * @return  RenderContext
   */
  public static function set(RenderContext $context) {
    return (self::$globalContext = array($context));
  }

  /**
   * (Deprecated) Set the global shared rendering context.
   *
   * @param   RenderContext  $context  The new global rendering context.
   * @return  RenderContext
   */
  public static function setGlobalContext(RenderContext $context) {
    trigger_error('Deprecated in favour of RenderContext::set()', E_USER_WARNING);
    return self::set($context);
  }

  /**
   * Push a new rendering context onto the global shared rendering context
   * stack.
   *
   * @param   RenderContext  $context  The new global rendering context.
   * @return  RenderContext
   */
  public static function push(RenderContext $context) {
    array_unshift(self::$globalContext, $context);
    return $context;
  }

  /**
   * (Deprecated) Push a new rendering context onto the global shared rendering
   * context stack.
   *
   * @param   RenderContext  $context  The new global rendering context.
   * @return  RenderContext
   */
  public static function pushGlobalContext(RenderContext $context) {
    trigger_error('Deprecated in favour of RenderContext::push()', E_USER_WARNING);
    return self::push($context);
  }

  /**
   * Pop a rendering context from the global shared rendering context stack and
   * return it.
   *
   * @return  RenderContext
   */
  public static function pop() {
    return array_shift(self::$globalContext);
  }

  /**
   * (Deprecated) Pop a rendering context from the global shared rendering
   * context stack and return it.
   *
   * @return  RenderContext
   */
  public static function popGlobalContext() {
    trigger_error('Deprecated in favour of RenderContext::pop()', E_USER_WARNING);
    return self::pop();
  }

  /**
   * Generate one of a number of common rendering contexts.
   *
   * @param   string  $type  One of the TYPE_* class constants.
   * @return  RenderContext
   *
   * @throws  InvalidArgumentException
   */
  public static function create($type) {
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
      case self::TYPE_XHTML1_MOBILE:
        $ctx->setLanguage(self::LANG_XHTML);
        $ctx->setVersion(1.0);
        $ctx->setDialect(self::DIALECT_MOBILE);
        break;
      case self::TYPE_XHTML1_1_MOBILE:
        $ctx->setLanguage(self::LANG_XHTML);
        $ctx->setVersion(1.1);
        $ctx->setDialect(self::DIALECT_MOBILE);
        break;
      case self::TYPE_XHTML1_2_MOBILE:
        $ctx->setLanguage(self::LANG_XHTML);
        $ctx->setVersion(1.2);
        $ctx->setDialect(self::DIALECT_MOBILE);
        break;
      default:
        throw new InvalidArgumentException('Unrecognised context type: '.$type);
    }

    return $ctx;
  }

  /**
   * (Deprecated) Generate one of a number of common rendering contexts.
   *
   * @param   string  $type  One of the TYPE_* class constants.
   * @return  RenderContext
   *
   * @throws  InvalidArgumentException
   */
  public static function makeContext($type) {
    trigger_error('Deprecated in favour of RenderContext::create()', E_USER_WARNING);
    return self::create($type);
  }

  /**
   * Generate the doctype for the current rendering context, if applicable.
   *
   * If there is no known doctype for the language, then an empty string will
   * be returned. If the version is not recognised, or the version supports
   * dialects and the dialect is not recognised, an exception will be thrown.
   *
   * @return  string
   *
   * @throws  InvalidArgumentException
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
   * @return  string
   */
  public function renderPreContent() {
    $output = '';
    if ($this->language == self::LANG_XML || $this->language == self::LANG_XHTML) {
      $output .= '<'.'?xml version="1.0" encoding="utf-8" ?'.">\n";
    }
    if ($doctype = $this->getDoctype()) {
      $output .= $doctype."\n";
    }
    return $output;
  }

  /**
   * Return the language for this context.
   *
   * @return  string
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * Set the language for this context.
   *
   * @param   string  $language  The new language
   * @return  string
   */
  public function setLanguage($language) {
    return ($this->language = $language);
  }

  /**
   * Return the version of the language for this context.
   *
   * @return  mixed
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * Set the version of the language for this context.
   *
   * @param   mixed  $version  The new language version
   * @return  mixed
   */
  public function setVersion($version) {
    return ($this->version = $version);
  }

  /**
   * Return the language dialect for this context (such as "transitional" or
   * "strict").
   *
   * @return  string
   */
  public function getDialect() {
    return $this->dialect;
  }

  /**
   * Set the language dialect for this context (such as "transitional" or "strict").
   *
   * @param   string  $dialect  The new language dialect
   * @return  string
   */
  public function setDialect($dialect) {
    return ($this->dialect = $dialect);
  }

  /**
   * Return the content type appropriate for this rendering context. If we are
   * in strict mode, we will return the correct MIME type for XHTML, otherwise
   * we will use text/html. The default is "application/octet-stream".
   *
   * @param   bool  $strict  Whether or not to be strict about content types.
   * @return  string
   */
  public function getContentType($strict = true) {
    if ($this->contentType) {
      return $this->contentType;
    }
    switch ($this->getLanguage()) {
      case self::LANG_HTML:
        $contentType = self::CONTENT_HTML;
        break;
      case self::LANG_XHTML:
        if ($this->getDialect() == self::DIALECT_MOBILE) {
          $contentType = self::CONTENT_XHTML_MP;
        } elseif ($strict) {
          $contentType = self::CONTENT_XHTML;
        } else {
          $contentType = self::CONTENT_HTML;
        }
        break;
      case self::LANG_JS:
      case self::LANG_FBJS:
        $contentType = self::CONTENT_JS;
        break;
      case self::LANG_TEXT:
        $contentType = self::CONTENT_TEXT;
        break;
      case self::LANG_XML:
      case self::LANG_FBML:
        $contentType = self::CONTENT_XML;
        break;
      case self::LANG_JSON:
        $contentType = self::CONTENT_JSON;
        break;
      case self::LANG_CSS:
        $contentType = self::CONTENT_CSS;
        break;
      case self::LANG_MHTML:
        $contentType = self::CONTENT_XHTML_MP;
        break;
      case self::LANG_WML:
        $contentType = self::CONTENT_WML;
        break;
      default:
        $contentType = 'application/octet-stream';
        break;
    }
    $this->setContentType($contentType);
    return $contentType;
  }

  /**
   * Set the content type appropriate for this rendering context, or null to
   * automatically detect based on the properties of the context.
   *
   * @param   string  $type  The new content type for this rendering context.
   * @return  string
   */
  public function setContentType($type) {
    $this->contentType = $type;
  }

  /**
   * Determines whether the current render context is a language with XML
   * syntax.
   *
   * @return  boolean
   */
  public function isXMLSyntax() {
    switch ($this->getLanguage()) {
      case RenderContext::LANG_FBML:
      case RenderContext::LANG_WML:
      case RenderContext::LANG_XHTML:
      case RenderContext::LANG_XML:
        return true;
      default:
        return false;
    }
  }
}
