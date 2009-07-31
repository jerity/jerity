<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/jerity.php');

class ChromeTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(dirname(dirname(__FILE__)).'/data/templates');
  }

  public function testCustomRelLink() {
    Chrome::clearLinks();

    Chrome::addLink('next', 'http://www.jerity.com/next');
    $l = Chrome::getLinks();

    $this->assertTrue(is_array($l));
    $this->assertEquals(count($l), 1);
    $this->assertEquals(count($l[0]), 2);
    $this->assertEquals($l[0]['href'], 'http://www.jerity.com/next');
    $this->assertEquals($l[0]['rel'],  'next');
  }

  public function testCustomRevLink() {
    Chrome::clearLinks();

    Chrome::addLink('author', 'mailto:info@jerity.com', true);
    $l = Chrome::getLinks();

    $this->assertTrue(is_array($l));
    $this->assertEquals(count($l), 1);
    $this->assertEquals(count($l[0]), 2);
    $this->assertEquals($l[0]['rev'],  'author');
    $this->assertEquals($l[0]['href'], 'mailto:info@jerity.com');
  }

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

  public function titleSeparatorTest($sep) {
    if ($sep === null) $sep = Chrome::getTitleSeparator();
    $title = array('Jerity', 'test', 'title');
    Chrome::setTitle($title);
    $this->assertEquals(implode($sep, $title), Chrome::getTitle($sep));
  }

  public function testTitleSeparator() {
    $tests = array(
      null,
      '',
      ' ',
      ' & ',
      '&',
      '&amp;',
      ' &amp; ',
      '&raquo;',
      ' &raquo; ',
    );
    foreach ($tests as $test) {
      $this->titleSeparatorTest($test);
    }
  }

  public function testGetTitleArray() {
    $title = array('Jerity', 'test', 'title');
    Chrome::setTitle($title);
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  public function testModularHead() {
    Chrome::setTitle('Test title');
    Chrome::addMetadata('generator', 'Jerity v0.1');
    Chrome::addMetadata('description', 'Jerity test case page');
    Chrome::addStylesheet('/css/common.css');
    Chrome::addStylesheet('/css/common.css');
    Chrome::addScript('/js/scriptaculous.js', 25);
    Chrome::addScript('/js/prototype.js', 15);
    Chrome::addIcon('/favicon.ico');
    Chrome::addIcon('/img/icons/favicon.png', Chrome::ICON_PNG);

    ob_start();
    Chrome::outputHead();
    $a = ob_get_clean();

    ob_start();
    echo RenderContext::getGlobalContext()->renderPreContent();
    Chrome::outputOpeningTags();
    Chrome::outputMetaTags();
    Chrome::outputTitleTag();
    Chrome::outputLinkTags();
    Chrome::outputStylesheetTags();
    Chrome::outputExternalScriptTags();
    Chrome::outputFaviconTags();
    Chrome::outputEndHead();
    $b = ob_get_clean();

    $this->assertSame($a, $b);
  }

  public function testRenderTitle() {
    $title = array('Jerity', 'test', 'title');
    Chrome::setTitle($title);
    Chrome::setTitleSeparator(' :: ');

    ob_start();
    Chrome::outputTitleTag();
    $d = ob_get_clean();

    $this->assertContains('<title>Jerity :: test :: title</title>', $d);

    $title = array('Jerity', 'test', 'title');
    Chrome::setTitle($title);
    Chrome::setTitleSeparator('::');

    ob_start();
    Chrome::outputTitleTag();
    $d = ob_get_clean();

    $this->assertContains('<title>Jerity::test::title</title>', $d);
  }

}
