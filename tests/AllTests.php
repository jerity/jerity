<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

require_once(dirname(__FILE__).'/setUp.php');
require_once('tests/AllTests.php');

class AllTests {
  public static function suite() {
    PHPUnit_Util_Filter::addDirectoryToFilter(dirname(__FILE__));
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTestSuite(Jerity_AllTests::suite());
    return $suite;
  }
}
