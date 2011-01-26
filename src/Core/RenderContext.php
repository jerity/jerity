<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

namespace Jerity\Core;

/**
 * Rendering context information.
 *
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */
class RenderContext {
  /**#@+
   * Known {@link RenderContext} languages.
   */
  /** HTML */
  const LANG_HTML  = 'html';
  /** XHTML */
  const LANG_XHTML = 'xhtml';
  /** XML */
  const LANG_XML   = 'xml';
  /** CSS */
  const LANG_CSS   = 'css';
  /** JavaScript */
  const LANG_JS    = 'javascript';
  /** JavaScript Object Notation */
  const LANG_JSON  = 'json';
  /** Plain text, or other text not covered here */
  const LANG_TEXT  = 'text';
  /**#@-*/

  /**#@+
   * Known {@link RenderContext} language dialects, generally for (X)HTML.
   */
  /** No dialect/not applicable */
  const DIALECT_NONE         = '';
  /** Strict dialect; deprecated features disallowed */
  const DIALECT_STRICT       = 'strict';
  /** Transitional dialect; deprecated features allowed */
  const DIALECT_TRANSITIONAL = 'transitional';
  /** Frameset dialect; frame container page */
  const DIALECT_FRAMESET     = 'frameset';
  /**#@-*/

  /**#@+
   * Known {@link RenderContext} MIME content types.
   */
  /** HTML */
  const CONTENT_HTML  = 'text/html';
  /** XHTML */
  const CONTENT_XHTML = 'application/xhtml+xml';
  /** XML */
  const CONTENT_XML   = 'application/xml';
  /** CSS */
  const CONTENT_CSS   = 'text/css';
  /** JavaScript */
  const CONTENT_JS    = 'text/javascript';
  /** JavaScript Object Notation (JSON) */
  const CONTENT_JSON  = 'application/json';
  /** Plain text */
  const CONTENT_TEXT  = 'text/plain';
  /** Atom (similar to RSS but more powerful) */
  const CONTENT_ATOM  = 'application/atom+xml';
  /** RSS (Rich Site Summary/Syndication) */
  const CONTENT_RSS   = 'application/rss+xml';
  /**#@-*/

  /**#@+
   * Common {@link RenderContext} types, incorporating language, version and dialect.
   */
  /** HTML 4.01 Strict */
  const TYPE_HTML4_STRICT        = 'html-4.01-strict';
  /** HTML 4.01 Transitional */
  const TYPE_HTML4_TRANSITIONAL  = 'html-4.01-transitional';
  /** HTML 4.01 Frameset */
  const TYPE_HTML4_FRAMESET      = 'html-4.01-frameset';
  /** HTML 5 */
  const TYPE_HTML5               = 'html-5';
  /** XHTML 1.0 Strict */
  const TYPE_XHTML1_STRICT       = 'xhtml-1.0-strict';
  /** XHTML 1.0 Transitional */
  const TYPE_XHTML1_TRANSITIONAL = 'xhtml-1.0-transitional';
  /** XHTML 1.0 Frameset */
  const TYPE_XHTML1_FRAMESET     = 'xhtml-1.0-frameset';
  /** XHTML 5 */
  const TYPE_XHTML5              = 'xhtml-5';
  /**#@-*/

  /**
   * The shared global rendering context stack.
   *
   * @var  \SplStack
   */
  static protected $global_context_stack = null;

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
  protected $version = 4.01;

  /**
   * The dialect of the language that should be used for rendering, for example
   * "strict" or "transitional".
   *
   * @var  string
   */
  protected $dialect = self::DIALECT_STRICT;

  /**
   * The MIME content type of the render context.
   *
   * @var  string
   */
  protected $content_type = null;

  /**
   * Whether to serve XHTML 1.0 documents in compatibility mode, i.e. with the
   * text/html MIME type in place of the standard application/xhtml+xml MIME
   * type.
   *
   * @var  boolean
   *
   * @link  http://www.w3.org/TR/xhtml1/#guidelines  HTML Compatilibilty
   */
  protected $xhtml_1_0_compat = false;

  /**
   * The character encoding with which to render content.  This is set to null 
   * by default which indicates that no encoding should be explicitly set.
   *
   * @var  string|null
   *
   * @link  http://www.iana.org/assignments/character-sets
   */
  protected $charset = null;

  /**
   * Create a new rendering context.
   *
   * @param  string  $language  The language for the new context.
   * @param  mixed   $version   The language version.
   * @param  string  $dialect   The language dialect.
   */
  public function __construct($language = null, $version = null, $dialect = null) {
    if (!is_null($language)) {
      $this->setLanguage($language);
      if (!is_null($version)) $this->setVersion($version);
      if (!is_null($dialect)) $this->setDialect($dialect);
    }
  }

  /**
   * Return the global shared rendering context on top of the stack.
   *
   * @return  RenderContext|null  The <tt>RenderContext</tt> on the stack.
   */
  public static function get() {
    if (!self::$global_context_stack instanceof \SplStack) {
      self::$global_context_stack = new \SplStack();
    }
    if (self::$global_context_stack->isEmpty()) return null;
    return self::$global_context_stack->top();
  }

