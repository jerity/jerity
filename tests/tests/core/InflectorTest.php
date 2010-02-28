<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class InflectorTest extends PHPUnit_Framework_TestCase {

  public static function getWordPairs() {
    $pairs = array();

    $fp = fopen(dirname(__FILE__).'/singular-plural-wordlist.txt', 'r');
    while (!feof($fp) && $data = fscanf($fp, '%s %s')) {
      $pairs[] = $data;
    }
    fclose($fp);

    return $pairs;
  }

  public static function wordPairProvider() {
    return self::getWordPairs();
  }

  public static function singularWordProvider() {
    return array_map(create_function('$a', 'return array($a[0]);'), self::getWordPairs());
  }

  public static function pluralWordProvider() {
    return array_map(create_function('$a', 'return array($a[1]);'), self::getWordPairs());
  }

  /**
   * @covers  Inflector::singularize()
   *
   * @dataProvider  wordPairProvider()
   */
  public function testSingularize($singular, $plural) {
    $our_singular = Inflector::singularize($plural);
    $this->assertSame($singular, $our_singular, 'Singular form of "'.$plural.'" differs');
  }

  /**
   * @covers  Inflector::pluralize()
   *
   * @dataProvider  wordPairProvider()
   */
  public function testPluralize($singular, $plural) {
    $our_plural = Inflector::pluralize($singular);
    $this->assertSame($plural, $our_plural, 'Plural form of "'.$singular.'" differs');
  }

  /**
   * @covers  Inflector::singularize()
   * @covers  Inflector::pluralize()
   *
   * @dataProvider  pluralWordProvider()
   */
  public function testSingularReverses($plural) {
    $replural = Inflector::pluralize(Inflector::singularize($plural));
    $this->assertSame($plural, $replural, 'Plural and replural form of "'.$plural.'" differ');
  }

  /**
   * @covers  Inflector::singularize()
   * @covers  Inflector::pluralize()
   *
   * @dataProvider  singularWordProvider()
   */
  public function testPluralReverses($singular) {
    $resingular = Inflector::singularize(Inflector::pluralize($singular));
    $this->assertSame($singular, $resingular, 'Singular and resingular form of "'.$singular.'" differ');
  }

}
