<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class TagTestXHTML10 extends PHPUnit_Framework_TestCase {
  public function setUp() {
    RenderContext::setGlobalContext(RenderContext::makeContext(RenderContext::TYPE_XHTML1_STRICT));
  }

  /**
   * @covers  Tag::base()
   */
  public function testBase() {
    $href = 'http://www.example.com/';
    $this->assertSame('<base href="'.$href.'" />', Tag::base($href));
  }

  /**
   * @covers  Tag::br()
   */
  public function testBr() {
    $this->assertSame('<br />', Tag::br());
  }

  /**
   * @covers  Tag::hr()
   */
  public function testHr() {
    $this->assertSame('<hr />', Tag::hr());
  }

  /**
   * @covers  Tag::wbr()
   */
  public function testWbr() {
    $this->assertSame('<wbr />', Tag::wbr());
  }

  /**
   * @covers  Tag::isImpliedCData()
   */
  public function testIsImpliedCData() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertTrue(Tag::isImpliedCData($tag));
    }
  }

  /**
   * @covers  Tag::shouldMaskContent()
   */
  public function testShouldMaskContent() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertTrue(Tag::shouldMaskContent($tag));
    }
  }

  /**
   * @covers  Tag::getContentMask()
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
   * @covers  Tag::getContentMask()
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
