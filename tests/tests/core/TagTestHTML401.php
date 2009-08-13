<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class TagTestHTML401 extends PHPUnit_Framework_TestCase {
  public function setUp() {
    RenderContext::setGlobalContext(RenderContext::makeContext(RenderContext::TYPE_HTML4_STRICT));
  }

  public function testBase() {
    $href = 'http://www.example.com/';
    $this->assertSame('<base href="'.$href.'">', Tag::base($href));
  }

  public function testBr() {
    $this->assertSame('<br>', Tag::br());
  }

  public function testHr() {
    $this->assertSame('<hr>', Tag::hr());
  }

  public function testWbr() {
    $this->assertSame('<wbr>', Tag::wbr());
  }

  public function testIsImpliedCData() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertFalse(Tag::isImpliedCData($tag));
    }
  }

  public function testShouldMaskContent() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertTrue(Tag::shouldMaskContent($tag));
    }
  }

  public function testGetContentMaskOpen() {
    $data = array(
      'script' => '<!--',
      'style'  => '<!--',
    );
    foreach ($data as $tag => $mask) {
      $this->assertSame($mask, Tag::getContentMask($tag, true));
    }
  }

  public function testGetContentMaskClose() {
    $data = array(
      'script' => '//-->',
      'style'  => '-->',
    );
    foreach ($data as $tag => $mask) {
      $this->assertSame($mask, Tag::getContentMask($tag, false));
    }
  }

}
