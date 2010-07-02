<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################


class TagTestHTML401 extends PHPUnit_Framework_TestCase {
  public function setUp() {
    RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));
  }

  /**
   * @covers  Tag::base()
   */
  public function testBase() {
    $href = 'http://www.example.com/';
    $this->assertSame('<base href="'.$href.'">', Tag::base($href));
  }

  /**
   * @covers  Tag::br()
   */
  public function testBr() {
    $this->assertSame('<br>', Tag::br());
  }

  /**
   * @covers  Tag::hr()
   */
  public function testHr() {
    $this->assertSame('<hr>', Tag::hr());
  }

  /**
   * @covers  Tag::wbr()
   */
  public function testWbr() {
    $this->assertSame('<wbr>', Tag::wbr());
  }

  /**
   * @covers  Tag::isImpliedCData()
   */
  public function testIsImpliedCData() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertFalse(Tag::isImpliedCData($tag));
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
      'script' => '<!--',
      'style'  => '<!--',
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
      'script' => '//-->',
      'style'  => '-->',
    );
    foreach ($data as $tag => $mask) {
      $this->assertSame($mask, Tag::getContentMask($tag, false));
    }
  }

}
