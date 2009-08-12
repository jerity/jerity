<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');
require_once('TemplateTest.php');
require_once('ChromeTest.php');
require_once('ContentTest.php');
require_once('ChromeTestHTML401.php');

class Jerity_Template_AllTests {
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->setName('jerity-template');

    $suite->addTestSuite('TemplateTest');
    $suite->addTestSuite('ChromeTest');
    $suite->addTestSuite('ContentTest');
    $suite->addTestSuite('ChromeTestHTML401');

    return $suite;
  }
}
