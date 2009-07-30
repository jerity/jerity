<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/jerity.php');

class ChromeTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(dirname(dirname(__FILE__)).'/data/templates');
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
