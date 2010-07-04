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

  public function testIsLower() {
    $this->assertTrue(String::isLower('word'));
    $this->assertFalse(String::isLower('WORD'));
    $this->assertFalse(String::isLower('Word'));
    $this->assertFalse(String::isLower('WoRd'));
    $this->assertFalse(String::isLower('WOrd'));
  }

  public function testIsUpper() {
    $this->assertFalse(String::isUpper('word'));
    $this->assertTrue(String::isUpper('WORD'));
    $this->assertFalse(String::isUpper('Word'));
    $this->assertFalse(String::isUpper('WoRd'));
    $this->assertFalse(String::isUpper('WOrd'));
  }

  public function testIsTitleCase() {
    $this->assertFalse(String::isTitleCase('word'));
    $this->assertFalse(String::isTitleCase('WORD'));
    $this->assertTrue(String::isTitleCase('Word'));
    $this->assertFalse(String::isTitleCase('WoRd'));
    $this->assertFalse(String::isTitleCase('WOrd'));

  /**
   * @dataProvider  pluralizeProvider()
   */
  public function testPluralizeWithPrefix($count, $expected) {
    $prefix = 'You have';
    $this->assertSame("$prefix $count $expected", String::pluralize("$prefix $count new bunny!"));
  }

  public static function pluralizeProvider() {
    return array(
      array('',              'new bunnies!'),
      array('0',             'new bunnies!'),
      array('1',             'new bunny!'),
      array('2',             'new bunnies!'),
      array('0.1',           'new bunnies!'),
      array('0.5',           'new bunnies!'),
      array('1.0',           'new bunny!'),
      array('1.1',           'new bunnies!'),
      array('1.5',           'new bunnies!'),
      array('2.0',           'new bunnies!'),
      array('2.1',           'new bunnies!'),
      array('2.5',           'new bunnies!'),
      array('1500',          'new bunnies!'),
      array('1501',          'new bunnies!'),
      array('2500',          'new bunnies!'),
      array('2501',          'new bunnies!'),
      array('1,500',         'new bunnies!'),
      array('1,501',         'new bunnies!'),
      array('2,500',         'new bunnies!'),
      array('2,501',         'new bunnies!'),
      array('-1',            'new bunny!'),
      array('-2',            'new bunnies!'),
      array('-0.1',          'new bunnies!'),
      array('-0.5',          'new bunnies!'),
      array('-1.0',          'new bunny!'),
      array('-1.1',          'new bunnies!'),
      array('-1.5',          'new bunnies!'),
      array('-2.0',          'new bunnies!'),
      array('-2.1',          'new bunnies!'),
      array('-2.5',          'new bunnies!'),
      array('-1500',         'new bunnies!'),
      array('-1501',         'new bunnies!'),
      array('-2500',         'new bunnies!'),
      array('-2501',         'new bunnies!'),
      array('-1,500',        'new bunnies!'),
      array('-1,501',        'new bunnies!'),
      array('-2,500',        'new bunnies!'),
      array('-2,501',        'new bunnies!'),
      array('zero',          'new bunnies!'),
      array('one',           'new bunny!'),
      array('one point one', 'new bunnies!'),
      array('two',           'new bunnies!'),
      array('minus one',     'new bunny!'),
      array('minus two',     'new bunnies!'),
    );
  }

  /**
   * @dataProvider  formatBitsProvider()
   * @depends       NumberTest::parseBits()
   */
  public function testFormatBits($n, $si, $dp, $prefix, $symbol, $expected) {
    $this->assertSame($expected, String::formatBits($n, $si, $dp, $prefix, $symbol));
  }

  public static function formatBitsProvider() {
    return array(
      # Test automatic prefix.
      array('1',          true,  0, null, true, '8 b'),
      array('1000',       true,  0, null, true, '8 kb'),
      array('1000000',    true,  0, null, true, '8 Mb'),
      array('1000000000', true,  0, null, true, '8 Gb'),
      array('1',          false, 0, null, true, '8 b'),
      array('1024',       false, 0, null, true, '8 Kib'),
      array('1048576',    false, 0, null, true, '8 Mib'),
      array('1073741824', false, 0, null, true, '8 Gib'),
      # Test fixed prefix.
      array('1',          true,  0, 'K', true, '0 kb'),
      array('1000',       true,  0, 'K', true, '8 kb'),
      array('1000000',    true,  0, 'K', true, '8000 kb'),
      array('1000000000', true,  0, 'K', true, '8000000 kb'),
      array('1',          false, 0, 'K', true, '0 Kib'),
      array('1024',       false, 0, 'K', true, '8 Kib'),
      array('1048576',    false, 0, 'K', true, '8192 Kib'),
      array('1073741824', false, 0, 'K', true, '8388608 Kib'),
      # Test non-symbol prefix.
      array('1 b',          true,  0, null, false, '1 bit'),
      array('1000 b',       true,  0, null, false, '1 kilobit'),
      array('1000000 b',    true,  0, null, false, '1 megabit'),
      array('1000000000 b', true,  0, null, false, '1 gigabit'),
      array('1 b',          false, 0, null, false, '1 bit'),
      array('1024 b',       false, 0, null, false, '1 kibibit'),
      array('1048576 b',    false, 0, null, false, '1 mebibit'),
      array('1073741824 b', false, 0, null, false, '1 gibibit'),
      array('1',          true,  0, null, false, '8 bits'),
      array('1000',       true,  0, null, false, '8 kilobits'),
      array('1000000',    true,  0, null, false, '8 megabits'),
      array('1000000000', true,  0, null, false, '8 gigabits'),
      array('1',          false, 0, null, false, '8 bits'),
      array('1024',       false, 0, null, false, '8 kibibits'),
      array('1048576',    false, 0, null, false, '8 mebibits'),
      array('1073741824', false, 0, null, false, '8 gibibits'),
      array('2',          true,  0, null, false, '16 bits'),
      array('2000',       true,  0, null, false, '16 kilobits'),
      array('2000000',    true,  0, null, false, '16 megabits'),
      array('2000000000', true,  0, null, false, '16 gigabits'),
      array('2',          false, 0, null, false, '16 bits'),
      array('2048',       false, 0, null, false, '16 kibibits'),
      array('2097152',    false, 0, null, false, '16 mebibits'),
      array('2147483648', false, 0, null, false, '16 gibibits'),
      # Test decimal places.
      array('1',          false, 2, null, false, '8.00 bits'),
      array('1000',       false, 2, null, false, '7.81 kibibits'),
      array('1000000',    false, 2, null, false, '7.63 mebibits'),
      array('1000000000', false, 2, null, false, '7.45 gibibits'),
      array('1',          true,  2, null, false, '8.00 bits'),
      array('1024',       true,  2, null, false, '8.19 kilobits'),
      array('1048576',    true,  2, null, false, '8.39 megabits'),
      array('1073741824', true,  2, null, false, '8.59 gigabits'),
      # Miscellaneous tests.
      array('1.44 MB',    false, 2, null, true, '10.99 Mib'),
      array('1.37 MiB',   true,  2, null, true, '11.49 Mb'),
      array('250 GB',     false, 0, 'M',  true, '1907349 Mib'),
      array('250 GiB',    false, 0, 'M',  true, '2048000 Mib'),
      array('250 GB',     true,  0, 'M',  true, '2000000 Mb'),
      array('250 GiB',    true,  0, 'M',  true, '2147484 Mb'),
    );
  }

  /**
   * @dataProvider  formatBytesProvider()
   * @depends       NumberTest::parseBytes()
   */
  public function testFormatBytes($n, $si, $dp, $prefix, $symbol, $expected) {
    $this->assertSame($expected, String::formatBytes($n, $si, $dp, $prefix, $symbol));
  }

  public static function formatBytesProvider() {
    return array(
      # Test automatic prefix.
      array('1',          true,  0, null, true, '1 B'),
      array('1000',       true,  0, null, true, '1 kB'),
      array('1000000',    true,  0, null, true, '1 MB'),
      array('1000000000', true,  0, null, true, '1 GB'),
      array('1',          false, 0, null, true, '1 B'),
      array('1024',       false, 0, null, true, '1 KiB'),
      array('1048576',    false, 0, null, true, '1 MiB'),
      array('1073741824', false, 0, null, true, '1 GiB'),
      # Test fixed prefix.
      array('1',          true,  0, 'K', true, '0 kB'),
      array('1000',       true,  0, 'K', true, '1 kB'),
      array('1000000',    true,  0, 'K', true, '1000 kB'),
      array('1000000000', true,  0, 'K', true, '1000000 kB'),
      array('1',          false, 0, 'K', true, '0 KiB'),
      array('1024',       false, 0, 'K', true, '1 KiB'),
      array('1048576',    false, 0, 'K', true, '1024 KiB'),
      array('1073741824', false, 0, 'K', true, '1048576 KiB'),
      # Test non-symbol prefix.
      array('1',          true,  0, null, false, '1 byte'),
      array('1000',       true,  0, null, false, '1 kilobyte'),
      array('1000000',    true,  0, null, false, '1 megabyte'),
      array('1000000000', true,  0, null, false, '1 gigabyte'),
      array('1',          false, 0, null, false, '1 byte'),
      array('1024',       false, 0, null, false, '1 kibibyte'),
      array('1048576',    false, 0, null, false, '1 mebibyte'),
      array('1073741824', false, 0, null, false, '1 gibibyte'),
      array('2',          true,  0, null, false, '2 bytes'),
      array('2000',       true,  0, null, false, '2 kilobytes'),
      array('2000000',    true,  0, null, false, '2 megabytes'),
      array('2000000000', true,  0, null, false, '2 gigabytes'),
      array('2',          false, 0, null, false, '2 bytes'),
      array('2048',       false, 0, null, false, '2 kibibytes'),
      array('2097152',    false, 0, null, false, '2 mebibytes'),
      array('2147483648', false, 0, null, false, '2 gibibytes'),
      # Test decimal places.
      array('1',          false, 2, null, false, '1.00 byte'),
      array('1000',       false, 2, null, false, '1000.00 bytes'),
      array('1000000',    false, 2, null, false, '976.56 kibibytes'),
      array('1000000000', false, 2, null, false, '953.67 mebibytes'),
      array('1',          true,  2, null, false, '1.00 byte'),
      array('1024',       true,  2, null, false, '1.02 kilobytes'),
      array('1048576',    true,  2, null, false, '1.05 megabytes'),
      array('1073741824', true,  2, null, false, '1.07 gigabytes'),
      # Miscellaneous tests.
      array('1.44 MB',    false, 2, null, true, '1.37 MiB'),
      array('1.37 MiB',   true,  2, null, true, '1.44 MB'),
      array('250 GB',     false, 0, 'M',  true, '238419 MiB'),
      array('250 GiB',    false, 0, 'M',  true, '256000 MiB'),
      array('250 GB',     true,  0, 'M',  true, '250000 MB'),
      array('250 GiB',    true,  0, 'M',  true, '268435 MB'),
    );
  }

}

# vim: encoding=utf-8 fileencoding=utf-8
