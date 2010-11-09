<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

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
  public function testInitialGlobalContext() {
    $ctx = RenderContext::get();
    $this->assertType('RenderContext', $ctx);
  }

  /**
   *
   */
  public function testPushPopContext() {
    $ctx1 = RenderContext::get();
    $this->assertType('RenderContext', $ctx1);

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
    while ($ctx = RenderContext::pop()) {
      $ctxs[] = $ctx;
    }

    $ctx = RenderContext::get();
    $this->assertSame(null, $ctx);

    $ctxs = array_reverse($ctxs);
    foreach ($ctxs as $ctx) {
      RenderContext::push($ctx);
    }
  }

  /**
   * @dataProvider  createProvider
   */
  public function testCreate($type) {
    $bits = explode('-', $type);
    while (count($bits) < 3) {
      $bits[] = null;
    }
    list($language, $version, $dialect) = $bits;
    $version = doubleval($version);
    if (is_null($dialect))  $dialect='';

    $ctx = RenderContext::create($type);
    $this->assertSame(  $language, $ctx->getLanguage());
    $this->assertEquals($version,  $ctx->getVersion());
    $this->assertSame(  $dialect,  $ctx->getDialect());
  }

  /**
   *
   */
  public static function createProvider() {
    return array(
      array(RenderContext::TYPE_HTML4_FRAMESET),
      array(RenderContext::TYPE_HTML4_STRICT),
      array(RenderContext::TYPE_HTML4_TRANSITIONAL),
      array(RenderContext::TYPE_HTML5),
      array(RenderContext::TYPE_XHTML1_FRAMESET),
      array(RenderContext::TYPE_XHTML1_MOBILE),
      array(RenderContext::TYPE_XHTML1_STRICT),
      array(RenderContext::TYPE_XHTML1_TRANSITIONAL),
      array(RenderContext::TYPE_XHTML1_1),
      array(RenderContext::TYPE_XHTML1_1_MOBILE),
      array(RenderContext::TYPE_XHTML1_2_MOBILE),
      array(RenderContext::TYPE_XHTML5),
    );
  }

  /**
   * @expectedException  InvalidArgumentException
   */
  public function testCreateFail() {
    $ctx = RenderContext::create('js-1.1');
  }

  /**
   * @dataProvider  getDoctypeProvider
   */
  public function testGetDoctype($lang, $ver, $xhtml_1_0_compat, $dialect, $expected) {
    $ctx = new RenderContext();
    $ctx->setLanguage($lang);
    $ctx->setVersion($ver);
    $ctx->setXHTML1CompatibilityMode($xhtml_1_0_compat);
    $ctx->setDialect($dialect);
    $this->assertSame($lang,               $ctx->getLanguage());
    $this->assertEquals($ver,              $ctx->getVersion());
    $this->assertEquals($xhtml_1_0_compat, $ctx->getXHTML1CompatibilityMode());
    $this->assertSame($dialect,            $ctx->getDialect());
    $this->assertSame($expected,           $ctx->getDoctype());

    $ctx = new RenderContext($lang, $ver, $dialect);
    $this->assertSame($lang,     $ctx->getLanguage());
    $this->assertEquals($ver,    $ctx->getVersion());
    $this->assertSame($dialect,  $ctx->getDialect());

    $ctx->setCharset('utf-8');

    $ctx->setXHTML1CompatibilityMode($xhtml_1_0_compat);
    $preContent = $ctx->renderPreContent();
    if ($ctx->getLanguage() == RenderContext::LANG_XML ||
      ($ctx->getLanguage() == RenderContext::LANG_XHTML && !$ctx->getXHTML1CompatibilityMode())) {
      $this->assertContains('<'.'?xml version="1.0" encoding="utf-8" ?'.">\n", $preContent);
      if ($expected !== '') {
        $this->assertContains($expected, $preContent);
      }
    } elseif ($preContent !== '') {
      $this->assertSame($expected."\n", $preContent);
    }
  }

  /**
   *
   */
  public static function getDoctypeProvider() {
    return array(
      array(RenderContext::LANG_HTML , 2   , false, RenderContext::DIALECT_NONE        , '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">'),
      array(RenderContext::LANG_HTML , 3.2 , false, RenderContext::DIALECT_NONE        , '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">'),
      array(RenderContext::LANG_HTML , 4.01, false, RenderContext::DIALECT_STRICT      , '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'),
      array(RenderContext::LANG_HTML , 4.01, false, RenderContext::DIALECT_TRANSITIONAL, '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'),
      array(RenderContext::LANG_HTML , 4.01, false, RenderContext::DIALECT_FRAMESET    , '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'),
      array(RenderContext::LANG_HTML , 5   , false, RenderContext::DIALECT_NONE        , '<!DOCTYPE html>'),
      array(RenderContext::LANG_XHTML, 1.0 , false, RenderContext::DIALECT_STRICT      , '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , false, RenderContext::DIALECT_TRANSITIONAL, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , false, RenderContext::DIALECT_FRAMESET    , '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , true , RenderContext::DIALECT_STRICT      , '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , true , RenderContext::DIALECT_TRANSITIONAL, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , true , RenderContext::DIALECT_FRAMESET    , '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">'),
      array(RenderContext::LANG_XHTML, 1.1 , false, RenderContext::DIALECT_NONE        , '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'),
      array(RenderContext::LANG_XHTML, 1.0 , false, RenderContext::DIALECT_MOBILE      , '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">'),
      array(RenderContext::LANG_XHTML, 1.1 , false, RenderContext::DIALECT_MOBILE      , '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.1//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd">'),
      array(RenderContext::LANG_XHTML, 1.2 , false, RenderContext::DIALECT_MOBILE      , '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">'),
      array(RenderContext::LANG_XHTML, 5   , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_CSS  , 2   , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_FBJS , 0   , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_FBML , 0   , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_JS   , 1.6 , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_JSON , 0   , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_MHTML, 0   , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_TEXT , 0   , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_WML  , 1   , false, RenderContext::DIALECT_NONE        , ''),
      array(RenderContext::LANG_XML  , 1.0 , false, RenderContext::DIALECT_NONE        , ''),
    );
  }

  /**
   * @dataProvider  getDoctypeFailProvider
   *
   * @expectedException  InvalidArgumentException
   */
  public function testGetDoctypeFail($lang, $ver, $dialect) {
    $ctx = new RenderContext($lang, $ver, $dialect);
    $dt = $ctx->getDoctype();
  }

  /**
   *
   */
  public static function getDoctypeFailProvider() {
    return array(
      array(RenderContext::LANG_HTML , 3   , RenderContext::DIALECT_NONE        ),
      array(RenderContext::LANG_HTML , 4.01, RenderContext::DIALECT_NONE        ),
      array(RenderContext::LANG_XHTML, 1.05, RenderContext::DIALECT_STRICT      ),
    );
  }

  /**
   * @dataProvider  contentTypeProvider
   */
  public function testContentTypeDetection($lang, $dialect, $xhtml_1_0_compat, $expected) {
    # Cheat and set version to 1.0 if testing XHTML 1.0 compatibility
    $ctx = new RenderContext($lang, ($xhtml_1_0_compat ? 1.0 : null), $dialect);
    $ctx->setXHTML1CompatibilityMode($xhtml_1_0_compat);
    $this->assertSame($expected, $ctx->getContentType());
  }

  /**
   *
   */
  public static function contentTypeProvider() {
    return array(
      array(RenderContext::LANG_HTML , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_HTML),
      array(RenderContext::LANG_XHTML, RenderContext::DIALECT_MOBILE, false, RenderContext::CONTENT_XHTML_MP),
      array(RenderContext::LANG_XHTML, RenderContext::DIALECT_STRICT, false, RenderContext::CONTENT_XHTML),
      array(RenderContext::LANG_XHTML, RenderContext::DIALECT_STRICT, true , RenderContext::CONTENT_HTML),
      array(RenderContext::LANG_JS   , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_JS),
      array(RenderContext::LANG_FBJS , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_JS),
      array(RenderContext::LANG_TEXT , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_TEXT),
      array(RenderContext::LANG_XML  , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_XML),
      array(RenderContext::LANG_FBML , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_XML),
      array(RenderContext::LANG_JSON , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_JSON),
      array(RenderContext::LANG_CSS  , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_CSS),
      array(RenderContext::LANG_MHTML, RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_XHTML_MP),
      array(RenderContext::LANG_WML  , RenderContext::DIALECT_NONE  , false, RenderContext::CONTENT_WML),
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
   * @dataProvider  xmlSyntaxProvider
   */
  public function testIsXMLSyntax($lang, $expected) {
    $ctx = new RenderContext($lang);
    $this->assertSame($expected, $ctx->isXMLSyntax());
  }

  /**
   *
   */
  public static function xmlSyntaxProvider() {
    return array(
      array(RenderContext::LANG_CSS  , false),
      array(RenderContext::LANG_FBJS , false),
      array(RenderContext::LANG_FBML , true ),
      array(RenderContext::LANG_HTML,  false),
      array(RenderContext::LANG_JS   , false),
      array(RenderContext::LANG_JSON , false),
      array(RenderContext::LANG_MHTML, false),
      array(RenderContext::LANG_TEXT , false),
      array(RenderContext::LANG_WML  , true ),
      array(RenderContext::LANG_XHTML, true ),
      array(RenderContext::LANG_XML  , true ),
      array('binary'                 , false),
      array(''                       , false),
    );
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
