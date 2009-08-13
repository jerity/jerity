<?php
require_once(dirname(dirname(__FILE__)).'/setUp.php');
require_once(dirname(__FILE__).'/core/AllTests.php');
require_once(dirname(__FILE__).'/template/AllTests.php');

class Jerity_AllTests {
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite();

    $suite->addTestSuite(Jerity_Core_AllTests::suite());
    $suite->addTestSuite(Jerity_Template_AllTests::suite());

    return $suite;
  }
}
?>