  /**
   * Set the global shared rendering context.
   *
   * This will clear all contexts that are currently on the stack.
   *
   * @param   RenderContext  $context  The new global rendering context.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   */
  public static function set(RenderContext $context = null) {
    if ($context === null) {
      self::$global_context_stack = null;
    } else {
      self::$global_context_stack = new \SplStack();
      self::$global_context_stack->push($context);
    }
    return $context;
  }

  /**
   * Push a new rendering context onto the global shared rendering context
   * stack.
   *
   * @param   RenderContext  $context  The new global rendering context.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   */
  public static function push(RenderContext $context = null) {
    if (!self::$global_context_stack instanceof \SplStack) {
      self::$global_context_stack = new \SplStack();
    }
    self::$global_context_stack->push($context);
    return $context;
  }

  /**
   * Pop a rendering context from the global shared rendering context stack and
   * return it.
   *
   * @return  RenderContext  The render context.
   */
  public static function pop() {
    if (!self::$global_context_stack instanceof \SplStack) {
      self::$global_context_stack = new \SplStack();
    }
    if (self::$global_context_stack->isEmpty()) return null;
    return self::$global_context_stack->pop();
  }

  /**
   * Generate one of a number of common rendering contexts.
   *
   * @param   string  $type  One of the TYPE_* class constants.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   *
   * @throws  \InvalidArgumentException
   */
  public static function create($type) {
    switch ($type) {
      case self::TYPE_HTML4_STRICT:
        return new RenderContext(self::LANG_HTML, 4.01, self::DIALECT_STRICT);
      case self::TYPE_HTML4_TRANSITIONAL:
        return new RenderContext(self::LANG_HTML, 4.01, self::DIALECT_TRANSITIONAL);
      case self::TYPE_HTML4_FRAMESET:
        return new RenderContext(self::LANG_HTML, 4.01, self::DIALECT_FRAMESET);
      case self::TYPE_HTML5:
        return new RenderContext(self::LANG_HTML, 5, self::DIALECT_NONE);
      case self::TYPE_XHTML1_STRICT:
        return new RenderContext(self::LANG_XHTML, 1.0, self::DIALECT_STRICT);
      case self::TYPE_XHTML1_TRANSITIONAL:
        return new RenderContext(self::LANG_XHTML, 1.0, self::DIALECT_TRANSITIONAL);
      case self::TYPE_XHTML1_FRAMESET:
        return new RenderContext(self::LANG_XHTML, 1.0, self::DIALECT_FRAMESET);
      case self::TYPE_XHTML5:
        return new RenderContext(self::LANG_XHTML, 5, self::DIALECT_NONE);
    }
    throw new \InvalidArgumentException("Unrecognised context type '{$type}'");
  }

  /**
   * Generate the doctype for the current rendering context, if applicable.
   *
   * If there is no known doctype for the language, then an empty string will
   * be returned. If the version is not recognised, or the version supports
   * dialects and the dialect is not recognised, an exception will be thrown.
   *
   * @return  string  The doctype (if available) for the current context.
   *
   * @throws  \InvalidArgumentException
   */
  public function getDoctype() {
    switch ($this->language) {
      case self::LANG_HTML:
        switch ($this->version) {
          case 4.01:
            switch ($this->dialect) {
              case self::DIALECT_STRICT:
                return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
              case self::DIALECT_TRANSITIONAL:
                return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
              case self::DIALECT_FRAMESET:
                return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
              default:
                throw new \InvalidArgumentException("Unrecognised HTML 4.01 dialect '{$this->dialect}'; cannot build doctype.");
            }
            break;
          case 5:
            return '<!DOCTYPE html>';
          default:
            throw new \InvalidArgumentException("Unrecognised HTML version '{$this->version}'; cannot build doctype.");
        }
        break;
      case self::LANG_XHTML:
        switch ($this->version) {
          case 1.0:
            switch ($this->dialect) {
              case self::DIALECT_STRICT:
              case self::DIALECT_TRANSITIONAL:
              case self::DIALECT_FRAMESET:
                return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 '.ucfirst($this->dialect).'//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-'.$this->dialect.'.dtd">';
              default:
                throw new \InvalidArgumentException("Unrecognised XHTML 1.0 dialect '{$this->dialect}'; cannot build doctype.");
            }
            break;
          case 5:
            return '<!DOCTYPE html>';
          default:
            throw new \InvalidArgumentException("Unrecognised XHTML version '{$this->version}'; cannot build doctype.");
        }
        break;
    }
    return '';
  }

  /**
   * Render any content that should come before the document (such as doctype,
   * XML declaration, etc) as appropriate to the context.
   *
   * @return  string  The document preamble for the current context.
   */
  public function renderPreContent() {
    $output = '';
    if ($this->language == self::LANG_XML ||
      ($this->language == self::LANG_XHTML && !$this->getXHTMLCompatMode())) {
        $output .= '<'.'?xml version="1.0"';
        $charset = strtolower($this->getCharset());
        if ($charset !== null) $output .= ' encoding="'.$charset.'"';
        $output .= ' ?'.">\n";
    }
    $doctype = $this->getDoctype();
    if ($doctype) $output .= $doctype."\n";
    return $output;
  }

