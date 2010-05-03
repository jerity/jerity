<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

if (!isset($count)) $count=count($this->getContent());
for ($i=0; $i<$count; $i++) {
  $c = $this->getNextContent();
  if (!isset($compact) || $compact) {
    print $c.'|';
  } else {
    if (is_null($c)) {
      printf("%s Content %2d %s\n", str_repeat('#', 5), $i, str_repeat('#', 5));
    } else {
      printf("%s Content %2d %s\n", str_repeat('=', 5), $i, str_repeat('=', 5));
      echo $c;
    }
    echo "\n".str_repeat(22, '=')."\n";
  }
}
