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
   * @dataProvider  wordPairProvider()
   */
  public function testSingularize($singular, $plural, $fix_case) {
    $our_singular = Inflector::singularize($plural, true, $fix_case);
    $this->assertSame($singular, $our_singular, 'Singular form of "'.$plural.'" differs');
  }

  /**
   * @dataProvider  wordPairProvider()
   */
  public function testPluralize($singular, $plural, $fix_case) {
    $our_plural = Inflector::pluralize($singular, true, $fix_case);
    $this->assertSame($plural, $our_plural, 'Plural form of "'.$singular.'" differs');
  }

  /**
   * @dataProvider  pluralWordProvider()
   * @depends       testPluralize()
   * @depends       testSingularize()
   */
  public function testSingularReverses($plural, $fix_case) {
    $replural = Inflector::pluralize(Inflector::singularize($plural, true, $fix_case), true, $fix_case);
    $this->assertSame($plural, $replural, 'Plural and replural form of "'.$plural.'" differ');
  }

  /**
   * @dataProvider  singularWordProvider()
   * @depends       testPluralize()
   * @depends       testSingularize()
   */
  public function testPluralReverses($singular, $fix_case) {
    $resingular = Inflector::singularize(Inflector::pluralize($singular, true, $fix_case), true, $fix_case);
    $this->assertSame($singular, $resingular, 'Singular and resingular form of "'.$singular.'" differ');
  }

  /**
   * @dataProvider  fixLetterCaseProvider()
   * @depends       testPluralize()
   * @depends       testSingularize()
   */
  public function testFixLetterCase($a, $b, $pluralise) {
    # We disable simplification for this test to ensure that we do not split
    # as camel case when testing with silly capitalisation.
    if ($pluralise) {
      $this->assertSame($b, Inflector::pluralize($a, false));
    } else {
      $this->assertSame($b, Inflector::singularize($a, false));
    }
  }

  public function fixLetterCaseProvider() {
    return array(
      array('WORD', 'WORDS', true),
      array('word', 'words', true),
      array('Word', 'Words', true),
      array('WoRd', 'WoRds', true),
      array('VORTEX', 'VORTICES', true),
      array('vortex', 'vortices', true),
      array('Vortex', 'Vortices', true),
      array('VoRtEx', 'VoRtices', true),
      array('WORDS', 'WORD', false),
      array('words', 'word', false),
      array('Words', 'Word', false),
      array('WoRds', 'WoRd', false),
      array('VORTICES', 'VORTEX', false),
      array('vortices', 'vortex', false),
      array('Vortices', 'Vortex', false),
      array('VoRtIcEs', 'VoRtex', false),
    );
  }

}
