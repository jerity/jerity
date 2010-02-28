<?php
/**
 * @package    JerityCore
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */

/**
 * Pluralisation/singularisation rules for English words.
 *
 * Includes significant amounts of (modified) code from the CakePHP 1.2
 * Inflector, which is copyright 2005-2008, Cake Software Foundation, Inc.
 * (http://www.cakefoundation.org).
 *
 * @package    JerityCore
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 * @copyright  Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 */
class Inflector {
  /**
   * Pluralized words cache.
   *
   * @var array
   **/
  protected static $pluralized = array();

  /**
   * List of pluralization rules in the form of pattern => replacement.
   *
   * @var array
   **/
  protected static $pluralRules = array(
    'pluralRules' => array(
      '/(s)tatus$/i' => '\1\2tatuses',
      '/(quiz)$/i' => '\1zes',
      '/^(ox)$/i' => '\1\2en',
      '/([m|l])ouse$/i' => '\1ice',
      '/(matr|vert|ind)(ix|ex)$/i'  => '\1ices',
      '/(x|ch|ss|sh)$/i' => '\1es',
      '/([^aeiouy]|qu)y$/i' => '\1ies',
      '/(hive)$/i' => '\1s',
      '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
      '/sis$/i' => 'ses',
      '/([ti])um$/i' => '\1a',
      '/(p)erson$/i' => '\1eople',
      '/(m)an$/i' => '\1en',
      '/(c)hild$/i' => '\1hildren',
      '/(buffal|tomat)o$/i' => '\1\2oes',
      '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
      '/us$/' => 'uses',
      '/(alias)$/i' => '\1es',
      '/(ax|cris|test)is$/i' => '\1es',
      '/s$/' => 's',
      '/^$/' => '',
      '/$/' => 's'),
    'uninflected' => array(
      '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'Amoyese',
      'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
      'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
      'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
      'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
      'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
      'nexus', 'Niasese', 'Pekingese', 'People', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
      'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
      'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
      'whiting', 'wildebeest', 'Yengeese'),
    'irregular' => array(
      'atlas' => 'atlases',
      'beef' => 'beefs',
      'brother' => 'brothers',
      'child' => 'children',
      'corpus' => 'corpuses',
      'cow' => 'cows',
      'ganglion' => 'ganglions',
      'genie' => 'genies',
      'genus' => 'genera',
      'graffito' => 'graffiti',
      'hoof' => 'hoofs',
      'loaf' => 'loaves',
      'man' => 'men',
      'money' => 'monies',
      'mongoose' => 'mongooses',
      'move' => 'moves',
      'mythos' => 'mythoi',
      'numen' => 'numina',
      'occiput' => 'occiputs',
      'octopus' => 'octopuses',
      'opus' => 'opuses',
      'ox' => 'oxen',
      'penis' => 'penises',
      'person' => 'people',
      'sex' => 'sexes',
      'soliloquy' => 'soliloquies',
      'testis' => 'testes',
      'trilby' => 'trilbys',
      'turf' => 'turfs'),
  );

  /**
   * Singularized words cache.
   *
   * @var array
   **/
  protected static $singularized = array();

