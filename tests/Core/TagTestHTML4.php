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
class TagTestHTML4 extends PHPUnit_Framework_TestCase {

  /**
   *
   */
  public function setUp() {
    RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));
  }

  /**
   *
   */
  public function testBr() {
    $this->assertSame('<br>', (string) Tag::br());
  }

  /**
   *
   */
  public function testHr() {
    $this->assertSame('<hr>', (string) Tag::hr());
  }

  /**
   *
   */
  public function testImg() {
    $src = 'example.png';
    $this->assertSame('<img src="'.$src.'">', (string) Tag::img($src));
  }

  /**
   *
   */
  public function testScript() {
    # Default empty script tag:
    $this->assertSame(
      '<script type="text/javascript"></script>',
      (string) Tag::script());
    # Override content type:
    $this->assertSame(
      '<script type="text/html"></script>',
      (string) Tag::script()->type('text/html'));
    # Specify a source:
    $this->assertSame(
      '<script src="example.js" type="text/javascript"></script>',
      (string) Tag::script('example.js'));
    # Specify some content:
    $this->assertSame(
      "<script type=\"text/javascript\">\nalert('Hello World!');\n</script>",
      (string) Tag::script()->_('alert(\'Hello World!\');'));
  }

  /**
   *
   */
  public function testStyle() {
    # Default empty style tag:
    $this->assertSame(
      '<style type="text/css"></style>',
      (string) Tag::style());
    # Override content type:
    $this->assertSame(
      '<style type="text/x-less"></style>',
      (string) Tag::style()->type('text/x-less'));
    # Specify some content:
    $this->assertSame(
      "<style type=\"text/css\">\nbody { margin: 0; }\n</style>",
      (string) Tag::style()->_('body { margin: 0; }'));
  }

  /**
   *
   */
  public function testWbr() {
    $this->assertSame('<wbr>', (string) Tag::wbr()->_(false));
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