  /**
   * Return the language for this context.
   *
   * @return  string  The language for this context.
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * Set the language for this context.
   *
   * @param  string  $language  The new language for this context.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   */
  public function setLanguage($language) {
    $this->language = $language;
    return $this;
  }

  /**
   * Return the language version for this context.
   *
   * @return  mixed  The version for this context.
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * Set the language version for this context.
   *
   * @param  mixed  $version  The new version for this context.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   */
  public function setVersion($version) {
    $this->version = $version;
    return $this;
  }

  /**
   * Return the language dialect for this context.
   *
   * Examples include "strict" or "transitional".
   *
   * @return  string  The dialect for this context.
   */
  public function getDialect() {
    return $this->dialect;
  }

  /**
   * Set the language dialect for this context
   *
   * Examples include "strict" or "transitional".
   *
   * @param  string  $dialect  The new dialect for this context.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   */
  public function setDialect($dialect) {
    $this->dialect = $dialect;
    return $this;
  }

  /**
   * Return the content type appropriate for this rendering context.
   *
   * If we are using XHTML 1.0 in compatibility mode, we will return
   * <tt>text/html</tt> as the content type, otherwise the normal
   * <tt>application/xhtml+xml</tt> will be used.
   *
   * The default content type is <tt>application/octet-stream</tt>.
   *
   * @return  string  The content type for this context.
   *
   * @see  getXHTMLCompatMode()
   */
  public function getContentType() {
    if ($this->content_type) return $this->content_type;
    switch ($this->getLanguage()) {
      case self::LANG_HTML:
        $content_type = self::CONTENT_HTML;
        break;
      case self::LANG_XHTML:
        if ($this->getXHTMLCompatMode()) {
          $content_type = self::CONTENT_HTML;
        } else {
          $content_type = self::CONTENT_XHTML;
        }
        break;
      case self::LANG_XML:
        $content_type = self::CONTENT_XML;
        break;
      case self::LANG_CSS:
        $content_type = self::CONTENT_CSS;
        break;
      case self::LANG_JS:
        $content_type = self::CONTENT_JS;
        break;
      case self::LANG_JSON:
        $content_type = self::CONTENT_JSON;
        break;
      case self::LANG_TEXT:
        $content_type = self::CONTENT_TEXT;
        break;
      default:
        $content_type = 'application/octet-stream';
        break;
    }
    $this->setContentType($content_type);
    return $content_type;
  }

  /**
   * Set the content type appropriate for this rendering context, or null to
   * automatically detect based on the properties of the context.
   *
   * @param  string  $type  The new content type for this context.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   */
  public function setContentType($type) {
    $this->content_type = $type;
    return $this;
  }

  /**
   * Whether to serve XHTML 1.0 documents in compatibility mode, i.e. with
   * <tt>text/html</tt> in place of <tt>application/xhtml+xml</tt>.
   *
   * @return  bool  Whether to serve XHTML with <tt>text/html</tt>.
   *
   * @link  http://www.w3.org/TR/xhtml1/#guidelines  HTML Compatibility
   */
  public function getXHTMLCompatMode() {
    return $this->xhtml_1_0_compat;
  }

  /**
   * Whether to serve XHTML 1.0 documents in compatibility mode, i.e. with
   * <tt>text/html</tt> in place of <tt>application/xhtml+xml</tt>.
   *
   * @param  bool  $enabled  Whether to serve XHTML with <tt>text/html</tt>.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   *
   * @link  http://www.w3.org/TR/xhtml1/#guidelines  HTML Compatibility
   */
  public function setXHTMLCompatMode($enabled) {
    if ($this->getLanguage() == RenderContext::LANG_XHTML
      && $this->getVersion() == 1.0) {
      $this->xhtml_1_0_compat = $enabled;
    } else {
      $this->xhtml_1_0_compat = false;
      trigger_error('Compatibility mode only for XHTML 1.0', E_USER_WARNING);
    }
    return $this;
  }

  /**
   * Gets the default character set to use when rendering.
   *
   * @return  string|null
   *
   * @link  http://www.iana.org/assignments/character-sets
   */
  public function getCharset() {
    return $this->charset;
  }

  /**
   * Sets a default character set to use when rendering.  If set to null then 
   * no encoding will be explicitly set.  A valid IANA assigned character set 
   * must be used.
   *
   * @param  string | null  $charset  The character set to use.
   *
   * @return  RenderContext  <tt>RenderContext</tt> object for method chaining.
   *
   * @link  http://www.iana.org/assignments/character-sets
   */
  public function setCharset($charset) {
    $this->charset = $charset;
    return $this;
  }

  /**
   * Determines whether the current render context is a language with XML
   * syntax.
   *
   * @return  boolean
   */
  public function isXMLSyntax() {
    switch ($this->getLanguage()) {
      case RenderContext::LANG_XHTML:
      case RenderContext::LANG_XML:
        return true;
    }
    return false;
  }
}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