  /**
   * List of singularization rules in the form of pattern => replacement.
   *
   * @var array
   **/
  protected static $singularRules = array(
    'singularRules' => array(
      '/(s)tatuses$/i' => '\1\2tatus',
      '/^(.*)(menu)s$/i' => '\1\2',
      '/(quiz)zes$/i' => '\\1',
      '/(matr)ices$/i' => '\1ix',
      '/(vert|ind)ices$/i' => '\1ex',
      '/^(ox)en/i' => '\1',
      '/(alias)(es)*$/i' => '\1',
      '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
      '/([ftw]ax)es/' => '\1',
      '/(cris|ax|test)es$/i' => '\1is',
      '/(shoe)s$/i' => '\1',
      '/(o)es$/i' => '\1',
      '/ouses$/' => 'ouse',
      '/uses$/' => 'us',
      '/([m|l])ice$/i' => '\1ouse',
      '/(x|ch|ss|sh)es$/i' => '\1',
      '/(m)ovies$/i' => '\1\2ovie',
      '/(s)eries$/i' => '\1\2eries',
      '/([^aeiouy]|qu)ies$/i' => '\1y',
      '/([lr])ves$/i' => '\1f',
      '/(tive)s$/i' => '\1',
      '/(hive)s$/i' => '\1',
      '/(drive)s$/i' => '\1',
      '/([^fo])ves$/i' => '\1fe',
      '/(^analy)ses$/i' => '\1sis',
      '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
      '/([ti])a$/i' => '\1um',
      '/(p)eople$/i' => '\1\2erson',
      '/(m)en$/i' => '\1an',
      '/(c)hildren$/i' => '\1\2hild',
      '/(n)ews$/i' => '\1\2ews',
      '/^(.*us)$/' => '\\1',
      '/s$/i' => ''),
    'uninflected' => array(
      '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss', 'Amoyese',
      'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
      'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
      'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
      'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
      'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
      'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
      'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
      'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
      'whiting', 'wildebeest', 'Yengeese'
    ),
    'irregular' => array(
      'atlases' => 'atlas',
      'beefs' => 'beef',
      'brothers' => 'brother',
      'children' => 'child',
      'corpuses' => 'corpus',
      'cows' => 'cow',
      'ganglions' => 'ganglion',
      'genies' => 'genie',
      'genera' => 'genus',
      'graffiti' => 'graffito',
      'hoofs' => 'hoof',
      'loaves' => 'loaf',
      'men' => 'man',
      'monies' => 'money',
      'mongooses' => 'mongoose',
      'moves' => 'move',
      'mythoi' => 'mythos',
      'numina' => 'numen',
      'occiputs' => 'occiput',
      'octopuses' => 'octopus',
      'opuses' => 'opus',
      'oxen' => 'ox',
      'penises' => 'penis',
      'people' => 'person',
      'sexes' => 'sex',
      'soliloquies' => 'soliloquy',
      'testes' => 'testis',
      'trilbys' => 'trilby',
      'turfs' => 'turf',
      'waves' => 'wave'
    ),
  );

  /**
   * This is a non-instantiable utility class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Convert a word to its plural form.
   *
   * @param   string  $word  Word in singular
   * @return  string  Word in plural
   */
  public static function pluralize($word) {
    if (isset(self::$pluralized[$word])) {
      return self::$pluralized[$word];
    }
    extract(self::$pluralRules);

    if (!isset($regexUninflected) || !isset($regexIrregular)) {
      $regexUninflected = self::_enclose(join( '|', $uninflected));
      $regexIrregular = self::_enclose(join( '|', array_keys($irregular)));
      self::$pluralRules['regexUninflected'] = $regexUninflected;
      self::$pluralRules['regexIrregular'] = $regexIrregular;
    }

    if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
      self::$pluralized[$word] = $word;
      return $word;
    }

    if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
      self::$pluralized[$word] = $regs[1] . substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
      return self::$pluralized[$word];
    }

    foreach ($pluralRules as $rule => $replacement) {
      if (preg_match($rule, $word)) {
        self::$pluralized[$word] = preg_replace($rule, $replacement, $word);
        return self::$pluralized[$word];
      }
    }
  }

  /**
   * Convert a word to its singular form.
   *
   * @param   string  $word  Word in plural
   * @return  string  Word in singular
   */
  public static function singularize($word) {
    if (isset(self::$singularized[$word])) {
      return self::$singularized[$word];
    }
    extract(self::$singularRules);

    if (!isset($regexUninflected) || !isset($regexIrregular)) {
      $regexUninflected = self::_enclose(join( '|', $uninflected));
      $regexIrregular = self::_enclose(join( '|', array_keys($irregular)));
      self::$singularRules['regexUninflected'] = $regexUninflected;
      self::$singularRules['regexIrregular'] = $regexIrregular;
    }

    if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
      self::$singularized[$word] = $word;
      return $word;
    }

    if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
      self::$singularized[$word] = $regs[1] . substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
      return self::$singularized[$word];
    }

    foreach ($singularRules as $rule => $replacement) {
      if (preg_match($rule, $word)) {
        self::$singularized[$word] = preg_replace($rule, $replacement, $word);
        return self::$singularized[$word];
      }
    }
    self::$singularized[$word] = $word;
    return $word;
  }

  /**
   * Enclose a string for preg matching.
   *
   * @param string $string String to enclose
   * @return string Enclosed string
   */
  protected static function _enclose($string) {
    return '(?:' . $string . ')';
  }
}
