<?php
/**
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Pluralisation/singularisation rules for English words.
 *
 * Rewritten from scratch with better rules and implementation.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Inflector {
  /**
   * Inflector rules for pluralizing words.
   *
   * @var array
   */
  protected static $plural = array(
    'rules' => array(
      '/(quiz)$/i' => '\1zes',
      '/^(ox)$/i' => '\1en',
      '/([lm])ouse$/i' => '\1ice',
      '/(matr)ix$/i' => '\1ices',
      '/(ap|cod|cort|ind|lat|sil|simpl|v[eo]rt)ex$/i' => '\1ices',
      '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
      '/(alg|alumn|formul|vertebr)a$/i' => '\1ae',
      '/(ali|atl|canv)as$/i' => '\1ases',
      '/(ax|test)is$/i' => '\1es',
      '/(ch|sh|ss|x|z)$/i' => '\1es',
      '/(^[dg]|her|potat|tomat)o$/i' => '\1oes',
      '/([^aeiouy]|qu)y$/i' => '\1ies',
      '/(hoo|l[eo]a)f$/i' => '\1ves',
      '/lf$/i' => 'lves',
      '/([^afo])fe$/i' => '\1ves',
      '/([dimrtv])um$/i' => '\1a',
      '/person$/i' => 'people',
      '/man$/i' => 'men',
      '/child$/i' => 'children',
      '/tooth$/i' => 'teeth',
      '/us$/i' => 'uses',
      '/sis$/i' => 'ses',
      '/s$/' => 's',
      '/^$/' => '',
      '/$/' => 's',
    ),
    'irregular' => array(
      'ascensor' => 'ascensores',
      'criterion' => 'criteria',
      'delouse' => 'delouses',
      'dwarf' => 'dwarves',
      'genus' => 'genera',
      'goose' => 'geese',
      'graffito' => 'graffiti',
      'lease' => 'leases',
      'money' => 'monies',
      'murex' => 'murecis',
      'mythos' => 'mythoi',
      'numen' => 'numina',
      'pannino' => 'pannini',
      'penis' => 'penises',
      'trilby' => 'trilbys',
    ),
    'uncountable' => array(
      '.*media',
    ),
    'cache' => array(
      'irregular'   => null,
      'uncountable' => null,
      'lookup'      => array(),
    ),
  );

  /**
   * Inflector rules for singularizing words.
   *
   * @var array
   */
  protected static $singular = array(
    'rules' => array(
      '/(quiz)zes$/i' => '\1',
      '/^(ox)en$/i' => '\1',
      '/([lm])ice$/i' => '\1ouse',
      '/(matr)ices$/i' => '\1ix',
      '/(ap|cod|cort|ind|lat|sil|simpl|v[eo]rt)ices$/i' => '\1ex',
      '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
      '/(alg|alumn|formul|vertebr)ae$/i' => '\1a',
      '/(ali|atl|canv)ases$/i' => '\1as',
      '/([ftw]ax)es$/i' => '\1',
      '/(ax|test)es$/i' => '\1is',
      '/izes$/i' => 'ize',
      '/(ch|sh|ss|x|z)es$/i' => '\1',
      '/([dg]|her|potat|tomat)oes$/i' => '\1o',
      '/(cook|gen|mov)ies$/i' => '\1ie',
      '/([^aeiouy]|qu)ies$/i' => '\1y',
      '/([htr]ive)s$/i' => '\1',
      '/(hoo|l[eo]a)ves$/i' => '\1f',
      '/([lr])ves$/i' => '\1f',
      '/([^afo])ves$/i' => '\1fe',
      '/([dimrtv])a$/i' => '\1um',
      '/people$/i' => 'person',
      '/men$/i' => 'man',
      '/children$/i' => 'child',
      '/teeth$/i' => 'tooth',
      '/([ao])uses$/i' => '\1use',
      '/([anpr])ses$/i' => '\1se',
      '/uses$/i' => 'us',
      '/ses$/i' => 'sis',
      '/s$/' => '',
    ),
    'irregular' => array(
      'ascensores' => 'ascensor',
      'caches' => 'cache',
      'closes' => 'close',
      'criteria' => 'criterion',
      'geese' => 'goose',
      'genera' => 'genus',
      'graffiti' => 'graffito',
      'leases' => 'lease',
      'mongooses' => 'mongoose',
      'monies' => 'money',
      'murecis' => 'murex',
      'mythoi' => 'mythos',
      'numina' => 'numen',
      'pannini' => 'pannino',
      'penises' => 'penis',
      'synopses' => 'synopsis',
    ),
    'uncountable' => array(
      '.*ss',
      '.+media',
    ),
    'cache' => array(
      'irregular'   => null,
      'uncountable' => null,
      'lookup'      => array(),
    ),
  );

  /**
   * A common list of words that are uncountable.
   *
   * @var array
   */
  protected static $uncountable = array(
    '.*[lmnr]ese', '.*deer', '.*fish', '.*ois', '.*pox', '.*sheep', 
    'analytics', 'bison', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 
    'carp', 'chassis', 'clippers', 'cod', 'corps', 'debris', 'diabetes', 
    'djinn', 'dynamo', 'elk', 'equipment', 'feedback', 'flounder', 'gallows', 
    'gallows', 'headquarters', 'herpes', 'hijinks', 'homework', 'housework', 
    'hubris', 'information', 'innings', 'jackanapes', 'junk', 'mackerel', 
    'measles', 'moose', 'mumps', 'news', 'nexus', 'peoples', 'pincers', 
    'pliers', 'proceedings', 'rabies', 'rhinoceros', 'rice', 'salmon', 
    'scissors', 'sea-bass', 'series', 'shears', 'species', 'swine', 'trousers', 
    'trout', 'tuna', 'wildebeest', 'contretemps',
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
   * @param  string  $word  Word in singular form.
   *
   * @return  string  Word in plural form.
   */
  public static function pluralize($word) {
    return self::inflect($word, self::$plural);
  }

  /**
   * Convert a word to its singular form.
   *
   * @param  string  $word  Word in plural form.
   *
   * @return  string  Word in singular form.
   */
  public static function singularize($word) {
    return self::inflect($word, self::$singular);
  }

  /**
   * Inflects a word according to the passed ruleset.
   *
   * @todo  Attempt to return inflected word in case matching the passed word.
   *
   * @param  string  $word     The word to inflect.
   * @param  array   $ruleset  The rules to use for inflection.
   *
   * @return  string  The word inflected according to the ruleset.
   */
  protected static function inflect($word, array &$ruleset) {
    extract($ruleset, EXTR_REFS);

    // Build and cache regular expressions.
    if (!isset($cache['uncountable']) || !isset($cache['irregular'])) {
      $cache['uncountable'] = '(?:' . join( '|', array_merge(self::$uncountable, $uncountable)) . ')';
      $cache['irregular']   = '(?:' . join( '|', array_keys($irregular)) . ')';
    }

    // Check to see if we've already inflected.
    if (isset($cache['lookup'][$word])) return $cache['lookup'][$word];

    // Check the uncountable list to see if word should not be altered.
    if (!empty($uncountable) && preg_match('/^(' . $cache['uncountable'] . ')$/i', $word)) {
      $cache['lookup'][$word] = $word;
      return $cache['lookup'][$word];
    }

    // Check the irregular list to see if the word is a special case.
    if (!empty($irregular) && preg_match('/^(.*)\\b(' . $cache['irregular'] . ')$/i', $word, $match)) {
      $cache['lookup'][$word] = $match[1] . $irregular[strtolower($match[2])];
      return $cache['lookup'][$word];
    }

    // Use standard rule list to perform inflection.
    foreach ($rules as $rule => $replacement) {
      if (!!preg_match($rule, $word)) {
        $cache['lookup'][$word] = preg_replace($rule, $replacement, $word);
        return $cache['lookup'][$word];
      }
    }

    // Return the original word if we could apply a rule for some reason.
    return $word;
  }

}
