<?php

set_exception_handler(array('ErrorHandler', 'GlobalExceptionHandler'));
set_error_handler(array('ErrorHandler', 'GlobalErrorHandler'), E_ALL &~ E_STRICT);

if (!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
if (!defined('E_DEPRECATED'))        define('E_DEPRECATED', 8192);
if (!defined('E_USER_DEPRECATED'))   define('E_USER_DEPRECATED', 16384);

class ErrorHandler {

	public static function GlobalExceptionHandler($exception) {
		self::hasErred(true);		
		$msg = "PHP Fatal error: Uncaught exception";
		$msg .= self::getExceptionLogString($exception);
		// output to PHP error log as configured by error_log ini setting
		self::_logError($msg, false, false); // don't add extra info, don't display
		if (ini_get('display_errors')) {
			self::displayError($msg);
		} else {
			// output message
			require_once '_layouts/php_error.php';
		}
		// die script, die!
		die();
	}

	/**
	 * Global error handler
   * - This is only really useful for E_RECOVERABLE_ERROR since we want to 
   * treat all non-fatal errors exactly as php does by default (log, dont 
   * display)
   *   and our PHP user-defined error handler can't catch any fatal errors 
   *   (E_ERROR etc) apart from E_RECOVERABLE_ERROR (which I gather is 
   *   currently 
	 *   just used for type hint violation..?)
	 */
	public static function GlobalErrorHandler($code, $message, $file, $line, $context) {
		$msg = "PHP ";
		$fatal = true;
		$strong = (ini_get('html_errors') ? '<strong>' : '');
		$endstrong = (ini_get('html_errors') ? '</strong>' : '');
		switch ($code) {
			case E_WARNING:
				$msg .= 'Warning:';
				$fatal = false;
				break;
			case E_NOTICE:
				$msg .= 'Notice:';
				$fatal = false;
				break;
			case E_USER_ERROR:
				$msg .= 'User Error:';
				$fatal = false;
				break;
			case E_USER_WARNING:
				$msg .= 'User Warning:';
				$fatal = false;
				break;
			case E_USER_NOTICE:
				$msg .= 'User Notice:';
				$fatal = false;
				break;
			case E_STRICT:
				$msg .= 'Strict Error:';
				$fatal = false;
				break;
			case E_RECOVERABLE_ERROR:
				$msg .= 'Recoverable Error:';
				// treated as E_ERROR (i.e. fatal) if not caught by custom handler
				break;
			case E_DEPRECATED:
				$msg .= 'Deprecated Warning:';
				$fatal = false;
				break;
			case E_USER_DEPRECATED:
				$msg .= 'User Deprecated Warning:';
				$fatal = false;
				break;
			default:
				$msg .= "Unknown Error Type ($code):";
				// play safe - fatal				
		}
		$msg .= " $message";
		if ($code) {
			$msg .= " ($code)";
		}
		$msg .= " in $file:$line.";
		$msg .= self::getEnvironmentInfoString();
		// Skip one level of backtracing... i.e. this function
		$trace = self::debug_backtrace_pretty(ini_get('html_errors') ? 'html_string' : 'plain_string', 1);
		$trace_text = self::debug_backtrace_pretty('plain_string', 1);
		$error_reporting_level = error_reporting();
		if (!$error_reporting_level) {
			// override @-suppression of errors - this is a BAD coding style since hides fatal errors
      //  NB in case of an @-suppressed fatal error this code won't be called, 
      //  but at least we warn devs against using @-supression 
			//  to hide warning errors here
			// only complain about this in debug mode (don't fill up error logs on live site)
			if (SITE_DEBUG_MODE && self::warnOnSuppression()) {
				self::hasErred(true);		
				$warning = "Warning: @-suppression of errors (or setting error_reporting() level to 0) is bad coding practice - causes code to die without explanation on fatal error. Use error_reporting(E_ERROR) or similar instead.\n";
				// output to PHP error log as configured by error_log ini setting
				self::_logError($warning . $trace_text, false, false); // dont add extra info, don't display
				if (ini_get('display_errors')) {
					self::displayError($warning . "\n$trace");
				}
			}
			// override with E_ERROR - i.e. report fatal errors
			$error_reporting_level = E_ERROR;
		}
		if ( ($error_reporting_level & $code) || $fatal) {
			self::hasErred(true);		
			// output to PHP error log as configured by error_log ini setting
			self::_logError($msg."\n". $trace_text, false, false); // dont add extra info, don't display
		}
		$msg .="\n$trace";
		if (ini_get('display_errors')) {
			if ( ($error_reporting_level & $code) || $fatal) {
				self::displayError($msg);
			}
		} elseif ($fatal) {
			// output message
			require_once '_layouts/php_error.php';
		}
		if ($fatal) {
			// die script, die!
			die();
		}
		// don't return false to avoid php logging a duplicate of this error itself
		//  - note that as a result this won't populate $php_errormsg / error_get_last() since php 5.2.0
	}

	/**
	 * Write error to the error log (and output it to the browser if display_errors is on)
	 * $msg can be a string or Exception
	 */
	public static function logError($msg, $include_meta = true) {
		return self::_logError($msg, $include_meta, true);
	} 

	/**
	 * This function is protected to avoid confusion when using the public interface logError()
   *  - subsequent params are just for use in special cases within this class 
   *  when we want to control display etc manually
	 */
	protected static function _logError($msg, $include_meta = true, $display = true) {
		$trace = '';
		$trace_text = '';
		if ($msg instanceof Exception) {
			$msg = 'Exception ' . self::getExceptionLogString($msg);
		} elseif ($include_meta) {
			$msg .= self::getEnvironmentInfoString();
			// Skip one level of backtracing... i.e. this function
			$trace = self::debug_backtrace_pretty(ini_get('html_errors') ? 'html_string' : 'plain_string', 1);
			$trace_text = self::debug_backtrace_pretty('plain_string', 1);
		}
		if ($display) {
			self::displayError($msg . $trace);
		}
		$msg = explode("\n", $msg . $trace_text);
		foreach ($msg as $line) {
			error_log($line);
		}
	}
	
	/**
	 * helper function to call logError() with both an exception and some extra info (e.g. original sql query) 
	 */
	public static function logExceptionWithInfo($e, $extra_info) {
		self::_logError($e);
		self::_logError('Extra info: ' . $extra_info, false); // no need to include extra info a second time
	}
	
	public static function displayError($msg) {
		if (ini_get('display_errors')) {
			echo ini_get('error_prepend_string') . (ini_get('html_errors') ? '<p>' : '') . $msg . (ini_get('html_errors') ? '</p>' : '') . ini_get('error_append_string');
		}		
	}

	public static function logGET() {
		if (!count($_GET)) {
			self::_logError('[No GET vars]', false);
			return;
		}
		self::_logError('GET vars:', false);
		foreach ($_POST as $k=>$v) {
			self::_logError('  "'.str_replace('"', '\\"', $k).'" => "'.str_replace('"', '\\"', $_POST[$k]).'"', true);
		}
	}

	public static function logPOST() {
		if (!count($_POST)) {
			self::_logError('[No POST vars]', false);
			return;
		}
		self::_logError('POST vars:', false);
		foreach ($_POST as $k=>$v) {
			self::_logError('  "'.str_replace('"', '\\"', $k).'" => "'.str_replace('"', '\\"', $_POST[$k]).'"', false);
		}
	}
	
	public static function getExceptionLogString($exception) {
		$msg = '';
		if ($exception instanceof Exception) {
			$class_name = get_class($exception);
			$message = $exception->getMessage();
			$code = $exception->getCode();
			$file = $exception->getFile();
			$line = $exception->getLine();
			$trace = $exception->getTraceAsString();
			if ($class_name && $class_name != 'Exception') {
				$msg .= " '$class_name'";
			}
			$msg .= " with message '$message'";
			if ($code) {
				$msg .= " ($code)";
			}
			$msg .= " in $file:$line.";
			$msg .= self::getEnvironmentInfoString();
			$msg .="\nTrace: $trace\n";
		} else {
			trigger_error('shouldnt be here');
		}
		return $msg;
	}		

	/**
	 * Return a string with additional info about what was going on at the time of the error - user and url
	 */
	protected static function getEnvironmentInfoString() {
		$msg = '';
		if (session_id()) {
			$user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
			$loggedin = !empty($_SESSION['user_logged_in']);
		} else {
			$user = null;
			$loggedin = null;
		}
		if ($user) {
			$msg .= "\nUser ID: $user";
			if (!$loggedin) {
				$msg .= " (not logged in)";
			}
		}
		if (!empty($_SERVER['REQUEST_URI']) && !empty($_SERVER['SERVER_NAME'])) {
			$msg .= "\nCurrent URI: http".(isset($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['SERVER_NAME'].'/'.$_SERVER['REQUEST_URI'];
		}
		if (!empty($_SERVER['HTTP_REFERER'])) {
			$msg .= "\nReferrer: ".$_SERVER['HTTP_REFERER'];
		}
		return $msg;
	}

	/*
	 * Keep track of whether an error has occurred
	 *  required because error_get_last() can be reset
	 */
	public static function hasErred($set = false) {
		static $has_erred = false;
		if ($set) {
			$has_erred = true;
		}
		return $has_erred;
	}

	/*
	 * Keep track of whether we want to override warning about (mis)use of @-suppression
	 *  useful for third-party libraries. N.b. not strictly required for the live site
	 */
	public static function warnOnSuppression($set = null) {
		static $warn = true;
		if (!is_null($set)) {
			$warn = $set;
		}
		return $warn;
	}

	/**
	 * Print a function backtrace in multiple formats. Available formats:
	 *   - 'html':    HTML unordered list format
	 *   - 'plain':   plain text output
	 *   - 'comment': HTML/XML comment
	 *   - 'css':     CSS/Javascript comment
	 *   - 'error'/'error_log'/'errorlog': PHP error log
	 *   - 'javascript'/'js'/'firebug': Javascript Firebug output, falling back to 
	 *       document.write() if Firebug is not available
	 *   - 'plain_string': Return trace as a plain-text string
	 *   - 'html_string':  Return trace as an HTML string
	 *
	 * @param string $mode Formatted output mode
	 * @param int    $skip Number of items to ignore from the top of the call stack
	 *
	 * @return void
	 */
	public static function debug_backtrace_pretty($mode='html', $skip=0) {
		$bt=debug_backtrace();
		if ($skip>0) array_splice($bt, 0, $skip);
		switch (strtolower($mode)) {
			case 'plain_string':
				$msg = 'Backtrace';
				if (isset($bt[0]['file'])) {
					$msg .= ' from '.$bt[0]['file'];
					if (isset($bt[0]['line'])) {
						$msg .= ':'.$bt[0]['line'];
					}
				}
				$msg .= ":\n";
				foreach ($bt as $trace) {
					$msg .= '  ';
					if (array_key_exists('class', $trace)) $msg .= $trace['class'];
					if (array_key_exists('type', $trace))  $msg .= $trace['type'];
					$msg .= $trace['function'].'()';
					if (array_key_exists('file', $trace)) {
						$msg .= ' at '.$trace['file'];
						if (array_key_exists('line', $trace)) {
							$msg .= ':'.$trace['line'];
						}
					}
					$msg .= "\n";
				}
				return $msg;
				break;
			case 'plain':
				echo debug_backtrace_pretty('plain_string', 1);
				break;
			case 'comment':
				echo "\n<!-- \n";
				debug_backtrace_pretty('plain', 1);
				echo "-->\n";
				break;
			case 'css':
				echo "\n/*\n";
				debug_backtrace_pretty('plain', 1);
				echo "*/\n";
				break;
			case 'js':
			case 'javascript':
			case 'firebug':
				echo "\n<script type=\"text/javascript\">if (console && console.debug) { fn=console.debug; } else { fn=document.write; };\nfn(\"";
				$s = debug_backtrace_pretty('plain_string', 1);
				echo str_replace(array('"', "\n"), array('\"', '\n'), $s);
				echo "\");</script>\n";
				break;
			case 'err':
			case 'error':
			case 'errorlog':
			case 'error_log':
				error_log(debug_backtrace_pretty('plain_string', 1));
				break;
			case 'html_string':
				$msg = '<b>Backtrace</b>';
				if (isset($bt[0]['file'])) {
					$msg .= ' from <b>'.$bt[0]['file'].'</b>';
					if (isset($bt[0]['line'])) {
						$msg .= ':<b>'.$bt[0]['line'].'</b>';
					}
				}
				$msg .= ":<dl>\n";
				array_shift($bt); # remove this function call from the list
				foreach ($bt as $trace) {
					$msg .= '  <dd><tt>';
					if (array_key_exists('class', $trace)) $msg .= $trace['class'];
					if (array_key_exists('type', $trace))  $msg .= $trace['type'];
					$msg .= $trace['function'].'()</tt>';
					if (array_key_exists('file', $trace)) {
						$msg .= ' at <b>'.$trace['file'].'</b>';
						if (array_key_exists('line', $trace)) {
							$msg .= ':<b>'.$trace['line'].'</b>';
						}
					}
					$msg .= "</dd>\n";
				}
				$msg .= "</dl>\n";
				return $msg;
				break;
			case 'html':
			default:
				echo debug_backtrace_pretty('html_string', 1);
				break;
		}
	}

}

?>
