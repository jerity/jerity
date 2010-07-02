<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################


class InflectorTest extends PHPUnit_Framework_TestCase {

  public static function getWordPairs() {
    static $pairs = array();
    if (count($pairs)) {
      return $pairs;
    }

    $fp = fopen(dirname(__FILE__).'/singular-plural-wordlist.txt', 'r');
    while (!feof($fp) && $data = fscanf($fp, '%s %s %d')) {
      $pairs[] = $data;
    }
    fclose($fp);

    return $pairs;
  }

  public static function wordPairProvider() {
    return self::getWordPairs();
  }

  public static function singularWordProvider() {
    return array_map(create_function('$a', 'return array($a[0], $a[2]);'), self::getWordPairs());
  }

  public static function pluralWordProvider() {
    return array_map(create_function('$a', 'return array($a[1], $a[2]);'), self::getWordPairs());
  }

  /**
   * @covers  Inflector::singularize()
   *
   * @dataProvider  wordPairProvider()
   */
  public function testSingularize($singular, $plural, $fix_case) {
    $our_singular = Inflector::singularize($plural, $fix_case);
    $this->assertSame($singular, $our_singular, 'Singular form of "'.$plural.'" differs');
  }

  /**
   * @covers  Inflector::pluralize()
   *
   * @dataProvider  wordPairProvider()
   */
  public function testPluralize($singular, $plural, $fix_case) {
    $our_plural = Inflector::pluralize($singular, $fix_case);
    $this->assertSame($plural, $our_plural, 'Plural form of "'.$singular.'" differs');
  }

  /**
   * @covers  Inflector::singularize()
   * @covers  Inflector::pluralize()
   *
   * @dataProvider  pluralWordProvider()
   */
  public function testSingularReverses($plural, $fix_case) {
    $replural = Inflector::pluralize(Inflector::singularize($plural, $fix_case), $fix_case);
    $this->assertSame($plural, $replural, 'Plural and replural form of "'.$plural.'" differ');
  }

  /**
   * @covers  Inflector::singularize()
   * @covers  Inflector::pluralize()
   *
   * @dataProvider  singularWordProvider()
   */
  public function testPluralReverses($singular, $fix_case) {
    $resingular = Inflector::singularize(Inflector::pluralize($singular, $fix_case), $fix_case);
    $this->assertSame($singular, $resingular, 'Singular and resingular form of "'.$singular.'" differ');
  }

}
