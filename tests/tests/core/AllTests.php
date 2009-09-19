<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');
require_once('ArrayUtilTest.php');
require_once('NumberTest.php');
require_once('RenderContextTest.php');
require_once('TagTestHTML401.php');
require_once('TagTestXHTML10.php');

class Jerity_Core_AllTests {
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->setName('jerity-core');

    $suite->addTestSuite('ArrayUtilTest');
    $suite->addTestSuite('NumberTest');
    $suite->addTestSuite('RenderContextTest');
    $suite->addTestSuite('TagTestHTML401');
    $suite->addTestSuite('TagTestXHTML10');

    return $suite;
  }
}
