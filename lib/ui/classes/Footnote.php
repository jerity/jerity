<?php
/**
 * @package    JerityUI
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Footnote handling class.  Each object instance can handle a separate
 * list of footnotes.  When you add a footnote, the link to the footnote
 * is returned.
 *
 * @todo  Accessors/mutators for CSS classes.
 * @todo  Support for re-use of a footnote.
 *
 * @package    JerityUI
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Footnote implements Renderable {
  /**
   * The footnotes that have been added to the page.
   *
   * @var  array
   */
  protected $footnotes = array();

  /**
   * The fragment name associated with this footnote list.
   *
   * @var  array
   */
  protected $fragment;

  /**
   * The class name for footnote links.
   *
   * @var  string
   */
  protected $class_link = 'ftn';

  /**
   * The top-level class name for the footnote list.
   *
   * @var  string
   */
  protected $class_list = 'footnotes';

  /**
   * An array of taken fragment names.
   *
   * @var  array
   */
  protected static $fragments = array();

  /**
   * Creates a footnote list object.
   *
   * If a null fragment name is provided, one will be automatically generated
   * and this will be 'ftn' followed by the addition of lowercase alphabetic
   * characters.
   *
   * Fragments can only consist of alphabetic characters.
   *
   * @param  string  $fragment  The URL fragment base to use for this list.
   *
   * @throws  Exception
   * @throws  OverflowException
   */
  public function __construct($fragment = null) {
    if (is_null($fragment)) {
      $base = $fragment = 'ftn';
      $count = 97;
      while (in_array($fragment, self::$fragments)) {
        if ($count === 123) {
          throw new OverflowException('Fragment name pool overflow.');
        }
        $fragment = $base.chr($count++);
      }
    } elseif (in_array($fragment, self::$fragments) || !preg_match('/^[A-Za-z]+$/', $fragment)) {
      throw new Exception('Fragment name already in use.');
    }
    self::$fragments[] = $this->fragment = $fragment;
  }

  /**
   * Adds a footnote to the page and returns a link to the footnote item.
   *
   * @param  string   $footnote  The footnote text
   * @param  boolean  $escape    Whether to escape the footnote content.
   *
   * @return  string  The rendered footnote link.
   */
  public function add($footnote, $escape = false) {
    $this->footnotes[] = ($escape ? String::escape($footnote) : $footnote);
    $index = count($this->footnotes);
    $attrs = array(
      'class' => $this->class_link,
      'href'  => '#'.$this->fragment.$index,
      'id'    => $this->fragment.'-'.$index,
    );
    return Tag::renderTag('a', $attrs, $index);
  }

  /**
   * Render this footnote list using the current render context and return it 
   * as a string.
   *
   * @return  string
   */
  public function render() {
    $out = Tag::renderTag('ul', array('class' => $this->class_list)).PHP_EOL;
    $index = 1;
    foreach ($this->footnotes as $footnote) {
      $attrs = array(
        'href'  => '#'.$this->fragment.'-'.$index,
      );
      $a = Tag::renderTag('a', $attrs, $index);
      $attrs = array(
        'id'    => $this->fragment.$index,
      );
      $out .= Tag::renderTag('li', $attrs, $a.': '.$footnote).PHP_EOL;
      $index++;
    }
    $out .= '</ul>'.PHP_EOL;
    return $out;
  }

  /**
   * Render this footnote list using the current render context and return it 
   * as a string.
   *
   * @return  string
   *
   * @see     Footnote::render()
   */
  public function __toString() {
    return $this->render();
  }
}
