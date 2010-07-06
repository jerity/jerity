<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################


class StringTest extends PHPUnit_Framework_TestCase {

  /**
   *
   * @dataProvider  escapeHtmlProvider()
   */
  public function testEscapeHtml($text, $full_encode, $double_encode, $expected) {
    $this->assertSame($expected, String::escapeHTML($text, $full_encode, $double_encode), 'Escaping using function');
    if (!$double_encode) {
      // we can't force double-encoding off for the generic escape() method
      // as it's usually the sensible behaviour.
      return;
    }
    $ctx = RenderContext::create(RenderContext::TYPE_HTML4_STRICT);
    RenderContext::push($ctx);
    $this->assertSame($expected, String::escape($text, null, $full_encode), 'Escaping using HTML RenderContext');
    $ctx = RenderContext::create(RenderContext::TYPE_XHTML1_STRICT);
    RenderContext::push($ctx);
    $this->assertSame($expected, String::escape($text, null, $full_encode), 'Escaping using XHTML RenderContext');
    RenderContext::pop();
    RenderContext::pop();
    $this->assertSame($expected, String::escape($text, RenderContext::CONTENT_HTML, $full_encode), 'Escaping using HTML content override');
    $this->assertSame($expected, String::escape($text, RenderContext::CONTENT_XHTML, $full_encode), 'Escaping using XHTML content override');
  }

  public static function escapeHtmlProvider() {
    return array(
      array('', false, true, ''),
      array('', true,  true, ''),
      array('foo bar test', false, true, 'foo bar test'),
      array('foo bar test', true,  true, 'foo bar test'),
      array('foo <b>ar test', false, true, 'foo &lt;b&gt;ar test'),
      array('foo <b>ar test', true,  true, 'foo &lt;b&gt;ar test'),
      array('foo b<>ar test', false, true, 'foo b&lt;&gt;ar test'),
      array('foo b<>ar test', true,  true, 'foo b&lt;&gt;ar test'),
      array('foo bar <tes>t', false, true, 'foo bar &lt;tes&gt;t'),
      array('foo bar <tes>t', true,  true, 'foo bar &lt;tes&gt;t'),
      array('\'"&<>', false, true, '&#039;&quot;&amp;&lt;&gt;'),
      array('\'"&<>', true,  true, '&#039;&quot;&amp;&lt;&gt;'),
      array('&amp;', false, true,  '&amp;amp;'),
      array('&amp;', true,  true,  '&amp;amp;'),
      array('&amp;', false, false, '&amp;'),
      array('&amp;', true,  false, '&amp;'),
      array('&amp;&quot;', false, false, '&amp;&quot;'),
      array('&amp;&quot;', true,  false, '&amp;&quot;'),
      array('£', false, false, '£'),
      array('£', true,  false, '&pound;'),
    );
  }

  /**
   *
   * @dataProvider  escapeXmlProvider()
   */
  public function testEscapeXml($text, $full_encode, $double_encode, $expected) {
    $this->assertSame($expected, String::escapeXML($text, $full_encode, $double_encode), 'Escaping using function');
    if (!$double_encode) {
      // we can't force double-encoding off for the generic escape() method
      // as it's usually the sensible behaviour.
      return;
    }
    $ctx = new RenderContext();
    $ctx->setLanguage(RenderContext::LANG_XML);
    RenderContext::push($ctx);
    $this->assertSame($expected, String::escape($text, null, $full_encode), 'Escaping using RenderContext');
    RenderContext::pop();
    $this->assertSame($expected, String::escape($text, RenderContext::CONTENT_XML, $full_encode), 'Escaping using content override');
  }

