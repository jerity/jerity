<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

# Include the core Jerity utility class.
require_once(dirname(__FILE__).'/Jerity.class.php');

# Add Jerity Core as an autoload directory.
Jerity::addAutoloadDir(dirname(__FILE__));

# Set default global render context to HTML 4.01 Strict
RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
