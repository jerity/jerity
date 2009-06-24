<?php
/**
 * @package JerityCore
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

require_once(dirname(__FILE__).'/Jerity.class.php');

Jerity::addAutoloadDir(dirname(__FILE__));

// default global render context: HTML 4.01 strict
RenderContext::setGlobalContext(RenderContext::makeContext(RenderContext::TYPE_HTML4_STRICT));
