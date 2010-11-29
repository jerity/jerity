<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

use \Jerity\Util\Inflector;

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 *
 * @group  inflector
 * @group  utility
 */
class InflectorTest extends PHPUnit_Framework_TestCase {

  /**
   * Read in the word test data.
   *
   * @return  array  The normal word test data.
   */
  protected static function getInflectorWordData() {
    static $data = array();
    if (count($data)) return $data;
    $fp = fopen(__DIR__.'/../_data/inflector-word.dat', 'r');
    while (!feof($fp) && $line = fscanf($fp, '%s %s')) {
      # Skip commented lines:
      if ($line[0][0] == '#') continue;
      # Split multiple singular/plural forms:
      $line[0] = explode('|', $line[0]);
      $line[1] = explode('|', $line[1]);
      $data[] = $line;
    }
    fclose($fp);
    return $data;
  }

  /**
   * The normal word test data.
   *
   * @return  array  The normal word test data.
   */
  public static function inflectorWordProvider() {
    return self::getInflectorWordData();
  }

#  /**
#   *
#   */
#  public function fixLetterCaseProvider() {
#    return array(
#      array('WORD',     'WORDS',    true),
#      array('word',     'words',    true),
#      array('Word',     'Words',    true),
#      array('WoRd',     'WoRds',    true),
#      array('VORTEX',   'VORTICES', true),
#      array('vortex',   'vortices', true),
#      array('Vortex',   'Vortices', true),
#      array('VoRtEx',   'VoRtices', true),
#      array('WORDS',    'WORD',     false),
#      array('words',    'word',     false),
#      array('Words',    'Word',     false),
#      array('WoRds',    'WoRd',     false),
#      array('VORTICES', 'VORTEX',   false),
#      array('vortices', 'vortex',   false),
#      array('Vortices', 'Vortex',   false),
#      array('VoRtIcEs', 'VoRtex',   false),
#    );
#  }

  /**
   * Test whether the inflector produces correct singular forms.
   *
   * We check all plural forms yield the correct singular.
   * Only the first (most common) singular form is acceptable.
   *
   * @param  array  $singulars  The singular form(s) of the word.
   * @param  array  $plurals    The plural form(s) of the word.
   *
   * @dataProvider  inflectorWordProvider()
   */
  public function testSingularize($singulars, $plurals) {
    foreach ($plurals as $plural) {
      $output = Inflector::singularize($plural);
      $this->assertSame($singulars[0], $output, "Unexpected singular form of '{$plural}'.");
    }
  }

  /**
   * Test whether the inflector produces correct plural forms.
   *
   * We check all singular forms yield the correct plural.
   * Only the first (most common) plural form is acceptable.
   *
   * @param  array  $singulars  The singular form(s) of the word.
   * @param  array  $plurals    The plural form(s) of the word.
   *
   * @dataProvider  inflectorWordProvider()
   */
  public function testPluralize($singulars, $plurals) {
    foreach ($singulars as $singular) {
      $output = Inflector::pluralize($singular);
      $this->assertSame($plurals[0], $output, "Unexpected plural form of '{$singular}'.");
    }
  }

#  /**
#   * @dataProvider  fixLetterCaseProvider()
#   * @depends       testPluralize()
#   * @depends       testSingularize()
#   */
#  public function testFixLetterCase($a, $b, $pluralise) {
#    # We disable simplification for this test to ensure that we do not split
#    # as camel case when testing with silly capitalisation.
#    if ($pluralise) {
#      $this->assertSame($b, Inflector::pluralize($a, false));
#    } else {
#      $this->assertSame($b, Inflector::singularize($a, false));
#    }
#  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
