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
class ContentTest extends PHPUnit_Framework_TestCase {

  /**
   *
   */
  public function setUp() {
    Template::setPath(DATA_DIR.'templates');
  }

  /**
   *
   */
  public function testRender() {
    $c = new Content('simple');
    $c->set('content', '');
    $this->assertSame('', $c->render());
    $c->set('content', 'PASS');
    $this->assertSame('PASS', $c->render());
  }

  /**
   *
   */
  public function testRender2() {
    $c = Content::create('simple');
    $c->set('content', '');
    $this->assertSame('', $c->render());
    $c->set('content', 'PASS');
    $this->assertSame('PASS', $c->render());
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
