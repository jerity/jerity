<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

if (is_object($this) && $this instanceof Template) {
  echo 'PASS';
  return;
}
var_dump($this);

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
