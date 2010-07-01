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
 * @package    jerity.core
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */

# Include the core Jerity utility class.
require_once(dirname(__FILE__).'/Jerity.class.php');

# Add Jerity Core as an autoload directory.
Jerity::addAutoloadDir(dirname(__FILE__));

# Set default global render context to HTML 4.01 Strict
RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));
