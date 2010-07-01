<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

/**
 * @package    jerity.template
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * The Content template is used to load content-specific template files and
 * provide an object that can be taken in by a Chrome template which will
 * identify it as a main content block.
 *
 * @package    jerity.template
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
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
