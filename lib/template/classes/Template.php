<?php
/**
 * @package    JerityTemplate
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * An abstract template class providing default simple handling for template
 * variables.
 *
 * @todo  Support multiple paths for template lookup.
 * @todo  Output methods for escaping variables where necessary.
 *
 * @package    JerityTemplate
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
abstract class Template implements Renderable {

  ##############################################################################
  # global template options {{{

  /**
   * The base template search path under which all templates reside.
   *
   * @var  string
   */
  protected static $base_path = null;

  # }}} global template options
  ##############################################################################

  ##############################################################################
  # default rendering methods {{{

  /**
   * The path to the template source file.
   *
   * @var  string
   */
  protected $template;

  # }}} default rendering methods
  ##############################################################################

  ##############################################################################
  # template rendering options {{{

  /**
   * The prefix to use when extracting variables for a template.
   *
   * @var  string
   */
  protected $variable_prefix = null;

  /**
   * Stores the post render hook functions and an associated priority.
   *
   * @todo  Use PHP 5.3 SplPriorityQueue.
   *
   * @var  array
   */
  protected $post_render_hooks = array();

  # }}} template rendering options
  ##############################################################################

  ##############################################################################
  # template variable management {{{

  /**
   * @var  array
   */
  protected $variables = array();

  # }}} template variable management
  ##############################################################################

  /**
   * Initialises the template.
   *
   * @param  string  $t  The template to use.
   */
  public function __construct($t) {
    $count = preg_match('!(?:(?:^|\.{2,})/)+!', $t);
    if ($count) throw new InvalidArgumentException('Warning: Attempted misuse of template system.');
    $t = self::getPath().$t.'.tpl.php';
    if (!file_exists($t) || (!is_file($t) && !is_link($t)) || !is_readable($t)) {
      throw new RuntimeException('Could not find template: \''.$t.'\'');
    }
    $this->template = $t;
  }

  ##############################################################################
  # global template options {{{

  /**
   * Returns the base template search path.
   *
   * @return  string
   */
  public static function getPath() {
    if (!self::$base_path) {
      throw new UnexpectedValueException('Template directory has not been set.');
    }
    return self::$base_path;
  }

  /**
   * Sets the base template search path used for looking up templates.
   *
   * @param  string  $path  The path to use for searching.
   */
  public static function setPath($path) {
    if (!file_exists($path) || !is_dir($path) || !is_readable($path)) {
      throw new InvalidArgumentException('Template directory could not be read.');
    }
    self::$base_path = preg_replace('#/+#', '/', rtrim($path, '/')).'/';
  }

  # }}} global template options
  ##############################################################################

  ##############################################################################
  # default rendering methods {{{

  /**
   * Overrides the default object to string conversion to force the Renderable
   * item to be rendered in string context.
   *
   * @return  string
   */
  public function __toString() {
    return $this->render();
  }

  /**
   * Render the item using the current global rendering context, and return it
   * as a string.
   *
   * @return  string
   *
   * @throws  RuntimeException
   */
  public function render() {
    # Check whether the template path is valid.
    if (!file_exists($this->template) || (!is_file($this->template) && !is_link($this->template)) || !is_readable($this->template)) {
      throw new RuntimeException('Could not find template: \''.$this->template.'\'');
    }

    # Extract template variables in preparation for template inclusion.
    if ($this->variables) {
      $type = EXTR_REFS;
      $prefix = $this->getVariablePrefix();
      if (!is_null($prefix)) $type |= EXTR_PREFIX_ALL;
      extract($this->variables, $type, $prefix);
    }

    # Create a buffer to hold rendered content and pass in post render hooks if
    # required.
    if ($this->post_render_hooks) {
      ob_start(array($this, 'executePostRenderHooks'));
    } else {
      ob_start();
    }

    # Pull in and execute the template code.
    include($this->template);

    # Return the rendered template.
    return ob_get_clean();
  }

  # }}} default rendering methods
  ##############################################################################

  ##############################################################################
  # template rendering options {{{

  /**
   * Gets the prefix to use when extracting the template variables array when
   * rendering the template.
   *
   * @return  string
   */
  public function getVariablePrefix() {
    return $this->variable_prefix;
  }

  /**
   * Sets the prefix to use when extracting the template variables array when
   * rendering the template.
   *
   * @param  string  $prefix  The prefix to set.
   *
   * @throws  InvalidArgumentException
   */
  public function setVariablePrefix($prefix) {
    if (is_string($prefix) || is_null($prefix)) {
      $this->variable_prefix = ($prefix === '' ? null : $prefix);
    } else {
      throw new InvalidArgumentException('Variable prefix must be a string.');
    }
  }

  /**
   * Adds a post render hook to be executed after content is generated but
   * prior to display.
   *
   * Post render hooks should be functions that can be passed as the callback
   * to ob_start().  This implies that they should take in the contents of the
   * buffer as a string in the first parameter and return a string of the
   * modified contents.
   *
   * @see  ob_start()
   *
   * @param  callback  $callback  The function to execute.
   * @param  integer   $priority  The priority of the function [0-99]
   *
   * @throws  Exception
   * @throws  OutOfRangeException
   */
  public function addPostRenderHook($callback, $priority = 50) {
    if ($priority < 0 || $priority > 99) {
      throw new OutOfRangeException('Post render hook priority must be in the range [0-99]');
    }
    if (!is_callable($callback)) {
      throw new Exception('Attempted to register invalid post render hook - not callable');
    }
    $hash = $this->generateCallbackHash($callback);
    $this->post_render_hooks[$hash] = array(
      'priority' => $priority,
      'callback' => $callback,
    );
  }

  /**
   * Removes a post render hook.
   *
   * @param  callback  $callback  The hook to remove.
   *
   * @throws  Exception
   */
  public function removePostRenderHook($callback) {
    if (!is_callable($callback)) {
      throw new Exception('Attempted to deregister invalid post render hook - not callable');
    }
    $hash = $this->generateCallbackHash($callback);
    unset($this->post_render_hooks[$hash]);
  }

  /**
   * Returns a copy of the array of post render hooks.
   *
   * @return  array
   */
  public function getPostRenderHooks() {
    return $this->post_render_hooks;
  }

  /**
   * Clears all current post render hooks associated with this template.
   */
  public function clearPostRenderHooks() {
    $this->post_render_hooks = array();
  }

  /**
   * Executes the post render hooks.  The callback functions are daisy-chained
   * together passing each modified buffer through.
   *
   * @see  ob_start()
   *
   * @param  string  $contents  The contents of the output buffer to modify.
   * @param  int     $status    The status of the output buffer.
   *
   * @return  string  The modified buffer contents.
   *
   * @throws  Exception
   */
  protected function executePostRenderHooks($contents, $status) {
    uasort($this->post_render_hooks, create_function('$a,$b', 'return strcmp($a[\'priority\'], $b[\'priority\']);'));
    foreach ($this->post_render_hooks as $hash => $hook) {
      if (!is_callable($hook['callback'])) {
        throw new Exception('Invalid post render hook - not callable');
      }
      $contents = call_user_func($hook['callback'], $contents, $status);
    }
    return $contents;
  }

  /**
   * Return the hash of a callback.
   *
   * @param  callback  $callback  The callback to hash.
   *
   * @return  string
   */
  protected function generateCallbackHash($callback) {
    if (is_array($callback)) {
      if (is_object($callback[0])) {
        $callback[0] = spl_object_hash($callback[0]);
      }
      return implode('#', $callback);
    } else {
      # Assume we have a string.  We should have checked using is_callable().
      return $callback;
    }
  }

  # }}} template rendering options
  ##############################################################################

  ##############################################################################
  # template variable management {{{

  /**
   * Gets the value of a variable that has been assigned to the template so
   * far. If the variable does not exist, <kbd>null</kbd> will be returned.
   *
   * @param  string  $key  The variable name to fetch, or null to fetch all as an array.
   *
   * @return  mixed
   */
  public function get($key = null) {
    if (is_null($key)) {
      return $this->variables;
    } else {
      if (isset($this->variables[$key])) {
        return $this->variables[$key];
      } else {
        return null;
      }
    }
  }

  /**
   * Adds one or more variables to the template variable store.
   * You may add variables in the following ways:
   *   $c->set('key', 'val');
   *   $c->set(
   *     array('key0', 'key1'),
   *     array('val0', 'val1')
   *   );
   *   $c->set(array(
   *     'key0' => 'val0',
   *     'key1' => 'val1',
   *   ));
   *
   * @param   mixed  $key    The variable name.
   * @param   mixed  $value  The variable content.
   *
   * @throws  InvalidArgumentException
   * @throws  LengthException
   */
  public function set($key, $value = null) {
    if (is_array($key)) {
      if (is_array($value)) {
        if (!$key || !$value || count($key) !== count($value)) {
          throw new LengthException('Number of keys does not match number of values.');
        }
        # TODO: Check array keys?  Or don't use extract()?
        $this->variables = array_merge($this->variables, array_combine($key, $value));
      } elseif (is_null($value)) {
        # TODO: Check array keys?  Or don't use extract()?
        $this->variables = array_merge($this->variables, $key);
      } else {
        throw new InvalidArgumentException('Multiple keys require multiple values.');
      }
    } elseif (is_string($key)) {
      # TODO: Check array keys?  Or don't use extract()?
      $this->variables[$key] = $value;
    } else {
      throw new InvalidArgumentException('Could not clear a key from the template variables.');
    }
  }

  /**
   * Clears one, more or all of the variables that have been assigned to the
   * template so far.
   *
   * @param  mixed  $key  The variable(s) to clear; all if null.
   *
   * @throws  InvalidArgumentException
   */
  public function clear($key = null) {
    if (is_null($key) || $key === '') {
      $this->variables = array();
    } elseif (is_array($key)) {
      foreach ($key as $k) unset($this->variables[$k]);
    } elseif (is_string($key)) {
      unset($this->variables[$key]);
    } else {
      throw new InvalidArgumentException('Could not clear a key from the template variables.');
    }
  }

  # }}} template variable management
  ##############################################################################

}
