<?php
/**
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

/**
 * A wrapper class for rendering simple content.
 *
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
class SimpleContent implements Renderable {
  /**
   * The content to be rendered.
   *
   * @var mixed
   */
  protected $content = '';

  /**
   * Create the wrapper around some content.
   *
   * @param mixed $content Content to be output when render() is called; should
   * ideally be a string.
   */
  public function __construct($content) {
    $this->content = $content;
  }

  /**
   * Render the item using the current global rendering context, and return it
   * as a string.
   *
   * @return string
   */
  public function render() {
    return $this->content;
  }
}

?>
