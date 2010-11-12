<?php
/*
Revision History:
-----------------

v1002 26-mar-06 AY : updated benchmark() to include mysql query count
v1001 26-mar-06 AY : updated NOTICE errors in benchmark() if initBenchmarking() not called 
v1000 15-dec-05 AY : updated to output valid comments inside css/js files
*/

function initBenchmarking()
{
	global $_SI_benchmarking;

	$rusage = getrusage();
	$_SI_benchmarking['utime_before'] = $rusage["ru_utime.tv_sec"] + $rusage["ru_utime.tv_usec"] / 1000000;
	$_SI_benchmarking['stime_before'] = $rusage["ru_stime.tv_sec"] + $rusage["ru_stime.tv_usec"] / 1000000;

	stopwatch();

	$_SI_benchmarking['initial_mem_usage'] = memory_get_usage();
}

// stopwatch function adapted from http://uk2.php.net/microtime - AY 15-sep-2005
function stopwatch($sinceBeginning = false, $echo = false){
   static $mt_start = 0;
   static $mt_previous = 0;

   list($usec, $sec) = explode(" ",microtime());
   $mt_current = (float)$usec + (float)$sec;

   if (!$mt_start) {
     $mt_start = $mt_current;
     $mt_previous = $mt_current;
     $val = '';
   } else {
     $mt_diff = ($mt_current - ($sinceBeginning ? $mt_start : $mt_previous));
     $mt_previous = $mt_current;
     $val = sprintf('%.16f',$mt_diff);
   }

   if ($echo) {
	echo "<!--  $val -->\n";
   }
   return $val;
}

function benchmark($extra = false)
{
	$contentType = getContentType();
	if (strpos($contentType, 'xml') !== false) {
		return; // don't do anything to mess up xml docs!
	}
	
	global $_SI_benchmarking, $_db, $_pageSettings;
	$dat = getrusage();
	
	if ((isset($_SI_benchmarking['hide']) && $_SI_benchmarking['hide']) || (isset($_pageSettings['hide_benchmarking']) && $_pageSettings['hide_benchmarking'])) {
		// skip
		return;
	}
	
	$_SI_benchmarking['final_mem_usage'] = getSizeHR(memory_get_usage());
	
	$_SI_benchmarking['utime_after'] = $dat["ru_utime.tv_sec"] + $dat["ru_utime.tv_usec"] / 1000000;
	$_SI_benchmarking['stime_after'] = $dat["ru_stime.tv_sec"] + $dat["ru_stime.tv_usec"] / 1000000;
	
	$_SI_benchmarking['utime_elapsed'] = isset($_SI_benchmarking['utime_before']) ? ($_SI_benchmarking['utime_after'] - $_SI_benchmarking['utime_before']) : '-';
	$_SI_benchmarking['stime_elapsed'] = isset($_SI_benchmarking['stime_before']) ? ($_SI_benchmarking['stime_after'] - $_SI_benchmarking['stime_before']) : '-';
	
	$_SI_benchmarking['realtime'] = stopwatch(true);

	$query_count = null;
	if (class_exists('Propel')) {
		$con = Propel::getConnection();
		if ($con instanceof DebugPDO) {
			$query_count = $con->getQueryCount();
		}
	}

	if ((strpos($contentType, 'css') === false) && (strpos($contentType, 'javascript') === false))
	{
		$pre = '<!-- ';
		$post = ' -->';

		if ($extra)
		{
			ed($_SI_benchmarking);
		}
	} elseif (strpos($contentType, 'javascript') !== false) {
		$pre = '// ';
		$post = ' ';
	} else {
		$pre = '/* ';
		$post = ' */';
	}
	
	printf("\n" . $pre . 'Page created in %.5f seconds (%s seconds actual processor time). Memory usage: %s.' . (!is_null($query_count) ? ' Queries: %s.' : '') . $post ."\n", $_SI_benchmarking['realtime'], $_SI_benchmarking['utime_elapsed'] + $_SI_benchmarking['stime_elapsed'], $_SI_benchmarking['final_mem_usage'], $query_count);
}
?>
