<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

use \Jerity\Core\RenderContext;

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */
class RenderContextTest extends PHPUnit_Framework_TestCase {

  /**
   *
   */
  public function setUp() {
    RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));
  }

  /**
   *
   */
  public function testInitialGlobalContext() {
    $ctx = RenderContext::get();
    $this->assertType('\Jerity\Core\RenderContext', $ctx);
  }

  /**
   * Check that a new stack is created if we get from an empty global context.
   */
  public function testGetGlobalContextNullStack() {
    RenderContext::set(null); # Need to replace SplStack with null.
    $this->assertNull(RenderContext::get());
  }

  /**
   * Check that a new stack is created if we push to an empty global context.
   */
  public function testPushGlobalContextNullStack() {
    RenderContext::set(null); # Need to replace SplStack with null.
    $this->assertNull(RenderContext::push());
  }

  /**
   * Check that a new stack is created if we get from an empty global context.
   */
  public function testPopGlobalContextNullStack() {
    RenderContext::set(null); # Needed to replace SplStack with null.
    $this->assertNull(RenderContext::pop());
  }

  /**
   *
   */
  public function testPushPopContext() {
    $ctx1 = RenderContext::get();
    $this->assertType('\Jerity\Core\RenderContext', $ctx1);

    $newctx = RenderContext::create(RenderContext::TYPE_HTML5);
    RenderContext::push($newctx);
    $ctx2 = RenderContext::get();
    $this->assertNotSame($ctx1, $ctx2);
    $this->assertNotEquals($ctx1, $ctx2);
    $this->assertEquals($newctx, $ctx2);
    $this->assertSame($newctx, $ctx2);

    $ctx2a = RenderContext::pop();
    $this->assertEquals($newctx, $ctx2a);
    $this->assertSame($newctx, $ctx2a);
    $ctx1a = RenderContext::get();
    $this->assertEquals($ctx1, $ctx1a);
    $this->assertSame($ctx1, $ctx1a);
  }

  /**
   *
   */
  public function testEmptyGlobalContext() {
    $ctxs = array();
    while ($ctx = RenderContext::pop()) $ctxs[] = $ctx;

    $ctx = RenderContext::get();
    $this->assertSame(null, $ctx);

    $ctxs = array_reverse($ctxs);
    foreach ($ctxs as $ctx) RenderContext::push($ctx);
  }

  /**
   * @dataProvider  createProvider
   */
  public function testCreate($type) {
    $bits = explode('-', $type);
    while (count($bits) < 3) $bits[] = null;
    list($language, $version, $dialect) = $bits;
    $version = doubleval($version);
    if (is_null($dialect)) $dialect='';

    $ctx = RenderContext::create($type);
    $this->assertSame($language,  $ctx->getLanguage());
    $this->assertEquals($version, $ctx->getVersion());
    $this->assertSame($dialect,   $ctx->getDialect());
  }

  /**
   *
   */
  public static function createProvider() {
    return array(
      array(RenderContext::TYPE_HTML4_STRICT),
      array(RenderContext::TYPE_HTML4_TRANSITIONAL),
      array(RenderContext::TYPE_HTML4_FRAMESET),
      array(RenderContext::TYPE_HTML5),
      array(RenderContext::TYPE_XHTML1_STRICT),
      array(RenderContext::TYPE_XHTML1_TRANSITIONAL),
      array(RenderContext::TYPE_XHTML1_FRAMESET),
      array(RenderContext::TYPE_XHTML5),
    );
  }

  /**
   * @expectedException  \InvalidArgumentException
   */
  public function testCreateFail() {
    $ctx = RenderContext::create('js-1.1');
  }

  /**
   * @dataProvider  getDoctypeProvider
   */
  public function testGetDoctype($language, $version, $dialect, $xhtml_1_0_compat, $expected) {
    $ctx = new RenderContext();
    $ctx->setLanguage($language);
    $ctx->setVersion($version);
    $ctx->setDialect($dialect);
    if ($xhtml_1_0_compat) $ctx->setXHTMLCompatMode($xhtml_1_0_compat);

    $this->assertSame($language,           $ctx->getLanguage());
    $this->assertEquals($version,          $ctx->getVersion());
    $this->assertSame($dialect,            $ctx->getDialect());
    $this->assertEquals($xhtml_1_0_compat, $ctx->getXHTMLCompatMode());
    $this->assertSame($expected,           $ctx->getDoctype());

    $ctx = new RenderContext($language, $version, $dialect);

    $this->assertSame($language,  $ctx->getLanguage());
    $this->assertEquals($version, $ctx->getVersion());
    $this->assertSame($dialect,   $ctx->getDialect());

    $ctx->setCharset('utf-8');
    if ($xhtml_1_0_compat) $ctx->setXHTMLCompatMode($xhtml_1_0_compat);

    $pre_content = $ctx->renderPreContent();
    if ($ctx->getLanguage() == RenderContext::LANG_XML
      || ($ctx->getLanguage() == RenderContext::LANG_XHTML
      && !$ctx->getXHTMLCompatMode())) {
      $this->assertContains('<'.'?xml version="1.0" encoding="utf-8" ?'.">\n", $pre_content);
      if ($expected !== '') $this->assertContains($expected, $pre_content);
    } elseif ($pre_content !== '') {
      $this->assertSame($expected."\n", $pre_content);
    }
  }

  /**
   *
   */
  public static function getDoctypeProvider() {
    return array(
      array(RenderContext::LANG_HTML , 4.01, RenderContext::DIALECT_STRICT      , false, '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'),
      array(RenderContext::LANG_HTML , 4.01, RenderContext::DIALECT_TRANSITIONAL, false, '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'),
      array(RenderContext::LANG_HTML , 4.01, RenderContext::DIALECT_FRAMESET    , false, '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'),
      array(RenderContext::LANG_HTML , 5   , RenderContext::DIALECT_NONE        , false, '<!DOCTYPE html>'),
      array(RenderContext::LANG_XHTML, 1.0 , RenderContext::DIALECT_STRICT      , false, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , RenderContext::DIALECT_TRANSITIONAL, false, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , RenderContext::DIALECT_FRAMESET    , false, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , RenderContext::DIALECT_STRICT      , true , '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , RenderContext::DIALECT_TRANSITIONAL, true , '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , RenderContext::DIALECT_FRAMESET    , true , '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">'),
      array(RenderContext::LANG_XHTML, 5   , RenderContext::DIALECT_NONE        , false, '<!DOCTYPE html>'),
      array(RenderContext::LANG_XML  , 1.0 , RenderContext::DIALECT_NONE        , false, ''),
      array(RenderContext::LANG_CSS  , 3   , RenderContext::DIALECT_NONE        , false, ''),
      array(RenderContext::LANG_JS   , 1.8 , RenderContext::DIALECT_NONE        , false, ''),
      array(RenderContext::LANG_JSON , 0   , RenderContext::DIALECT_NONE        , false, ''),
      array(RenderContext::LANG_TEXT , 0   , RenderContext::DIALECT_NONE        , false, ''),
    );
  }

  /**
   * @dataProvider  getDoctypeFailProvider
   *
   * @expectedException  \InvalidArgumentException
   */
  public function testGetDoctypeFail($language, $version, $dialect) {
    $ctx = new RenderContext($language, $version, $dialect);
    $dt = $ctx->getDoctype();
  }

  /**
   *
   */
  public static function getDoctypeFailProvider() {
    return array(
      array(RenderContext::LANG_HTML , 3   , RenderContext::DIALECT_NONE  ),
      array(RenderContext::LANG_HTML , 4.01, RenderContext::DIALECT_NONE  ),
      array(RenderContext::LANG_XHTML, 1.00, RenderContext::DIALECT_NONE  ),
      array(RenderContext::LANG_XHTML, 1.05, RenderContext::DIALECT_STRICT),
    );
  }

  /**
   * @dataProvider  contentTypeProvider
   */
  public function testContentTypeDetection($language, $dialect, $xhtml_1_0_compat, $expected) {
    # Cheat and set version to 1.0 if testing XHTML 1.0 compatibility
    $ctx = new RenderContext($language, ($xhtml_1_0_compat ? 1.0 : null), $dialect);
    if ($xhtml_1_0_compat) $ctx->setXHTMLCompatMode($xhtml_1_0_compat);
    $this->assertSame($expected, $ctx->getContentType());
  }

  /**
   *
   */
  public static function contentTypeProvider() {
    return array(
      array(RenderContext::LANG_HTML , RenderContext::DIALECT_STRICT, false, RenderContext::CONTENT_HTML),
      array(RenderContext::LANG_XHTML, RenderContext::DIALECT_STRICT, false, RenderContext::CONTENT_XHTML),
      array(RenderContext::LANG_XHTML, RenderContext::DIALECT_STRICT, true , RenderContext::CONTENT_HTML),
      array(RenderContext::LANG_XML  , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_XML),
      array(RenderContext::LANG_CSS  , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_CSS),
      array(RenderContext::LANG_JS   , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_JS),
      array(RenderContext::LANG_JSON , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_JSON),
      array(RenderContext::LANG_TEXT , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_TEXT),
      array('binary'                 , RenderContext::DIALECT_NONE  , false, 'application/octet-stream'),
      array(''                       , RenderContext::DIALECT_NONE  , false, 'application/octet-stream'),
    );
  }

  /**
   *
   */
  public function testContentTypeCache() {
    $ctx = RenderContext::create(RenderContext::TYPE_HTML5);
    $this->assertSame(RenderContext::CONTENT_HTML, $ctx->getContentType());
    $this->assertSame(RenderContext::CONTENT_HTML, $ctx->getContentType());
  }

  /**
   * @expectedException PHPUnit_Framework_Error
   */
  public function testSetXHTMLCompatModeError() {
    $ctx = RenderContext::create(RenderContext::TYPE_HTML4_STRICT);
    $ctx->setXHTMLCompatMode(true);
  }

  /**
   * @dataProvider  isXMLSyntaxProvider
   */
  public function testIsXMLSyntax($language, $expected) {
    $ctx = new RenderContext($language);
    $this->assertSame($expected, $ctx->isXMLSyntax());
  }

  /**
   *
   */
  public static function isXMLSyntaxProvider() {
    return array(
      array(RenderContext::LANG_HTML,  false),
      array(RenderContext::LANG_XHTML, true ),
      array(RenderContext::LANG_XML  , true ),
      array(RenderContext::LANG_CSS  , false),
      array(RenderContext::LANG_JS   , false),
      array(RenderContext::LANG_JSON , false),
      array(RenderContext::LANG_TEXT , false),
      array('binary'                 , false),
      array(''                       , false),
    );
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
