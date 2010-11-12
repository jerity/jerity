<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

use \Jerity\Core\RenderContext;
use \Jerity\Layout\Chrome;

# Needed for separator provider
require_once(dirname(__FILE__).'/ChromeTest.php');

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */
class ChromeTestHTML401 extends PHPUnit_Framework_TestCase {

  /**
   *
   */
  public function setUp() {
    Template::setPath(DATA_DIR.'templates');
    RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));
  }

  /**
   *
   */
  public function testCustomLinkRender() {
    Chrome::clearLinks();

    Chrome::addLink('next', 'http://www.jerity.com/next');
    Chrome::addLink('author', 'mailto:info@jerity.com', true);

    ob_start();
    Chrome::outputLinkTags();
    $d = ob_get_clean();

    $this->assertContains('<link rel="next" href="http://www.jerity.com/next">', $d);
    $this->assertContains('<link rev="author" href="mailto:info@jerity.com">', $d);
  }

  /**
   * @dataProvider  ChromeTest::titleSeparatorProvider
   */
  public function testTitleRender($sep) {
    if (is_null($sep)) $sep = Chrome::getTitleSeparator();
    $title = array('Jerity', 'test', 'title');
    Chrome::setTitle($title);
    Chrome::setTitleSeparator($sep);

    ob_start();
    Chrome::outputTitleTag();
    $d = ob_get_clean();

    $this->assertContains('<title>'.implode($sep, $title).'</title>', $d);
  }

  /**
   *
   */
  public function testFullRender() {
    $c = new Chrome('simple');
    $c->setContent('PASS');
    $this->assertSame('PASS', $c->render());
    $this->assertSame('PASS', (string)$c);
  }

  /**
   *
   */
  public function testFullRenderByCreate() {
    $c = Chrome::create('simple');
    $c->setContent('PASS');
    $this->assertSame('PASS', $c->render());
    $this->assertSame('PASS', (string)$c);
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
