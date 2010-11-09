<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.template
 */

/**
 * The Content template is used to load content-specific template files and
 * provide an object that can be taken in by a Chrome template which will
 * identify it as a main content block.
 *
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.template
 */
class Content extends Template {

  /**
   * Passes the template name with appropriate prefix up to the template
   * constructor.
   *
   * @param  string  $t  The template to use.
   */
  public function __construct($t) {
    parent::__construct('content/'.$t);
  }

  /**
   * Create a new content template in a fluent API manner.
   *
   * @param  string  $t  The template to use.
   *
   * @return  Content
   * @see     self::__construct()
   *
   * @todo  Replace with PHP 5.3 late static binding support?
   */
  public static function create($t) {
    return new Content($t);
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
