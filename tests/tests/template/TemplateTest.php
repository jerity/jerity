<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/jerity.php');

class TemplateT extends Template { }

class TemplateTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(dirname(dirname(__FILE__)).'/data/templates');
  }

  public function testValidTemplate1() {
    $t = new TemplateT('foo-succeed');
  }

  /**
   * @expectedException RuntimeException
   */
  public function testInvalidTemplate() {
    $t = new TemplateT('foo-fail');
  }

}
