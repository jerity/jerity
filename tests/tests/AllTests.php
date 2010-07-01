<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

require_once(dirname(dirname(__FILE__)).'/Configure.php');
require_once(dirname(__FILE__).'/core/AllTests.php');
require_once(dirname(__FILE__).'/template/AllTests.php');

class Jerity_AllTests {
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite();

    $suite->addTestSuite(Jerity_Core_AllTests::suite());
    $suite->addTestSuite(Jerity_Template_AllTests::suite());

    return $suite;
  }
}
?>
