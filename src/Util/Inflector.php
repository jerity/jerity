<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.util
 */

namespace Jerity\Util;

/**
 * Pluralisation/singularisation rules for English words.
 *
 * Rewritten from scratch with better rules and implementation:
 * - Supports standard and irregular rules for pluralisation/singularisation.
 * - Supports many uncountable words.
 * - Attempts to detect original letter case of word and return in same case.
 * - Can disable letter case fixing such as for abbreviations: URL --> URLs.
 * - Useful for code generation:
 *  - Splits on ' ' and '_' by default, and
 *  - Splits camel-cased words for correctness, e.g. getGoose --> getGeese
 *
 * Some awkward cases where there are multiple pluralised forms:
 * - brother --> brothers/bretheren
 * - cow     --> cows/kine
 * - dominatrix --> dominatri(ce|x)s
 * - iris    --> irises/iris/irides
 * - octopus --> octopodes/octopuses
 * - sister  --> sisters/sistren
 * - virus   --> viri/virii
 * - *arf --> *ar(f|ve)s
 * - *eau --> *eau(x|s)
 * - (dogm|schem)a --> $1(as|ata)
 * - (archipelag|buffal|carg|hal|innuend|mosquit|mott|n|tornad|toped|volcan|zer)o --> $1(os|oes)
 *
 * See the following sites for alternative pluralizer implementations:
 *
 * @see http://kuwamoto.org/2007/12/17/improved-pluralizing-in-php-actionscript-and-ror/
 * @see http://dev.rubyonrails.org/browser/trunk/activesupport/lib/active_support/inflections.rb
 *
 * See the following sites for grammar rules and irregular plurals:
 *
 * @see http://www2.gsu.edu/~wwwesl/egw/crump.htm
 * @see http://www2.gsu.edu/~wwwesl/egw/pluralsl.htm
 * @see http://www.fortunecity.com/bally/durrus/153/gramch13.html
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.util
 */
