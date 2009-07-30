<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/jerity.php');

class ChromeTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(dirname(dirname(__FILE__)).'/data/templates');
  }
}
