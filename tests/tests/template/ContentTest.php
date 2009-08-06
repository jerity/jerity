<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class ContentTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Template::setPath(DATA_DIR.'templates');
  }

  public function testRender() {
    $c = new Content('simple');
    $c->set('content', '');
    $this->assertSame('', $c->render());
    $c->set('content', 'PASS');
    $this->assertSame('PASS', $c->render());
  }

}