  public static function escapeXmlProvider() {
    return array(
      array('', false, true, ''),
      array('', true,  true, ''),
      array('foo bar test', false, true, 'foo bar test'),
      array('foo bar test', true,  true, 'foo bar test'),
      array('foo <b>ar test', false, true, 'foo &lt;b&gt;ar test'),
      array('foo <b>ar test', true,  true, 'foo &lt;b&gt;ar test'),
      array('foo b<>ar test', false, true, 'foo b&lt;&gt;ar test'),
      array('foo b<>ar test', true,  true, 'foo b&lt;&gt;ar test'),
      array('foo bar <tes>t', false, true, 'foo bar &lt;tes&gt;t'),
      array('foo bar <tes>t', true,  true, 'foo bar &lt;tes&gt;t'),
      array('\'"&<>', false, true, '&#039;&quot;&amp;&lt;&gt;'),
      array('\'"&<>', true,  true, '&#039;&quot;&amp;&lt;&gt;'),
      array('&amp;', false, true,  '&amp;amp;'),
      array('&amp;', true,  true,  '&amp;amp;'),
      array('&amp;', false, false, '&amp;'),
      array('&amp;', true,  false, '&amp;'),
      array('&amp;&quot;', false, false, '&amp;&quot;'),
      array('&amp;&quot;', true,  false, '&amp;&quot;'),
      array('£', false, false, '£'),
      #array('£', true,  false, '&#163;'), // when TODO for numeric entities is fixed
    );
  }

  /**
   *
   * @dataProvider  escapeJsProvider()
   */
  public function testEscapeJs($text, $double_quote, $expected) {
    $this->assertSame($expected, String::escapeJS($text, $double_quote), 'Escaping using function');
    if (!$double_quote) {
      // we can't force double-quote encoding off for the generic escape()
      // method as it's a sensible default behaviour.
      return;
    }
    $ctx = new RenderContext();
    $ctx->setLanguage(RenderContext::LANG_JS);
    RenderContext::push($ctx);
    $this->assertSame($expected, String::escape($text), 'Escaping using RenderContext');
    RenderContext::pop();
    $this->assertSame($expected, String::escape($text, RenderContext::CONTENT_JS), 'Escaping using content override');
  }

  public static function escapeJsProvider() {
    return array(
      array('', true,  ''),
      array('', false, ''),
      array('foo bar test', true,  'foo bar test'),
      array('foo bar test', false, 'foo bar test'),
      array('foo "bar" test', true,  'foo \"bar\" test'),
      array('foo "bar" test', false, 'foo "bar" test'),
      array("'\"\\'\r\n", true,  '\\\'\"\\\\\\\'\r\n'), # '"\'^M^J  -->  \'\"\\\'\r\n
      array("'\"\\'\r\n", false, "\\'\"\\\\\\'\r\n"),   # '"\'^M^J  -->  \'"\\\'^M^J
      array('£', true,  '£'),
      array('£', false, '£'),
    );
  }

  /**
   */
  public function testEscapeDefault() {
    $text = '<foo\">\'&thing';
    $ctx = new RenderContext();
    $ctx->setLanguage(RenderContext::LANG_TEXT);
    RenderContext::push($ctx);
    $this->assertSame($text, String::escape($text), 'Escaping using RenderContext');
    RenderContext::pop();
    try {
      $tmp = String::escape($text, RenderContext::CONTENT_TEXT);
      $this->fail('Escape override failure did not fail.');
    } catch (InvalidArgumentException $e) {
    }
  }

  /**
   *
   * @dataProvider  conjunctionProvider()
   */
  public function testNaturalConjunction($list, $oxford_comma, $joiner, $expected) {
    $this->assertSame($expected, String::naturalConjunction($list, $oxford_comma, $joiner));
  }

  public static function conjunctionProvider() {
    return array(
      array(array(''),                            null,  null,  ''),
      array(array('one'),                         null,  null,  'one'),
      array(array('one', 'two'),                  null,  null,  'one and two'),
      array(array('one', 'two', 'three'),         null,  null,  'one, two, and three'),
      array(array('one', 'two', 'three', 'four'), null,  null,  'one, two, three, and four'),
      array(array('one'),                         true,  null,  'one'),
      array(array('one', 'two'),                  true,  null,  'one, and two'),
      array(array('one', 'two', 'three'),         true,  null,  'one, two, and three'),
      array(array('one', 'two', 'three', 'four'), true,  null,  'one, two, three, and four'),
      array(array('one'),                         false, null,  'one'),
      array(array('one', 'two'),                  false, null,  'one and two'),
      array(array('one', 'two', 'three'),         false, null,  'one, two and three'),
      array(array('one', 'two', 'three', 'four'), false, null,  'one, two, three and four'),
    );
  }

