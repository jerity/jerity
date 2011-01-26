<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

use \Jerity\Core\RenderContext;
use \Jerity\Core\Tag;

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */
class TagTestXHTML10 extends PHPUnit_Framework_TestCase {

  /**
   *
   */
  public function setUp() {
    RenderContext::set(RenderContext::create(RenderContext::TYPE_XHTML1_STRICT));
  }

  /**
   *
   */
  public function testBase() {
    $href = 'http://www.example.com/';
    $this->assertSame('<base href="'.$href.'" />', Tag::base($href));
  }

  /**
   *
   */
  public function testBr() {
    $this->assertSame('<br />', Tag::br());
  }

  /**
   *
   */
  public function testHr() {
    $this->assertSame('<hr />', Tag::hr());
  }

  /**
   *
   */
  public function testWbr() {
    $this->assertSame('<wbr />', Tag::wbr());
  }

  /**
   *
   */
  public function testIsImpliedCData() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertTrue(Tag::isImpliedCData($tag));
    }
  }

  /**
   *
   */
  public function testShouldMaskContent() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertTrue(Tag::shouldMaskContent($tag));
    }
  }

  /**
   *
   */
  public function testGetContentMaskOpen() {
    $data = array(
      'script' => '<!--//--><![CDATA[//><!--',
      'style'  => '<!--/*--><![CDATA[/*><!--*/',
    );
    foreach ($data as $tag => $mask) {
      $this->assertSame($mask, Tag::getContentMask($tag, true));
    }
  }

  /**
   *
   */
  public function testGetContentMaskClose() {
    $data = array(
      'script' => '//--><!]]>',
      'style'  => '/*]]>*/-->',
    );
    foreach ($data as $tag => $mask) {
      $this->assertSame($mask, Tag::getContentMask($tag, false));
    }
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
