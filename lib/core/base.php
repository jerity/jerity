<?php

/**
 * @package JerityCore
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */


/**
 * Rendering context information
 */
class RenderContext {
  const LANG_JSON  = 'json';
  const LANG_XML   = 'xml';
  const LANG_HTML  = 'html';
  const LANG_XHTML = 'xhtml';

  const DIALECT_STRICT       = 'strict';
  const DIALECT_TRANSITIONAL = 'transitional';
  const DIALECT_FRAMESET     = 'frameset';

  const CONTENT_HTML  = 'text/html';
  const CONTENT_CSS   = 'text/css';
  const CONTENT_JS    = 'application/javascript';
  const CONTENT_JSON  = 'application/json';
  const CONTENT_XML   = 'application/xml';
  const CONTENT_RSS   = 'application/rss+xml';
  const CONTENT_ATOM  = 'application/atom+xml';
  const CONTENT_XHTML = 'application/xhtml+xml';

  const TYPE_HTML4_STRICT        = 'html-4.01-strict';
  const TYPE_HTML4_TRANSITIONAL  = 'html-4.01-transitional';
  const TYPE_HTML4_FRAMESET      = 'html-4.01-frameset';
  const TYPE_XHTML1_STRICT       = 'xhtml-1.0-strict';
  const TYPE_XHTML1_TRANSITIONAL = 'xhtml-1.0-transitional';

  static protected $globalContext = null;

  protected $language = self::LANG_HTML;
  protected $version  = 4.01;
  protected $dialect  = self::DIALECT_STRICT;

  public function __construct() {
  }

  public static function getGlobalContext() {
    return $globalContext;
  }

  public static function setGlobalContext(RenderContext $context) {
    return (self::$globalContext = $context);
  }

  public static function makeContext($type) {
    // context factory
  }

  public function getLanguage() {
    return $this->language;
  }

  public function setLanguage($language) {
    return ($this->language = $language);
  }

  public function getVersion() {
    return $this->version;
  }

  public function setVersion($version) {
    return ($this->version = $version);
  }

  public function getDialect() {
    return $this->dialect;
  }

  public function setDialect($dialect) {
    return ($this->dialect = $dialect);
  }
}
