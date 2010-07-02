<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

require_once(dirname(__FILE__).'/ChromeTest.php'); // needed for separator provider

class ChromeTestHTML401 extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(DATA_DIR.'templates');
    RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));
  }

  /**
   * @covers  Chrome::outputLinkTags()
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
   * @covers        Chrome::outputTitleTag()
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
   * @covers  Chrome::__construct()
   * @covers  Chrome::render()
   */
  public function testFullRender() {
    $c = new Chrome('simple');
    $c->setContent('PASS');
    $this->assertSame('PASS', $c->render());
    $this->assertSame('PASS', (string)$c);
  }

  /**
   * @covers  Chrome::create()
   * @covers  Chrome::render()
   */
  public function testFullRenderByCreate() {
    $c = Chrome::create('simple');
    $c->setContent('PASS');
    $this->assertSame('PASS', $c->render());
    $this->assertSame('PASS', (string)$c);
  }

}

# vim: ts=2 sw=2 et foldmethod=marker
