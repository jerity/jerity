<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class ChromeTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(DATA_DIR.'templates');
  }

  ############################################################################
  # Title tests {{{

  /**
   * @covers  Chrome::setTitle()
   * @covers  Chrome::getTitle()
   */
  public function testEmptyTitle() {
    Chrome::setTitle(null);
    $this->assertSame('', Chrome::getTitle());
  }

  /**
   * @dataProvider  titleSeparatorProvider
   * @covers        Chrome::getTitleSeparator()
   * @covers        Chrome::getTitle()
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

  /**
   * @covers  Chrome::getTitle()
   */
  public function testGetTitleArray() {
    $title = array('Jerity', 'test', 'title');
    Chrome::setTitle($title);
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  ############################################################################
  # Title append tests {{{

  /**
   * @covers  Chrome::addToTitle()
   */
  public function testAddTitleAppend() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle('title');
    $title[] = 'title';
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  /**
   * @covers  Chrome::addToTitle()
   */
  public function testAddTitleAppend2() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle(array('title'));
    $title[] = 'title';
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  /**
   * @covers  Chrome::addToTitle()
   */
  public function testAddTitleAppend3() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle('title');
    Chrome::addToTitle('title');
    $title[] = 'title';
    $title[] = 'title';
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  /**
   * @covers  Chrome::addToTitle()
   */
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

  /**
   * @covers  Chrome::addToTitle()
   */
  public function testAddTitlePrepend() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle('title', true);
    array_unshift($title, 'title');
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  /**
   * @covers  Chrome::addToTitle()
   */
  public function testAddTitlePrepend2() {
    $title = array('Jerity', 'test');
    Chrome::setTitle($title);
    Chrome::addToTitle(array('title'), true);
    array_unshift($title, 'title');
    $this->assertEquals($title, Chrome::getTitle(false));
  }

  /**
   * @covers  Chrome::addToTitle()
   */
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

  /**
   * @covers  Chrome::__construct()
   * @covers  Chrome::clearContent()
   * @covers  Chrome::getContent()
   * @covers  Chrome::setContent()
   */
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

  /**
   * @covers  Chrome::__construct()
   * @covers  Chrome::clearContent()
   * @covers  Chrome::getContent()
   */
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
   * @covers             Chrome::setContent()
   */
  public function testContentFail1() {
    $c = new Chrome('simple');
    $c->clearContent();
    $this->assertEquals(0, count($c->getContent()));
    $c->setContent(new stdClass());
  }

  /**
   * @expectedException  InvalidArgumentException
   * @covers             Chrome::setContent()
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

  /**
   * @covers  Chrome::__construct()
   * @covers  Chrome::clearContent()
   * @covers  Chrome::setContent()
   * @covers  Chrome::render()
   * @covers  Chrome::getNextContent()
   */
  public function testMultiContent1() {
    $c = new Chrome('multicontent');
    $c->clearContent();
    $c->setContent('PASS', 'PASS');
    $d = $c->render();
    $this->assertEquals('PASS|PASS|', $d);
  }

  /**
   * @covers  Chrome::__construct()
   * @covers  Chrome::clearContent()
   * @covers  Chrome::setContent()
   * @covers  Chrome::render()
   * @covers  Chrome::getNextContent()
   */
  public function testMultiContent1a() {
    $c = new Chrome('multicontent');
    $cont = new Content('simple');
    $cont->set('content', 'PASS');
    $c->clearContent();
    $c->setContent($cont, $cont);
    $d = $c->render();
    $this->assertEquals('PASS|PASS|', $d);
  }

  /**
   * @covers  Chrome::__construct()
   * @covers  Chrome::clearContent()
   * @covers  Chrome::setContent()
   * @covers  Chrome::render()
   * @covers  Chrome::getNextContent()
   */
  public function testMultiContent2() {
    $c = new Chrome('multicontent');
    $c->clearContent();
    $c->setContent('PASS', 'PASS', 'PASS');
    $d = $c->render();
    $this->assertEquals('PASS|PASS|PASS|', $d);
  }

  /**
   * @covers  Chrome::__construct()
   * @covers  Chrome::clearContent()
   * @covers  Chrome::setContent()
   * @covers  Chrome::render()
   * @covers  Chrome::getNextContent()
   */
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

  /**
   * @covers  Chrome::outputHead()
   */
  public function testModularHead() {
    Chrome::setLanguage('en-gb');
    Chrome::setTitle('Test title');
    Chrome::clearMetadata();
    Chrome::addMetadata('generator', 'Jerity');
    Chrome::addMetadata('description', 'Jerity test case page');
    Chrome::clearStylesheets();
    Chrome::addStylesheet('/css/common.css', 15);
    Chrome::addStylesheet('/css/blah.css', 75);
    Chrome::addAlternateStylesheet('/css/theme1.css', 'Theme One', true);
    Chrome::addAlternateStylesheet('/css/theme2.css', 'Theme Two', false);
    Chrome::addAlternateStylesheet('/css/theme3.css', 'Theme Three', false);
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
    Chrome::outputHeaders();
    echo RenderContext::get()->renderPreContent();
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

  /**
   * @covers  Chrome::outputFoot()
   */
  public function testFoot() {
    ob_start();
    Chrome::outputFoot();
    $a = ob_get_clean();

    $this->assertRegExp('#^</html>\s*$#s', $a);
  }

  # }}} Foot tests
  ############################################################################

  ############################################################################
  # HTTP header tests {{{

  /**
   * @covers  Chrome::clearHeaders()
   * @covers  Chrome::getHeaders()
   * @covers  Chrome::addHeader()
   * @covers  Chrome::removeHeader()
   */
  public function testHTTPHeader1() {
    Chrome::clearHeaders();
    $this->assertEquals(0, count(Chrome::getHeaders()));
    Chrome::addHeader('X-Test', '1, 2, 3.');
    $h = Chrome::getHeaders();
    $this->assertEquals(1, count($h));
    $this->assertEquals(1, count($h['X-Test']));
    Chrome::addHeader('X-Test', '4, 5, 6.');
    $h = Chrome::getHeaders();
    $this->assertEquals(1, count($h));
    $this->assertEquals(1, count($h['X-Test']));
    Chrome::addHeader('X-Test2', '1, 2, 3.');
    $this->assertEquals(2, count(Chrome::getHeaders()));
    Chrome::removeHeader('X-Test');
    $this->assertEquals(1, count(Chrome::getHeaders()));
    Chrome::removeHeader('X-Test2');
    $this->assertEquals(0, count(Chrome::getHeaders()));
  }

  /**
   * @covers  Chrome::clearHeaders()
   * @covers  Chrome::getHeaders()
   * @covers  Chrome::addHeader()
   * @covers  Chrome::removeHeader()
   */
  public function testHTTPHeader2() {
    Chrome::clearHeaders();
    $this->assertEquals(0, count(Chrome::getHeaders()));
    Chrome::addHeader('X-Test', '1, 2, 3.');
    $h = Chrome::getHeaders();
    $this->assertEquals(1, count($h));
    $this->assertEquals(1, count($h['X-Test']));
    Chrome::addHeader('X-Test', '4, 5, 6.', false);
    $h = Chrome::getHeaders();
    $this->assertEquals(1, count($h));
    $this->assertEquals(2, count($h['X-Test']));
    Chrome::addHeader('X-Test2', '1, 2, 3.');
    $this->assertEquals(2, count(Chrome::getHeaders()));
    Chrome::removeHeader('X-Test');
    $this->assertEquals(1, count(Chrome::getHeaders()));
    Chrome::removeHeader('X-Test2');
    $this->assertEquals(0, count(Chrome::getHeaders()));
  }

  /**
   * @covers  Chrome::clearHeaders()
   * @covers  Chrome::getHeaders()
   * @covers  Chrome::addHeader()
   * @covers  Chrome::removeHeader()
   */
  public function testHTTPHeader3() {
    Chrome::clearHeaders();
    $this->assertEquals(0, count(Chrome::getHeaders()));
    Chrome::addHeader('X-Test', '1, 2, 3.');
    $h = Chrome::getHeaders();
    $this->assertEquals(1, count($h));
    $this->assertEquals(1, count($h['X-Test']));
    Chrome::addHeader('X-Test', '4, 5, 6.', false);
    $h = Chrome::getHeaders();
    $this->assertEquals(1, count($h));
    $this->assertEquals(2, count($h['X-Test']));
    Chrome::removeHeader('X-Test', '1, 2, 3.');
    $this->assertEquals(1, count(Chrome::getHeaders()));
  }

  # }}} HTTP header tests
  ############################################################################

  ############################################################################
  # Metadata tests {{{

  ############################################################################
  # Named metadata tests {{{

  /**
   * @covers  Chrome::clearMetadata()
   * @covers  Chrome::getMetadata()
   * @covers  Chrome::addMetadata()
   * @covers  Chrome::removeMetadata()
   */
  public function testMetaName1() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata()));
    Chrome::addMetadata('generator', 'Jerity');
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    $this->assertEquals(1, count(Chrome::getMetadata()));
    Chrome::removeMetadata('generator');
    $this->assertEquals(0, count(Chrome::getMetadata()));
  }

  /**
   * @covers  Chrome::clearMetadata()
   * @covers  Chrome::getMetadata()
   * @covers  Chrome::addMetadata()
   * @covers  Chrome::removeMetadata()
   */
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

  /**
   * @covers  Chrome::getMetadata()
   * @covers  Chrome::addMetadata()
   * @covers  Chrome::clearMetadata()
   */
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

  /**
   * @covers  Chrome::clearMetadata()
   * @covers  Chrome::getMetadata()
   * @covers  Chrome::addMetadata()
   * @covers  Chrome::removeMetadata()
   */
  public function testMetaHttp1() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    Chrome::addMetadata('refresh', '60', true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));
    Chrome::removeMetadata('refresh', true);
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
  }

  /**
   * @covers  Chrome::clearMetadata()
   * @covers  Chrome::getMetadata()
   * @covers  Chrome::addMetadata()
   * @covers  Chrome::removeMetadata()
   */
  public function testMetaHttp2() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    Chrome::addMetadata('refresh', '60', true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));
    Chrome::removeMetadata('refresh', true);
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
  }

  /**
   * @covers  Chrome::clearMetadata()
   * @covers  Chrome::getMetadata()
   * @covers  Chrome::addMetadata()
   */
  public function testMetaHttp3() {
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
    Chrome::addMetadata('refresh', '60', true);
    $this->assertEquals(0, count(Chrome::getMetadata()));
    $this->assertEquals(1, count(Chrome::getMetadata(true)));
    Chrome::clearMetadata();
    $this->assertEquals(0, count(Chrome::getMetadata(true)));
  }

  # }}} HTTP metadata tests
  ############################################################################

  ############################################################################
  # Mixed metadata tests {{{

  /**
   * @covers  Chrome::clearMetadata()
   * @covers  Chrome::getMetadata()
   * @covers  Chrome::addMetadata()
   */
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

  /**
   * @covers  Chrome::clearMetadata()
   * @covers  Chrome::addMetadata()
   * @covers  Chrome::getMetadata()
   */
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

  /**
   * @covers  Chrome::clearLinks()
   * @covers  Chrome::getLinks()
   * @covers  Chrome::addLink()
   */
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

  /**
   * @covers  Chrome::clearLinks()
   * @covers  Chrome::getLinks()
   * @covers  Chrome::addLink()
   */
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

  /**
   * @covers  Chrome::clearScripts()
   * @covers  Chrome::getScripts()
   * @covers  Chrome::addScript()
   * @covers  Chrome::removeScript()
   */
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

  /**
   * @covers  Chrome::clearScripts()
   * @covers  Chrome::getScripts()
   * @covers  Chrome::addScript()
   */
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

  /**
   * @covers  Chrome::clearScripts()
   * @covers  Chrome::getScripts()
   * @covers  Chrome::addScript()
   */
  function testScriptPriority1() {
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
    Chrome::addScript('/js/scriptaculous.js');
    Chrome::addScript('/js/prototype.js', 5);
    $this->assertEquals(2, count(Chrome::getScripts()));
    $s = Chrome::getScripts(null);
    $this->assertEquals($s[RenderContext::CONTENT_JS], Chrome::getScripts());
    $scriptArr = array(
      '/js/prototype.js' => array(
        'type' => RenderContext::CONTENT_JS,
        'src' => '/js/prototype.js'
      ),
      '/js/scriptaculous.js' => array(
        'type' => RenderContext::CONTENT_JS,
        'src' => '/js/scriptaculous.js'
      ),
    );
    $this->assertEquals($scriptArr, Chrome::getScripts());
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
  }

  /**
   * @covers  Chrome::clearScripts()
   * @covers  Chrome::getScripts()
   * @covers  Chrome::addScript()
   */
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
      '/js/prototype.js' => array(
        'type' => RenderContext::CONTENT_JS,
        'src' => '/js/prototype.js'
      ),
      '/js/misc.js' => array(
        'type' => RenderContext::CONTENT_JS,
        'src' => '/js/misc.js'
      ),
      '/js/scriptaculous.js' => array(
        'type' => RenderContext::CONTENT_JS,
        'src' => '/js/scriptaculous.js'
      ),
    );
    $this->assertEquals($scriptArr, Chrome::getScripts(), 'Expected array');
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
  }

  /**
   * @covers             Chrome::clearScripts()
   * @covers             Chrome::getScripts()
   * @covers             Chrome::addScript()
   * @expectedException  OutOfRangeException
   */
  function testScriptPriorityFail() {
    Chrome::clearScripts();
    $this->assertEquals(0, count(Chrome::getScripts()));
    Chrome::addScript('/js/scriptaculous.js', -5);
  }

  # }}} Script tests
  ############################################################################

  ############################################################################
  # Stylesheet tests {{{

  /**
   * @covers  Chrome::clearStylesheets()
   * @covers  Chrome::getStylesheets()
   * @covers  Chrome::addStylesheet()
   * @covers  Chrome::removeStylesheet()
   */
  function testStylesheet1() {
    Chrome::clearStylesheets();
    $this->assertEquals(0, count(Chrome::getStylesheets()));
    Chrome::addStylesheet('/css/common.css');
    $this->assertEquals(1, count(Chrome::getStylesheets()));
    Chrome::removeStylesheet('/css/common.css');
    $this->assertEquals(0, count(Chrome::getStylesheets()));
  }

  /**
   * @covers  Chrome::clearStylesheets()
   * @covers  Chrome::getStylesheets()
   * @covers  Chrome::addStylesheet()
   */
  function testDuplicateStylesheet() {
    Chrome::clearStylesheets();
    $this->assertEquals(0, count(Chrome::getStylesheets()));
    Chrome::addStylesheet('/css/foo.css');
    $this->assertEquals(1, count(Chrome::getStylesheets()));
    Chrome::addStylesheet('/css/foo.css');
    $this->assertEquals(1, count(Chrome::getStylesheets()));
    Chrome::addStylesheet('/css/foo.css', 10);
    $this->assertEquals(1, count(Chrome::getStylesheets()));
    Chrome::clearStylesheets();
    $this->assertEquals(0, count(Chrome::getStylesheets()));
  }

  /**
   * @covers  Chrome::clearStylesheets()
   * @covers  Chrome::getStylesheets()
   * @covers  Chrome::addStylesheet()
   */
  function testStylesheetPriority1() {
    Chrome::clearStylesheets();
    $this->assertEquals(0, count(Chrome::getStylesheets()));
    Chrome::addStylesheet('/css/common.css');
    Chrome::addStylesheet('/css/reset.css', 5);
    $this->assertEquals(2, count(Chrome::getStylesheets()));
    $stylesheetArr = array(
      array('rel'=>'stylesheet', 'type'=>RenderContext::CONTENT_CSS, 'href'=>'/css/reset.css'),
      array('rel'=>'stylesheet', 'type'=>RenderContext::CONTENT_CSS, 'href'=>'/css/common.css'),
    );
    $this->assertEquals($stylesheetArr, array_values(Chrome::getStylesheets()));
    Chrome::clearStylesheets();
    $this->assertEquals(0, count(Chrome::getStylesheets()));
  }

  /**
   * @covers  Chrome::clearStylesheets()
   * @covers  Chrome::getStylesheets()
   * @covers  Chrome::addStylesheet()
   */
  function testStylesheetPriority2() {
    Chrome::clearStylesheets();
    $this->assertEquals(0, count(Chrome::getStylesheets()));
    Chrome::addStylesheet('/css/common.css');
    Chrome::addStylesheet('/css/reset.css', 5);
    Chrome::addStylesheet('/css/misc.css', 15);
    $this->assertEquals(3, count(Chrome::getStylesheets()));
    $scriptArr = array(
      array('rel'=>'stylesheet', 'type'=>RenderContext::CONTENT_CSS, 'href'=>'/css/reset.css'),
      array('rel'=>'stylesheet', 'type'=>RenderContext::CONTENT_CSS, 'href'=>'/css/misc.css'),
      array('rel'=>'stylesheet', 'type'=>RenderContext::CONTENT_CSS, 'href'=>'/css/common.css'),
    );
    $this->assertEquals($scriptArr, array_values(Chrome::getStylesheets()));
    Chrome::clearStylesheets();
    $this->assertEquals(0, count(Chrome::getStylesheets()));
  }


  /**
   * @covers             Chrome::clearStylesheets()
   * @covers             Chrome::getStylesheets()
   * @covers             Chrome::addStylesheet()
   * @expectedException  OutOfRangeException
   */
  function testStylesheetPriorityFail() {
    Chrome::clearStylesheets();
    $this->assertEquals(0, count(Chrome::getStylesheets()));
    Chrome::addStylesheet('/css/common.css', -5);
  }

  /* TODO: Alternate stylesheet tests */

  # }}} Stylesheet tests
  ############################################################################

}

# vim: ts=2 sw=2 et foldmethod=marker
