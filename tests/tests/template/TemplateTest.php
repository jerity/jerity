<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/jerity.php');

if (!class_exists('TemplateT')) {
  class TemplateT extends Template { }
}

class TemplateTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(dirname(dirname(__FILE__)).'/data/templates');
  }

  ############################################################################
  # Template validity tests {{{

  public function testValidTemplate1() {
    $t = new TemplateT('foo-succeed');
  }

  /**
   * @expectedException RuntimeException
   */
  public function testInvalidTemplate() {
    $t = new TemplateT('foo-fail');
  }

  # }}} Template validity tests
  ############################################################################

  ############################################################################
  # Jailbreak tests {{{

  /**
   * @dataProvider       jailbreakProvider
   * @expectedException  InvalidArgumentException
   */
  public function testJailbreak($path) {
    $t = new TemplateT($path);
  }

  public static function jailbreakProvider() {
    return array(
      array('../foo-fail'),
      array('./../foo-fail'),
      array('./../foo-fail'),
      array('../../foo-fail'),
      array('./../../foo-fail'),
      array('no-dir/../foo-fail'),
      array('./no-dir/../foo-fail'),
      array('no-dir/../../foo-fail'),
      array('./no-dir/../../foo-fail'),
      array('chrome/../foo-fail'),
      array('./chrome/../foo-fail'),
      array('chrome/../../foo-fail'),
      array('./chrome/../../foo-fail'),
      array('/foo-fail'),
      array('/abc/foo-fail'),
      array('/abc/../foo-fail'),
      array('/abc/../../foo-fail'),
    );
  }

  # }}} Jailbreak tests
  ############################################################################

  ############################################################################
  # Template variable tests {{{

  public function testSingleGetSet() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
  }

  public function testMultipleGetSet1() {
    $t = new TemplateT('chrome/simple');
    $t->set(array('foo' => 'bar'));
    $this->assertSame('bar', $t->get('foo'));
  }

  public function testMultipleGetSet2() {
    $t = new TemplateT('chrome/simple');
    $t->set(array('foo'), array('bar'));
    $this->assertSame('bar', $t->get('foo'));
  }

  public function testMultipleGetSet3() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  public function testMultipleGetSet4() {
    $t = new TemplateT('chrome/simple');
    $t->set(array('foo' => 'bar', 'baz' => 'qux'));
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  public function testMultipleGetSet5() {
    $t = new TemplateT('chrome/simple');
    $t->set(array('foo', 'baz'), array('bar', 'qux'));
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  public function testGetSetSingleClearSingle() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
    $t->clear('foo');
    $this->assertSame(null, $t->get('foo'));
  }

  public function testGetSetSingleClearAll() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
    $t->clear();
    $this->assertSame(null, $t->get('foo'));
  }

  public function testGetSetMultipleClearIndividual() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
    $t->clear('foo');
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
    $t->clear('baz');
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame(null, $t->get('baz'));
  }

  public function testGetSetMultipleClearAll() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
    $t->clear();
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame(null, $t->get('baz'));
  }

  public function testComplexSingleGetSet1() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', array('bar'));
    $t->set('bar', array('baz', 'qux'));
    $this->assertSame(array('bar'), $t->get('foo'));
    $this->assertSame(array('baz', 'qux'), $t->get('bar'));
  }

  public function testComplexSingleGetSet2() {
    $t = new TemplateT('chrome/simple');
    $obj = new StdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $this->assertEquals($obj, $t->get('test'));
    $this->assertSame($obj, $t->get('test'));
  }

  public function testComplexSingleGetSet3() {
    $t = new TemplateT('chrome/simple');
    $obj = new StdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $obj->abc = 'def';
    $this->assertEquals($obj, $t->get('test'));
    $this->assertSame($obj, $t->get('test'));
  }

  public function testComplexSingleGetSet4() {
    $t = new TemplateT('chrome/simple');
    $obj = new StdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $obj = clone $obj;
    $obj->abc = 'def';
    $this->assertNotSame($obj, $t->get('test'));
    $this->assertNotEquals($obj, $t->get('test'));
  }

  # }}} Template variable tests
  ############################################################################
}

# vim: ts=2 sw=2 et foldmethod=marker