class Inflector {
  /**
   * Inflector rules for pluralizing words.
   *
   * @var  array
   */
  protected static $plural = array(
    'rules' => array(
      '/(quiz)$/i' => '\1zes',
      '/^(ox)$/i' => '\1en',
      '/([lm])ouse$/i' => '\1ice',
      '/(anacoluth|phenomen|polyhedr)on$/i' => '\1a',
      '/(append|aviatr|cerv|forn|hel|dominatr|matr)ix$/i' => '\1ices',
      '/(ap|cod|cort|lat|vert)ex$/i' => '\1ices',
      '/(ind|sil|simpl|vort)(ex)$/i' => '\1\2es',
      '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin)us$/i' => '\1i',
      '/(alg|alumn|formul|vertebr)a$/i' => '\1ae',
      '/(ali|atl|canv)(as)$/i' => '\1\2es',
      '/(ma)$/i' => '\1ta',
      '/([blnv]a)$/i' => '\1e',
      '/(it?a)$/i' => '\1e',
      '/(ax|test)is$/i' => '\1es',
      '/(ch|sh|ss|x|z)$/i' => '\1es',
      '/(buffal|carg|ech|embarg|her|innuend|^n|potat|tomat|torped|vet|zer)(o)$/i' => '\1\2es',
      '/([^aeiouy]|qu)y$/i' => '\1ies',
      '/(l[eo]a|l)f$/i' => '\1ves',
      '/([^afo])fe$/i' => '\1ves',
      '/(b|bat|bur|chat|tabl)(eau)$/i' => '\1\2x',
      '/([dilmrtv])um$/i' => '\1a',
      '/person$/i' => 'people',
      '/man$/i' => 'men',
      '/(child)$/i' => '\1ren',
      '/(x|zo)on$/i' => '\1a',
      '/(ar)f$/i' => '\1ves',
      '/foot$/i' => 'feet',
      '/tooth$/i' => 'teeth',
      '/([cr]is)$/i' => '\1es',
      '/([eu]s)$/i' => '\1es',
      '/(iti)s$/i' => '\1des',
      '/(s)is$/i' => '\1es',
      '/(s)$/i' => '\1',
      '/(-in-law|-up)$/i' => 's\1',
      '/^$/' => '',
      '/(\D)$/' => '\1s',
    ),
    'irregular' => array(
      'ascensor' => 'ascensores',
      'baristo' => 'baristi',
      'blouse' => 'blouses',
      'coccus' => 'cocci',
      'corpus' => 'corpora',
      'criterion' => 'criteria',
      'delouse' => 'delouses',
      'die' => 'dice',
      'dogma' => 'dogmas',
      'dwarf' => 'dwarfs',
      'forum' => 'forums',
      'genie' => 'genii',
      'genus' => 'genera',
      'goose' => 'geese',
      'graffito' => 'graffiti',
      'lease' => 'leases',
      'madame' => 'madams',
      'memorandum' => 'memorandums',
      'money' => 'monies',
      'mythos' => 'mythoi',
      'numen' => 'numina',
      'occiput' => 'occipita',
      'panino' => 'panini',
      'penis' => 'penises',
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
   * @var  array
   */
  protected static $singular = array(
    'rules' => array(
      '/(quiz)zes$/i' => '\1',
      '/^(ox)en$/i' => '\1',
      '/([lm])ice$/i' => '\1ouse',
      '/(anacoluth|automat|gangli|phenomen|polyhedr)a$/i' => '\1on',
      '/(append|aviatr|cerv|dominatr|forn|hel|matr)ices$/i' => '\1ix',
      '/(ap|cod|cort|ib|ind|lat|simpl|v[eo]rt)ices$/i' => '\1ex',
      '/(ind|sil|simpl|vort)(ex)es$/i' => '\1\2',
      '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
      '/(alg|alumn|formul|vertebr)ae$/i' => '\1a',
      '/(ali|atl|canv)ases$/i' => '\1as',
      '/(ma)ta$/i' => '\1',
      '/(a)e$/i' => '\1',
      '/([ftw]ax)es$/i' => '\1',
      '/(ax|test)es$/i' => '\1is',
      '/izes$/i' => 'ize',
      '/(ch|sh|ss|x|z)es$/i' => '\1',
      '/(albin|archipelag|buffal|carg|ech|embarg|fiasc|ghett|hal|her|innuend|ling|manifest|mement|mosquit|mott|^n|potat|tomat|tornad|torped|vet|volcan|zer)(o)e?s$/i' => '\1\2',
      '/(cook|gen|mov)ies$/i' => '\1ie',
      '/([^aeiouy]|qu)ies$/i' => '\1y',
      '/([htr]ive)s$/i' => '\1',
      '/(hoo|l[eo]a)ves$/i' => '\1f',
      '/([lr])(f|ve)s$/i' => '\1f',
      '/([^afo])ves$/i' => '\1fe',
      '/(eau)[xs]$/i' => '\1',
      '/([dilmrtv])a$/i' => '\1um',
      '/people$/i' => 'person',
      '/men$/i' => 'man',
      '/(child)ren$/i' => '\1',
      '/(x|zo)a$/i' => '\1on',
      '/feet$/i' => 'foot',
      '/teeth$/i' => 'tooth',
      '/([ao])uses$/i' => '\1use',
      '/([anr]|ap)ses$/i' => '\1se',
      '/((?:[uw]|[hr]o|pe|ci)s)es$/i' => '\1',
      '/([ct]i)des$/i' => '\1s',
      '/(s)es$/i' => '\1is',
      '/(cherub|seraph)im$/i' => '\1',
      '/s(-in-law|-up)$/i' => '\1',
      '/s$/i' => '',
    ),
    'irregular' => array(
      'algaes' => 'alga',
      'areæ' => 'area',
      'ascensores' => 'ascensor',
      'atlantes' => 'atlas',
      'baristi' => 'baristo',
      'brethren' => 'brother',
      'busses' => 'bus',
      'caches' => 'cache',
      'cattle' => 'cow',
      'closes' => 'close',
      'cocci' => 'coccus',
      'corpora' => 'corpus',
      'criteria' => 'criterion',
      'croci' => 'crocus',
      'dice' => 'die',
      'embryones' => 'embryo',
      'emphases' => 'emphasis',
      'geese' => 'goose',
      'genera' => 'genus',
      'genii' => 'genie',
      'graffiti' => 'graffito',
      'innuendis' => 'innuendo',
      'irides' => 'iris',
      'irises' => 'iris',
      'kine' => 'cow',
      'leases' => 'lease',
      'madams' => 'madame',
      'meese' => 'moose',
      'mesdames' => 'madame',
      'mongeese' => 'mongoose',
      'mongooses' => 'mongoose',
      'monies' => 'money',
      'mooses' => 'moose',
      'mythoi' => 'mythos',
      'nexûs' => 'nexus',
      'numina' => 'numen',
      'oases' => 'oasis',
      'occipita' => 'occiput',
      'octopodes' => 'octopus',
      'opera' => 'opus',
      'panini' => 'panino',
      'penes' => 'penis',
      'penises' => 'penis',
      'probosci' => 'proboscis',
      'rhinocerotes' => 'rhinoceros',
      'sistren' => 'sister',
      'statūs' => 'status',
      'synopses' => 'synopsis',
      'virii' => 'virus',
    ),
    'uncountable' => array(
      '.*ss',
      '.+media',
      'iris',
      'nexus',
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
   * @var  array
   */
  protected static $uncountable = array(
    '.*[lmnr]ese', '.*deer', '.*fish', '.*ois', '.+pox', '.*sheep',
    'analytics', 'bison', 'bream', 'breeches', 'britches', 'cantus',
    'carp', 'chassis', 'clippers', 'cod', 'corps', 'debris', 'diabetes',
    'elk', 'equipment', 'feedback', 'flounder', 'gallows',
    'gallows', 'headquarters', 'herpes', 'hijinks', 'homework', 'housework',
    'hubris', 'information', 'innings', 'junk', 'grouse', 'ibex',
    'measles', 'moose', 'mumps', 'news', 'peoples', 'pincers',
    'pliers', 'proceedings', 'rabies', 'rhinoceros', 'rice', 'ninja',
    'scissors', 'sea-bass', 'series', 'shears', 'species', 'swine', 'trousers',
    'trout', 'tuna', 'wildebeest', 'contretemps', 'crossroads',
    'halibut', 'offspring', 'music', 'pasta', 'surf', 'malware'
  );

  /**
   * A list of characters to split the string on to simplify it for inflection.
   *
   * @var  array
   */
  protected static $separators = array(' ', '_');

  /**
   * This is a non-instantiable utility class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Convert a word to its plural form.  Pass \t false to \t $fix if you do not
   * want to convert the letter case of the output based on the letter case of
   * the input word.  An example of this is pluralisation of an abbreviation
   * where it is usual to want "URL" to become "URLs" and not "URLS".
   *
   * @param  string   $word  Word in singular form.
   * @param  boolean  $fix   Whether to fix the letter case.
   *
   * @return  string  Word in plural form.
   */
  public static function pluralize($word, $simplify = false, $fix = true) {
    $stub = '';
    if ($simplify) list($stub, $word) = self::simplify($word);
    if (!$word && $stub) return $stub;
    $inflected = self::inflect($word, self::$plural);
    if ($fix) $inflected = self::fixLetterCase($word, $inflected);
    return $stub.$inflected;
  }

  /**
   * Convert a word to its singular form.  Pass \t false to \t $fix if you do
   * not want to convert the letter case of the output based on the letter case
   * of the input word.
   *
   * @param  string   $word  Word in plural form.
   * @param  boolean  $fix   Whether to fix the letter case.
   *
   * @return  string  Word in singular form.
   */
  public static function singularize($word, $simplify = false, $fix = true) {
    $stub = '';
    if ($simplify) list($stub, $word) = self::simplify($word);
    if (!$word && $stub) return $stub;
    $inflected = self::inflect($word, self::$singular);
    if ($fix) $inflected = self::fixLetterCase($word, $inflected);
    return $stub.$inflected;
  }

  /**
   * Fixes the letter case of the inflected word to match that of the original
   * if possible.
   *
   * @param  string  $original   The word before inflection.
   * @param  string  $inflected  The word after inflection.
   *
   * @return  string  The inflected word with the letter case fixed.
   */
  protected static function fixLetterCase($original, $inflected) {
    switch (true) {
      case String::isLower($original):
        return strtolower($inflected);
      case String::isUpper($original):
        return strtoupper($inflected);
      case String::isTitleCase($original):
        return ucfirst($inflected);
      default:
        return $inflected;
    }
  }

  /**
   * Splits a string up into two parts with the second part being a word that
   * can be pluralised.
   *
   * @param  string  $word  The word to simplify for pluralisation.
   *
   * @return  array  The split word.
   */
  protected static function simplify($word) {
    $stub = $join = '';
    foreach (self::$separators as $separator) {
      $a = explode($separator, $word);
      $word = array_pop($a);
      if (count($a)) {
        $stub = $stub.$join.implode($separator, $a);
        $join = $separator;
      }
    }
    if (!String::isLower($word) && !String::isUpper($word)) {
      $a = String::splitCamelCase($word);
      $word = array_pop($a);
      if (count($a)) {
        $stub = $stub.$join.implode('', $a);
        $join = '';
      }
    }
    return array($stub.$join, $word);
  }

  /**
   * Inflects a word according to the passed ruleset.
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

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
