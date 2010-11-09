<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

/**
 * Benchmarking class for measuring execution time, memory usage and counting
 * database queries.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */
class Benchmark {

  /**
   * Whether the information should be immediately printed or returned.
   *
   * @var  bool
   */
  protected static $print = false;

  /**
   * Whether the benchmark is currently running.
   *
   * @var  bool
   */
  protected static $running = false;

  /**
   * Stores the benchmark data.
   *
   * @var  array
   */
  protected static $data = array();

  /**
   * Non-instantiable class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() {
  }
  // @codeCoverageIgnoreEnd

  /**
   * Starts benchmarking.
   *
   * @param  bool  $print  Whether to immediately print out the result or
   *                       return it.
   *
   * @return  string  A benchmark information comment.
   */
  public static function start($print = false) {
    if (self::$running) {
      throw new BenchmarkException('Benchmark is already running.');
    }
    self::$print = $print;
    self::mark();
    self::$running = true;
    return self::output();
  }

  /**
   * Collects data at this interval.
   *
   * @param  bool  $verbose  Whether to output full information.
   *
   * @return  string  A benchmark information comment.
   */
  public static function interval($verbose = false) {
    if (!self::$running) {
      throw new BenchmarkException('Benchmarking has not been started.');
    }
    self::mark();
    return self::output($verbose);
  }

  /**
   * Stops benchmarking and gives back a final summary.
   *
   * @return  string  A benchmark information comment.
   */
  public static function stop() {
    if (!self::$running) {
      throw new BenchmarkException('Benchmarking has not been started.');
    }
    self::mark();
    self::$running = false;
    return self::output(true);
  }

  /**
   * Checks whether the benchmark is running.
   *
   * @return  bool  Whether the benckmark is running.
   */
  public static function isRunning() {
    return self::$running;
  }

  /**
   * Collects the benchmark data at the current point in time.
   */
  protected static function mark() {
    self::$data[] = array(
      'time'   => self::getTimeInfo(),
      'memory' => self::getMemoryInfo(),
      'query'  => self::getQueryInfo(),
      'cache'  => self::getCacheInfo(),
    );
  }

  /**
   * Outputs the information in the correct format.
   *
   * @param  bool  $verbose  Whether to output full information.
   *
   * @return  string  The benchmark data up to the current point.
   */
  protected static function output($verbose = false) {
    # Choose comment string based on render context.
    $ctx = RenderContext::get();
    switch ($ctx->getLanguage()) {
      case RenderContext::LANG_HTML:
      case RenderContext::LANG_XHTML:
        $prefix  = '<!-- Benchmark:';
        $postfix = '-->';
        break;
      case RenderContext::LANG_JS:
      case RenderContext::LANG_CSS:
        $prefix  = '/*** Benchmark:';
        $postfix = '***/';
        break;
      default:
        throw new BenchmarkException('Unsupported render context.');
    }
    # Retrieve required data.
    $a =& self::$data[0];
    $b =& self::$data[count(self::$data)-1];
    # Calculate time differences.
    $real   = $b['time']['real']   - $a['time']['real'];
    $user   = $b['time']['user']   - $a['time']['user'];
    $system = $b['time']['system'] - $a['time']['system'];
    # Generate output.
    $output = $prefix;
    if ($verbose) {
      $output .= PHP_EOL.'     ';
      $output .= sprintf('Time:    %.5fs real, %.5fs user, %.5fs system', $real, $user, $system);
      if (isset($b['memory'])) {
        $output .= PHP_EOL.'     ';
        $output .= sprintf('Memory:  %s current, %s peak',
          String::formatBytes($b['memory']['current'], false, null, 2),
          String::formatBytes($b['memory']['peak'], false, null, 2));
      }
      $output .= PHP_EOL.'     ';
      $output .= sprintf('Queries: %d', $b['query']['count']);
      if (isset($b['cache'])) {
        $output .= PHP_EOL.'     ';
        $output .= sprintf('Cache:   %d hits, %d misses, %s used, %s available',
          $b['cache']['hits'], $b['cache']['misses'],
          String::formatBytes($b['cache']['memory_used'], false, null, 2),
          String::formatBytes($b['cache']['memory_limit'], false, null, 2));
      }
      $output .= ' ';
    } else {
      $output .= ' ';
      $output .= sprintf('Time: %.5fs', $real);
      $output .= '; ';
      $output .= sprintf('Memory: %s',
        String::formatBytes($b['memory']['peak'], false, null, 2));
      $output .= '; ';
      $output .= sprintf('Queries: %d', $b['query']['count']);
      $output .= ' ';
    }
    $output .= $postfix . PHP_EOL;
    if (self::$print) echo $output;
    return $output;
  }

  /**
   * Returns an array containing current real, user and system time.
   *
   * @return  array  Current execution times.
   */
  protected static function getTimeInfo() {
    $r = getrusage();
    return array(
      'real'   => microtime(true),
      'user'   => $r['ru_utime.tv_sec'] + $r['ru_utime.tv_usec'] / 1e6,
      'system' => $r['ru_stime.tv_sec'] + $r['ru_stime.tv_usec'] / 1e6,
    );
  }

  /**
   * Returns an array containing the real current and peak memory usage.
   *
   * @return  array  Information about memory usage.
   */
  protected static function getMemoryInfo() {
    return array(
      'current' => memory_get_usage(true),
      'peak'    => memory_get_peak_usage(true),
    );
  }

  /**
   * Gets information about queries to the database that have been executed.
   *
   * @return  array  Information about the executed database queries.
   *
   * @todo  Add ability to register a callback for custom query counting.
   *
   * @see  DebugPDO
   */
  protected static function getQueryInfo() {
    $count = 0;
    if (class_exists('Propel')) {
      $connection = Propel::getConnection();
      if ($connection instanceof DebugPDO) {
        $count += $connection->getQueryCount();
      }
    }
    return array('count' => $count);
  }

  /**
   * Gets information about PHP caching.
   *
   * @return  array  Information about cache.
   */
  protected static function getCacheInfo() {
    if (!extension_loaded('apc')) return null;
    if (!function_exists('apc_cache_info')) return null;
    $info = apc_cache_info();
    static $limit = null;
    if (is_null($limit)) {
      $memory   = ini_get('apc.shm_size');
      $segments = ini_get('apc.shm_segments');
      $memory   = isset($memory)   ? $memory   : 30;
      $segments = isset($segments) ? $segments : 1;
      $limit = Number::parseBytes(($memory * $segments) . 'MiB');
    }
    return array(
      'hits'         => isset($info['num_hits']) ? $info['num_hits'] : null,
      'misses'       => isset($info['num_misses']) ? $info['num_misses'] : null,
      'memory_used'  => isset($info['mem_size']) ? $info['mem_size'] : null,
      'memory_limit' => $limit,
    );
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
