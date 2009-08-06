<?php
require_once(dirname(__FILE__).'/setUp.php');
require_once('tests/AllTests.php');

class AllTests {
  public static function suite() {
    PHPUnit_Util_Filter::addDirectoryToFilter(dirname(__FILE__));
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTestSuite(Jerity_AllTests::suite());
    return $suite;
  }
}
