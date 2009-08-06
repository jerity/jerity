<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class ContentTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(DATA_DIR.'templates');
  }
}
