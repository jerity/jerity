<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class ChromeTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(DATA_DIR.'templates');
  }

  ############################################################################
  # Title tests {{{

  public function testEmptyTitle() {
    Chrome::setTitle(null);
    $this->assertSame('', Chrome::getTitle());
  }

  /**
   * @dataProvider  titleSeparatorProvider
   */
  public function testTitleSeparator($sep) {
    if ($sep === null) $sep = Chrome::getTitleSeparator();
    $title = array('Jerity', 'test', 'title');
    Chrome::setTitle($title);
    $this->assertEquals(implode($sep, $title), Chrome::getTitle($sep));
  }

  public static function titleSeparatorProvider() {
    return array(
      array(null),
      array(''),
      array(' '),
      array(' & '),
      array('&'),
      array('&amp;'),
      array(' &amp; '),
      array('&raquo;'),
      array(' &raquo; '),
    );
  }

  public function testGetTitleArray() {
    $title = array('Jerity', 'test', 'title');
    Chrome::setTitle($title);
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  ############################################################################
  # Title append tests {{{

  public function testAddTitleAppend() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle('title');
    $title[] = 'title';
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  public function testAddTitleAppend2() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle(array('title'));
    $title[] = 'title';
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  public function testAddTitleAppend3() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle('title');
    Chrome::addToTitle('title');
    $title[] = 'title';
    $title[] = 'title';
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  public function testAddTitleAppend4() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle(array('title', 'title2'));
    $title[] = 'title';
    $title[] = 'title2';
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  # }}} Title append tests
  ############################################################################

  ############################################################################
  # Title prepend tests {{{

  public function testAddTitlePrepend() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle('title', true);
    array_unshift($title, 'title');
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  public function testAddTitlePrepend2() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle(array('title'), true);
    array_unshift($title, 'title');
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  public function testAddTitlePrepend3() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle('title', true);
    array_unshift($title, 'title');
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  # }}} Title prepend tests
  ############################################################################

  # }}} Title tests
  ############################################################################

  ############################################################################
  # Content tests {{{

  ############################################################################
  # Single content tests {{{

  public function testContent1() {
    $c = new Chrome('simple');
    $c->clearContent();
    $this->assertEquals(0, count($c->getContent()));
    $c->setContent('PASS');
    $this->assertEquals(1, count($c->getContent()));
    $c->setContent('PASS');
    $this->assertEquals(1, count($c->getContent()));
    $c->setContent('PASS', 'PASS');
    $this->assertEquals(2, count($c->getContent()));
    $c->setContent(array('PASS', 'PASS'));
    $this->assertEquals(2, count($c->getContent()));
    $c->clearContent();
    $this->assertEquals(0, count($c->getContent()));
  }

  public function testContent2() {
    $c = new Chrome('simple');
    $c->clearContent();
    $this->assertEquals(0, count($c->getContent()));

    $cont = new Content('simple');
    $c->setContent($cont);
    $this->assertEquals(1, count($c->getContent()));
    $c->setContent($cont);
    $this->assertEquals(1, count($c->getContent()));
    $c->setContent($cont, $cont);
    $this->assertEquals(2, count($c->getContent()));
    $c->setContent(array($cont, $cont));
    $this->assertEquals(2, count($c->getContent()));
    $c->clearContent();
    $this->assertEquals(0, count($c->getContent()));
  }

  # }}} Single content tests
  ############################################################################

  ############################################################################
  # Content failure tests {{{

  /**
   * @expectedException  InvalidArgumentException
   */
  public function testContentFail1() {
    $c = new Chrome('simple');
    $c->clearContent();
    $this->assertEquals(0, count($c->getContent()));
    $c->setContent(new stdClass());
  }

  /**
   * @expectedException  InvalidArgumentException
   */
  public function testContentFail2() {
    $c = new Chrome('simple');
    $c->clearContent();
    $this->assertEquals(0, count($c->getContent()));
    $c->setContent();
  }

  # }}} Content failure tests
  ############################################################################

  ############################################################################
  # Multiple content tests {{{

  public function testMultiContent1() {
    $c = new Chrome('multicontent');
    $c->clearContent();
    $c->setContent('PASS', 'PASS');
    $d = $c->render();
    $this->assertEquals('PASS|PASS|', $d);
  }

  public function testMultiContent1a() {
    $c = new Chrome('multicontent');
    $cont = new Content('simple');
    $cont->set('content', 'PASS');
    $c->clearContent();
    $c->setContent($cont, $cont);
    $d = $c->render();
    $this->assertEquals('PASS|PASS|', $d);
  }

  public function testMultiContent2() {
    $c = new Chrome('multicontent');
    $c->clearContent();
    $c->setContent('PASS', 'PASS', 'PASS');
    $d = $c->render();
    $this->assertEquals('PASS|PASS|PASS|', $d);
  }

  public function testMultiContent3() {
    $c = new Chrome('multicontent');
    $c->clearContent();
    $c->set('count', 3);
    $c->setContent('PASS', 'PASS');
    $d = $c->render();
    $this->assertEquals('PASS|PASS||', $d);
  }

  # }}} Multiple content tests
  ############################################################################

  # }}} Content tests
  ############################################################################

  ############################################################################
  # Modular head tests {{{

  public function testModularHead() {
    Chrome::setLanguage('en-gb');
    Chrome::setTitle('Test title');
    Chrome::clearMetadata();
    Chrome::addMetadata('Content-Type', 'text/html; charset=utf-8', true);
    Chrome::addMetadata('generator', 'Jerity');
    Chrome::addMetadata('description', 'Jerity test case page');
    Chrome::clearStylesheets();
    Chrome::addStylesheet('/css/common.css');
    Chrome::addStylesheet('/css/blah.css', 75);
    Chrome::clearScripts();
    Chrome::addScript('/js/scriptaculous.js', 25);
    Chrome::addScript('/js/prototype.js', 15);
    Chrome::clearIcons();
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

  # }}} Modular head tests
  ############################################################################

  ############################################################################
  # Foot tests {{{

  public function testFoot() {
    ob_start();
    Chrome::outputFoot();
    $a = ob_get_clean();

    $this->assertRegExp('#^</html>\s*$#s', $a);
  }

  # }}} Foot tests
  ############################################################################

  ############################################################################
  # Metadata tests {{{

  ############################################################################
  # Named metadata tests {{{

  public function testMetaName1() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata()));
    Chrome::addMetadata('generator', 'Jerity');
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    $this->assertEquals(1, count(Chrome::getMetadata()));
    Chrome::removeMetadata('generator');
    $this->assertEquals(0, count(Chrome::getMetadata()));
  }

  public function testMetaName2() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata()));
    Chrome::addMetadata('generator', 'Jerity');
    Chrome::addMetadata('description', 'Jerity Test Page');
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    $this->assertEquals(2, count(Chrome::getMetadata()));
    Chrome::removeMetadata('generator');
    $this->assertEquals(1, count(Chrome::getMetadata()));
    Chrome::removeMetadata('description');
    $this->assertEquals(0, count(Chrome::getMetadata()));
  }

  public function testMetaName3() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata()));
    Chrome::addMetadata('generator', 'Jerity');
    Chrome::addMetadata('description', 'Jerity Test Page');
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    $this->assertEquals(2, count(Chrome::getMetadata()));
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata()));
  }

  # }}} Named metadata tests
  ############################################################################

  ############################################################################
  # HTTP metadata tests {{{

  public function testMetaHttp1() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    Chrome::addMetadata('refresh', '60', true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));
    Chrome::removeMetadata('refresh', true);
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
  }

  public function testMetaHttp2() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    Chrome::addMetadata('refresh', '60', true);
    Chrome::addMetadata('content-type', 'text/html; charset=utf-8', true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(2, count(Chrome::getMetadata(true)));
    Chrome::removeMetadata('content-type', true);
    $this->assertEquals(1, count(Chrome::getMetadata(true)), 'Removing Content-Type');
    Chrome::removeMetadata('refresh', true);
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
  }

  public function testMetaHttp3() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    Chrome::addMetadata('refresh', '60', true);
    Chrome::addMetadata('content-type', 'text/html; charset=utf-8', true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(2, count(Chrome::getMetadata(true)));
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
  }

  # }}} HTTP metadata tests
  ############################################################################

  ############################################################################
  # Mixed metadata tests {{{

  public function testMetaMixed1() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(0, count(Chrome::getMetadata(true)));

    Chrome::addMetadata('refresh', '60', true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));

    Chrome::addMetadata('generator', 'Jerity');
    $this->assertEquals(1, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));

    Chrome::clearMetadata(true);
    $this->assertEquals(1, count(Chrome::getMetadata()));
    $this->assertEquals(0, count(Chrome::getMetadata(true)));

    Chrome::clearMetadata(false);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
  }

  public function testMetaMixed2() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(0, count(Chrome::getMetadata(true)));

    Chrome::addMetadata('refresh', '60', true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));

    Chrome::addMetadata('generator', 'Jerity');
    $this->assertEquals(1, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));

    Chrome::clearMetadata(false);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));

    Chrome::clearMetadata(true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
  }

  # }}} Mixed metadata tests
  ############################################################################

  # }}} Metadata tests
  ############################################################################

  ############################################################################
  # Link tests {{{

  public function testCustomRelLink() {
    Chrome::clearLinks();
    $this->assertEquals(0, count(Chrome::getLinks()));

    Chrome::addLink('next', 'http://www.jerity.com/next');
    $l = Chrome::getLinks();

    $this->assertTrue(is_array($l));
    $this->assertEquals(1, count($l));
    $this->assertEquals(2, count($l[0]));
    $this->assertEquals('next', $l[0]['rel']);
    $this->assertEquals('http://www.jerity.com/next', $l[0]['href']);
  }

  public function testCustomRevLink() {
    Chrome::clearLinks();
    $this->assertEquals(0, count(Chrome::getLinks()));

    Chrome::addLink('author', 'mailto:info@jerity.com', true);
    $l = Chrome::getLinks();

    $this->assertTrue(is_array($l));
    $this->assertEquals(1, count($l));
    $this->assertEquals(2, count($l[0]));
    $this->assertEquals('author', $l[0]['rev']);
    $this->assertEquals('mailto:info@jerity.com', $l[0]['href']);
  }

  # }}} Link tests
  ############################################################################

  ############################################################################
  # Script tests {{{

  function testScript1() {
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
    Chrome::addScript('/js/prototype.js');
    $this->assertEquals(1, count(Chrome::getScripts()));
    $s = Chrome::getScripts(null);
    $this->assertEquals($s[RenderContext::CONTENT_JS], Chrome::getScripts());
    Chrome::removeScript('/js/prototype.js');
    $this->assertEquals(0, count(Chrome::getScripts()));
    $s = Chrome::getScripts(null);
    $this->assertEquals($s[RenderContext::CONTENT_JS], Chrome::getScripts());
  }

  function testDuplicateScript() {
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
    Chrome::addScript('/js/prototype.js');
    $this->assertEquals(1, count(Chrome::getScripts()));
    Chrome::addScript('/js/prototype.js');
    $this->assertEquals(1, count(Chrome::getScripts()));
    Chrome::addScript('/js/prototype.js', 10);
    $this->assertEquals(1, count(Chrome::getScripts()));
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
  }

  function testScriptPriority1() {
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
    Chrome::addScript('/js/scriptaculous.js');
    Chrome::addScript('/js/prototype.js', 5);
    $this->assertEquals(2, count(Chrome::getScripts()));
    $s = Chrome::getScripts(null);
    $this->assertEquals($s[RenderContext::CONTENT_JS], Chrome::getScripts());
    $scriptArr = array(
      array('type'=>RenderContext::CONTENT_JS, 'src'=>'/js/prototype.js'),
      array('type'=>RenderContext::CONTENT_JS, 'src'=>'/js/scriptaculous.js'),
    );
    $this->assertEquals($scriptArr, Chrome::getScripts());
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
  }

  function testScriptPriority2() {
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
    Chrome::addScript('/js/scriptaculous.js');
    Chrome::addScript('/js/prototype.js', 5);
    Chrome::addScript('/js/misc.js', 15);
    $this->assertEquals(3, count(Chrome::getScripts()));
    $s = Chrome::getScripts(null);
    $this->assertEquals($s[RenderContext::CONTENT_JS], Chrome::getScripts());
    $scriptArr = array(
      array('type'=>RenderContext::CONTENT_JS, 'src'=>'/js/prototype.js'),
      array('type'=>RenderContext::CONTENT_JS, 'src'=>'/js/misc.js'),
      array('type'=>RenderContext::CONTENT_JS, 'src'=>'/js/scriptaculous.js'),
    );
    $this->assertEquals($scriptArr, Chrome::getScripts(), 'Expected array');
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
  }

  # }}} Script tests
  ############################################################################

}

# vim: ts=2 sw=2 et foldmethod=marker