  /**
   *
   * @dataProvider  truncateProvider()
   */
  public function testTruncate($text, $length, $boundary, $ellipsis, $extension, $text_mode, $expected) {
    if ($text_mode) {
      $ctx = new RenderContext();
      $ctx->setLanguage(RenderContext::LANG_TEXT);
      RenderContext::push($ctx);
    } else {
      $this->assertSame(RenderContext::get()->getLanguage(), RenderContext::LANG_HTML, 'Wrong RenderContext!');
    }
    $this->assertSame($expected, String::truncate($text, $length, $boundary, $ellipsis, $extension));
    if ($text_mode) {
      RenderContext::pop();
    }
  }

  public static function truncateProvider() {
    return array(
      array('', 15, true,  true,  true,  true, ''),
      array('', 15, true,  true,  false, true, ''),
      array('', 15, true,  false, true,  true, ''),
      array('', 15, false, true,  true,  true, ''),
      array('asdf', 15, true, true, false, true, 'asdf'),
      array('qwertyuiopasdfghjklzxcvbnm', 15, true, false, false, true, 'qwertyuiopasdfg'),
      array('qwertyuiopasdfghjklzxcvbnm', 15, true, true, false, true, 'qwertyuiopas...'),
      array('qwertyuiopasdfghjklzxcvbnm', 15, true, true, false, false, 'qwertyuiopasdf&#8230;'),
      array('qwertyuiopasdfghjklzxcvbnm.foo', 15, true, false, false, true,  'qwertyuiopasdfg'),
      array('qwertyuiopasdfghjklzxcvbnm.foo', 15, true, true,  false, true,  'qwertyuiopas...'),
      array('qwertyuiopasdfghjklzxcvbnm.foo', 15, true, true,  false, false, 'qwertyuiopasdf&#8230;'),
      array('qwertyuiopasdfghjklzxcvbnm.foo', 15, true, false, true,  true,  'qwertyui....foo'),
      array('qwertyuiopasdfghjklzxcvbnm.foo', 15, true, true,  true,  true,  'qwertyui....foo'),
      array('qwertyuiopasdfghjklzxcvbnm.foo', 15, true, true,  true,  false, 'qwertyuiop&#8230;.foo'),
    );
  }

  /**
   *
   * @dataProvider  splitCamelCaseProvider()
   */
  public function testSplitCamelCase($input, $expected) {
    $this->assertSame($expected, String::splitCamelCase($input));
  }

  public static function splitCamelCaseProvider() {
    return array(
      array('myCamelString', array('my', 'Camel', 'String')),
      array('MyCamelString', array('My', 'Camel', 'String')),
      array('fooBar', array('foo', 'Bar')),
      array('FooBar', array('Foo', 'Bar')),
      array('foo', array('foo')),
      array('Foo', array('Foo')),
      array('HTML', array('H', 'T', 'M', 'L')),
      array('hTML', array('h', 'T', 'M', 'L')),
      array('HTmL', array('H', 'Tm', 'L')),
      array('hTmL', array('h', 'Tm', 'L')),
      array('', array('')),
    );
  }

  /**
   *
   * @dataProvider  splitSplitCaseProvider()
   */
  public function testSplitSplitCase($input, $expected) {
    $this->assertSame($expected, String::splitSplitCase($input));
  }

  public static function splitsplitCaseProvider() {
    return array(
      array('A_SPLIT_CASE_STRING', array('A', 'SPLIT', 'CASE', 'STRING')),
      array('a_split_case_string', array('a', 'split', 'case', 'string')),
      array('foo', array('foo')),
      array('_foo', array('foo')),
      array('foo_', array('foo')),
      array('_foo_', array('foo')),
      array('foo_Bar', array('foo', 'Bar')),
      array('foo__Bar', array('foo', 'Bar')),
      array('_foo__Bar', array('foo', 'Bar')),
      array('_foo__Bar_', array('foo', 'Bar')),
      array('foo__Bar_', array('foo', 'Bar')),
      array('_', array('')),
      array('', array('')),
    );
  }

}

# vim: encoding=utf-8 fileencoding=utf-8
