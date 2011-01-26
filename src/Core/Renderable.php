<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

namespace Jerity\Core;

/**
 * Represents a renderable item.
 *
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */
interface Renderable {

  /**
   * Render the item using the current global rendering context, and return it
   * as a string.
   *
   * @return  string
   */
  public function render();

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
