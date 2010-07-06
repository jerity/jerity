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
   */
  public function testBase() {
    $href = 'http://www.example.com/';
    $this->assertSame('<base href="'.$href.'">', Tag::base($href));
  }

  /**
   */
  public function testBr() {
    $this->assertSame('<br>', Tag::br());
  }

  /**
   */
  public function testHr() {
    $this->assertSame('<hr>', Tag::hr());
  }

  /**
   */
  public function testWbr() {
    $this->assertSame('<wbr>', Tag::wbr());
  }

  /**
   */
  public function testIsImpliedCData() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertFalse(Tag::isImpliedCData($tag));
    }
  }

  /**
   */
  public function testShouldMaskContent() {
    $data = array('script', 'style');
    foreach ($data as $tag) {
      $this->assertTrue(Tag::shouldMaskContent($tag));
    }
  }

  /**
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
