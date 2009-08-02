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

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak1() {
    $t = new TemplateT('../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak2() {
    $t = new TemplateT('./../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak3() {
    $t = new TemplateT('../../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak4() {
    $t = new TemplateT('./../../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak5() {
    $t = new TemplateT('no-dir/../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak6() {
    $t = new TemplateT('./no-dir/../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak7() {
    $t = new TemplateT('no-dir/../../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak8() {
    $t = new TemplateT('./no-dir/../../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak9() {
    $t = new TemplateT('chrome/../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak10() {
    $t = new TemplateT('./chrome/../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak11() {
    $t = new TemplateT('chrome/../../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak12() {
    $t = new TemplateT('./chrome/../../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak13() {
    $t = new TemplateT('/foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak14() {
    $t = new TemplateT('/abc/foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak15() {
    $t = new TemplateT('/abc/../foo-fail');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testJailbreak16() {
    $t = new TemplateT('/abc/../../foo-fail');
  }

}
