<?php
/**
 * @package    JerityCore
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */

# Include the core Jerity utility class.
require_once(dirname(__FILE__).'/Jerity.class.php');

# Add Jerity Core as an autoload directory.
Jerity::addAutoloadDir(dirname(__FILE__));

# Set default global render context to HTML 4.01 Strict
RenderContext::setGlobalContext(RenderContext::makeContext(RenderContext::TYPE_HTML4_STRICT));
