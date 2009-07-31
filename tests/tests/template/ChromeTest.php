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
}
