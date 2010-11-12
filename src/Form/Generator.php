<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */

namespace Jerity\Form;

use \Jerity\Core\Tag;
use \Jerity\Core\URL;
use \Jerity\Util\Arrays;
use \Jerity\Util\String;

/**
 * This class creates valid and accessible HTML forms.
 *
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 *
 * @todo  Separate form structure from HTML output to allow different HTML output methods
 */
class Generator {

  /**
   * List of form fields
   */
  protected $fields = array();

  /**
   * List of errors
   */
  protected $errors = array();

  /**
   * List of pre-filled data
   */
  protected $data = array();

  /**
   * Attributes for the form element
   */
  protected $formProperties = array();

  /**
   * Whether we need a top-level list
   */
  protected $topLevelList = true;

  /**
   * Whether we want the form tags to be generated.  If not, we can generate
   * nested chunks of form elements which can be added with addCustomHTML().
   */
  protected $generateFormTags = true;

  /**
   * Initialise the form generator.
   *
   * @param  bool  $topLevelList  Whether to create the top-level UL. You almost always want this off.
   */
  public function __construct($topLevelList = false, $generateFormTags = true) {
    $this->topLevelList = $topLevelList;
    $this->generateFormTags = $generateFormTags;
  }

  /**
   *
   */
  public function setAttribute($name, $value) {
    $this->formProperties[$name] = $value;
  }

  /**
   *
   */
  public function getAttribute($name) {
    return isset($this->formProperties[$name]) ? $this->formProperties[$name] : null;
  }

  /**
   *
   */
  public function hasAttribute($name) {
    return isset($this->formProperties[$name]);
  }

  /**
   *
   */
  public function delAttribute($name) {
    unset($this->formProperties[$name]);
  }

  /**
   *
   */
  protected function &addElement($name, $label, $type, array $extra = null) {
    $newObj = new Generator_Element($name, $label, $type, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addHidden($name, $value, array $extra = null) {
    $extra = array_merge(array('value' => $value), $extra ? $extra : array());
    return $this->addElement($name, null, 'hidden', $extra);
  }

  /**
   *
   */
  public function addInput($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'text', $extra);
  }

  /**
   *
   */
  public function addPassword($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'password', $extra);
  }

  /**
   *
   */
  public function addCheckbox($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'checkbox', $extra);
  }

  /**
   *
   */
  public function addRadio($name, $label, $value, array $extra = null) {
    return $this->addElement($name, $label, 'radio', array('value' => $value) + ($extra ? $extra : array()));
  }

  /**
   *
   */
  public function addSubmit($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'submit', $extra);
  }

  /**
   *
   */
  public function addReset($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'reset', $extra);
  }

  /**
   *
   */
  public function addFieldset($label, array $extra = null) {
    $newObj = new Generator_Fieldset($label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addSelect($name, $label, $options, array $extra = null) {
    $newObj = new Generator_Select($name, $label, $options, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addTextarea($name, $label, array $extra = null) {
    $newObj = new Generator_Textarea($name, $label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addHint($content, array $extra = null) {
    $newObj = new Generator_Hint($content, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addCustomHTML($content, $label = null, array $extra = null) {
    $newObj = new Generator_CustomHTML($content, $label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function getError($name) {
    if (!isset($this->errors[$name])) return null;
    return $this->errors[$name];
  }

  /**
   *
   */
  public function clearError($name) {
    unset($this->errors[$name]);
  }

  /**
   *
   */
  public function setError($name, $msg) {
    $this->errors[$name] = $msg;
  }

  /**
   *
   */
  public function setErrors(array $errors, $replace = false) {
    if ($replace) {
      $this->errors = $errors;
    } else { // merge and overwrite
      $this->errors = $errors + $this->errors;
    }
  }

  /**
   *
   */
  public function hasErrors() {
    return count($this->errors);
  }

  /**
   *
   */
  public function clearData() {
    $this->data = array();
  }

  /**
   *
   */
  public function populateData(array $data, $replace = true) {
    // $data should be a single-dimension array
    $data = Arrays::collapseKeys($data);
    if ($replace) {
      $this->data = $data;
    } else { // merge and overwrite
      $this->data = $data + $this->data; # array addition is NOT commutative
    }
  }

  /**
   *
   */
  public function populateFromGet($replace = true) {
    $this->populateData($_GET, $replace);
  }

  /**
   *
   */
  public function populateFromPost($replace = true) {
    $this->populateData($_POST, $replace);
  }

  /**
   *
   */
  public function populateFromRequest($replace = true) {
    $this->populateData($_REQUEST, $replace);
  }

  /**
   *
   */
  public function renderElementList($elements) {
    if (!count($elements)) {
      return '';
    }
    $out = ($this->topLevelList) ? "<ul>\n" : '';
    foreach ($elements as $e) {
      if ($this->topLevelList && $e['type'] !== 'hidden') $out .= "<li>\n";
      if (isset($e['name']) && $e['name']) {
        if (isset($this->data[$e['name']])) {
          $e->populate($this->data[$e['name']]);
        }
        $out .= $e->render($this->getError($e['name']));
      } else {
        if ($e instanceof Generator_Fieldset) {
          $e->populate($this->data);
          $e->propagateErrors($this->errors);
        }
        $out .= $e->render();
      }
      if ($this->topLevelList && $e['type'] !== 'hidden') $out .= "</li>\n";
    }
    if ($this->topLevelList) $out .= "</ul>\n";
    return $out;
  }

  /**
   * Render the form.
   *
   * @param  string  $action  URL to submit to, defaults to self.
   * @param  string  $method  HTTP method to use: POST or GET.
   *
   * @return  string
   */
  public function render($action = null, $method = null) {
    $out = '';
    if ($this->generateFormTags) {
      $props = $this->formProperties;
      if (!is_null($action))            $props['action'] = $action;
      elseif (!isset($props['action'])) $props['action'] = URL::getCurrent();
      if (!is_null($method))            $props['method'] = strtolower($method);
      elseif (!isset($props['method'])) $props['method'] = 'post';
      $out .= '<form';
      foreach ($props as $k => $v) {
        $out .= ' '.$k.'="'.String::escape($v, true).'"';
      }
      $out .= ">\n";
    }
    // render elements
    $out .= $this->renderElementList($this->fields);
    if ($this->generateFormTags) $out .= "</form>\n";
    return $out;
  }

}

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class Generator_Element extends \ArrayObject {

  /**
   * Unique ID number counter
   */
  protected static $uniqueCounter = 0;

  /**
   *
   */
  protected $props = array();

  /**
   *
   */
  protected $data = null;

  /**
   *
   */
  protected $dataOnce = false;

  /**
   *
   */
  protected $errors = array();

  /**
   *
   */
  public function __construct($name, $label, $type, array $extra = null) {
    $this->props = array('type' => $type);
    if (!is_null($name)) {
      $this->props['name'] = $name;
    }
    if (!is_null($label)) {
      if ($type == 'submit' || $type == 'reset') {
        $this->props['value'] = $label;
      } else {
        $this->props['label'] = $label;
      }
    }
    if (!is_null($extra)) {
      $this->props = $this->props + $extra;
    }
    if (!isset($this->props['id'])) {
      $this->props['id'] = 'form-el'.(++self::$uniqueCounter).($name ? ('-'.$name) : '');
    }
    if (!isset($this->props['class'])) {
      $this->props['class'] = $this->props['type'];
    } elseif (!preg_match('/(?:^| )'.preg_quote($this->props['type']).'(?:$| )/', $this->props['class'])) {
      $this->props['class'] .= ' '.$this->props['type'];
    }
    ksort($this->props);
  }

  /**
   *
   */
  public function offsetExists($k) {
    return isset($this->props[$k]);
  }

  /**
   *
   */
  public function offsetGet($k) {
    return $this->props[$k];
  }

  /**
   *
   */
  public function offsetUnset($k) {
    unset($this->props[$k]);
  }

  /**
   *
   */
  public function getIterator() {
    return new \ArrayIterator($this->props);
  }

  /**
   *
   */
  public function getError($name) {
    if (!isset($this->errors[$name])) {
      return null;
    } else {
      return $this->errors[$name];
    }
  }

  /**
   * Pre-populate this control with data
   */
  public function populate($data) {
    $this->dataOnce = false;
    $this->data = $data;
  }

  /**
   * Pre-populate this control with data, but only for the next render
   */
  public function populateOnce($data) {
    $val = $this->populate($data);
    $this->dataOnce = true;
    return $val;
  }

  /**
   * Retrieves errors passed down by parent object/container.
   */
  public function propagateErrors($errors) {
    $this->errors = $errors;
  }

  /**
   * Renders an error string.
   *
   * @param  array   $attrs  Associative array of attributes for the errored element.
   * @param  string  $error  The error string to display for the errored element.
   */
  protected static function renderError(array $attrs = null, $error = null) {
    $attrs['id'] .= '-error';
    $attrs = array_intersect_key($attrs, array_flip(array('id')));
    return Tag::renderTag('strong', $attrs, $error)."\n";
  }

  /**
   * Render a form element.
   *
   * @param  string  $error  An error message to show, if applicable.
   *
   * @return  string
   */
  public function render($error = null) {
    $out = '';
    # add label, and remove from properties array
    if (isset($this['label']) && !in_array($this['type'], array('hidden', 'checkbox', 'radio'))) {
      $labelcontent = String::escape($this['label']).':';
      if (isset($this['required']) && $this['required']) {
        $labelcontent .= ' <em>Required</em>';
      }
      $out .= Tag::renderTag('label', array('for' => $this['id']), $labelcontent)."\n";
    }
    if (!is_null($this->data)) {
      switch ($this['type']) {
        case 'text':
        case 'hidden':
          $this->props['value'] = $this->data;
          break;
        case 'checkbox':
        case 'radio':
          if (!is_null($this->data) && (!isset($this['value']) || $this->data == $this['value'])) {
            $this->props['checked'] = true;
          }
          break;
      }
      if ($this->dataOnce) {
        $this->dataOnce = false;
        $this->data = null;
      }
    }
    if ($error && $this['type'] !== 'hidden') {
      $out .= self::renderError($this->props, $error);
      if (!isset($this->props['class'])) {
        $this->props['class'] = 'haserror';
      } elseif (!preg_match('/(?:^| )haserror(?:$| )/', $this->props['class'])) {
        $this->props['class'] .= ' haserror';
      }
    }
    $attrs = array_diff_key($this->props, array_flip(array('label', 'required')));
    $out .= Tag::renderTag('input', $attrs)."\n";
    if (isset($this['label']) && in_array($this['type'], array('checkbox', 'radio'))) {
      $out .= Tag::renderTag('label', array('for' => $this['id']), String::escape($this['label']))."\n";
    }
    return $out;
  }

}

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class Generator_Fieldset extends Generator_Element {

  /**
   *
   */
  protected $fields = array();

  /**
   *
   */
  protected static $fsUniqueCounter = 0;

  /**
   *
   */
  public function __construct($label, array $extra = null) {
    $this->props = array(
      'label' => $label,
      'type'  => 'fieldset',
    );
    if (!is_null($extra)) {
      $this->props = $this->props + $extra;
    }
    if (!isset($this->props['id'])) {
      $this->props['id'] = 'form-fs'.(++self::$fsUniqueCounter);
    }
    ksort($this->props);
  }

  /**
   *
   */
  protected function &addElement($name, $label, $type, array $extra = null) {
    $newObj = new Generator_Element($name, $label, $type, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addInput($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'text', $extra);
  }

  /**
   *
   */
  public function addPassword($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'password', $extra);
  }

  /**
   *
   */
  public function addCheckbox($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'checkbox', $extra);
  }

  /**
   *
   */
  public function addRadio($name, $label, $value, array $extra = null) {
    return $this->addElement($name, $label, 'radio', array('value' => $value) + ($extra ? $extra : array()));
  }

  /**
   *
   */
  public function addSubmit($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'submit', $extra);
  }

  /**
   *
   */
  public function addReset($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'reset', $extra);
  }

  /**
   *
   */
  public function addFieldset($label, array $extra = null) {
    $newObj = new Generator_Fieldset($label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addSelect($name, $label, $options, array $extra = null) {
    $newObj = new Generator_Select($name, $label, $options, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addTextarea($name, $label, array $extra = null) {
    $newObj = new Generator_Textarea($name, $label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addHint($content, array $extra = null) {
    $newObj = new Generator_Hint($content, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function addCustomHTML($content, $label = null, array $extra = null) {
    $newObj = new Generator_CustomHTML($content, $label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  /**
   *
   */
  public function renderElementList($elements) {
    if (!count($elements)) {
      return '';
    }
    $out = "<ul>\n";
    foreach ($elements as $e) {
      if ($e['type'] !== 'hidden') $out .= "<li>\n";
      if (isset($e['name']) && $e['name']) {
        if (isset($this->data[$e['name']])) {
          $e->populate($this->data[$e['name']]);
        }
        $out .= $e->render($this->getError($e['name']));
      } else {
        if ($e instanceof Generator_Fieldset) {
          $e->populate($this->data);
          $e->propagateErrors($this->errors);
        }
        $out .= $e->render();
      }
      if ($e['type'] !== 'hidden') $out .= "</li>\n";
    }
    $out .= "</ul>\n";
    return $out;
  }

  /**
   *
   */
  public function render($error = null) {
    $attrs = array_diff_key($this->props, array_flip(array('label', 'type')));
    $out = Tag::renderTag('fieldset', $attrs)."\n";
    if (isset($this['label']) && $this['label']) {
      # TODO: required flag
      $out .= '<legend><span>'.String::escape($this['label'])."</span></legend>\n";
    }
    // render elements
    $out .= $this->renderElementList($this->fields);
    $out .= "</fieldset>\n";
    return $out;
  }

}

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class Generator_Textarea extends Generator_Element {

  /**
   *
   */
  public function __construct($name, $label, array $extra = null) {
    parent::__construct($name, $label, 'textarea', $extra);
  }

  /**
   * Render a form element.
   *
   * @param string $error An error message to show, if applicable.
   * @return string
   */
  public function render($error = null) {
    $out = '';
    # add label, and remove from properties array
    if (isset($this['label'])) {
      $labelcontent = String::escape($this['label']).':';
      if (isset($this['required']) && $this['required']) {
        $labelcontent .= ' <em>Required</em>';
      }
      $out .= Tag::renderTag('label', array('for' => $this['id']), $labelcontent)."\n";
    }
    if (!is_null($this->data)) {
      $data = $this->data;
    } elseif (isset($this['value'])) {
      $data = $this['value'];
    } else {
      $data = '';
    }
    if ($error) {
      $out .= self::renderError($this->props, $error);
      if (!isset($this->props['class'])) {
        $this->props['class'] = 'haserror';
      } elseif (!preg_match('/(?:^| )haserror(?:$| )/', $this->props['class'])) {
        $this->props['class'] .= ' haserror';
      }
    }
    $attrs = array_diff_key($this->props, array_flip(array('label', 'value', 'type')));
    $out .= Tag::renderTag('textarea', $attrs, String::escape($data))."\n";
    return $out;
  }

}

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class Generator_Select extends Generator_Element {

  /**
   *
   */
  const OPTIONS_KEY_VALUE = 0;

  /**
   *
   */
  const OPTIONS_VALUE_ONLY = 1;

  /**
   *
   */
  const OPTIONS_FULL = 2;

  /**
   *
   */
  protected $options = array();

  /**
   *
   */
  public function __construct($name, $label, array $options = null, array $extra = null) {
    parent::__construct($name, $label, 'select', $extra);
    if (!is_null($options)) {
      $this->setOptions($options, self::OPTIONS_KEY_VALUE);
    }
  }

  /**
   *
   */
  public function setOptions(array $options, $arraytype = self::OPTIONS_KEY_VALUE) {
    switch ($arraytype) {
      case self::OPTIONS_FULL:
        $this->options = $options;
        return;
      case self::OPTIONS_KEY_VALUE:
        foreach ($options as $k => $v) {
          $this->options[$k] = array('value' => $k, 'label' => $v);
        }
        break;
      case self::OPTIONS_VALUE_ONLY:
        $this->options = array_combine($options, array_map($val_creator, $options));
        foreach ($this->options as $k => $v) {
          $this->options[$k] = array('value' => $k, 'label' => $v);
        }
        break;
      default:
        throw new \InvalidArgumentException('Invalid argument types');
    }
  }

  /**
   * Render a form element.
   *
   * @param  string  $error  An error message to show, if applicable.
   *
   * @return  string
   */
  public function render($error = null) {
    $out = '';
    # Add label, and remove from properties array
    if (isset($this['label'])) {
      $labelcontent = String::escape($this['label']).':';
      if (isset($this['required']) && $this['required']) {
        $labelcontent .= ' <em>Required</em>';
      }
      $out .= Tag::renderTag('label', array('for' => $this['id']), $labelcontent)."\n";
    }
    if ($error) {
      $out .= self::renderError($this->props, $error);
      if (!isset($this->props['class'])) {
        $this->props['class'] = 'haserror';
      } elseif (!preg_match('/(?:^| )haserror(?:$| )/', $this->props['class'])) {
        $this->props['class'] .= ' haserror';
      }
    }
    $attrs = array_diff_key($this->props, array_flip(array('label', 'value', 'type')));
    $out .= Tag::renderTag('select', $attrs)."\n";
    if (!is_null($this->data)) {
      if (isset($this->options[$this->data])) {
        $this->options[$this->data]['selected'] = 'selected';
      }
    } elseif (isset($this['value'])) {
      if (isset($this->options[$this['value']])) {
        $this->options[$this['value']]['selected'] = 'selected';
      }
    }
    foreach ($this->options as $option) {
      $attrs = array_diff_key($option, array_flip(array('label')));
      $out .= Tag::renderTag('option', $attrs, String::escape($option['label']))."\n";
    }
    $out .= "</select>\n";
    return $out;
  }

}

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class Generator_Hint extends Generator_Element {

  /**
   *
   */
  protected static $hintUniqueCounter = 0;

  /**
   *
   */
  public function __construct($content, array $extra = null) {
    $this->props = array(
      'content' => $content,
      'type'    => 'hint',
    );
    if (!is_null($extra)) {
      $this->props = $this->props + $extra;
    }
    if (!isset($this->props['id'])) {
      $this->props['id'] = 'form-hint'.(++self::$hintUniqueCounter);
    }
    if (!isset($this->props['class'])) {
      $this->props['class'] = $this->props['type'];
    } elseif (!preg_match('/(?:^| )'.preg_quote($this->props['type']).'(?:$| )/', $this->props['class'])) {
      $this->props['class'] .= ' '.$this->props['type'];
    }
    ksort($this->props);
  }

  /**
   * Render a form hint.
   *
   * @param  string  $error  An error message to show, if applicable.
   *
   * @return  string
   */
  public function render($error = null) {
    $out = '';
    if (isset($this['content'])) {
      if (!isset($this['escape']) || $this['escape']) {
        $content = String::escape($this['content']);
      } else {
        $content = $this['content'];
      }
    } else {
      $content = '';
    }
    $attrs = array_diff_key($this->props, array_flip(array('content', 'escape', 'type')));
    $out .= Tag::renderTag('div', $attrs, $content)."\n";
    return $out;
  }

}

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class Generator_CustomHTML extends Generator_Element {

  /**
   *
   */
  protected static $customUniqueCounter = 0;

  /**
   *
   */
  public function __construct($content, $label = null, array $extra = null) {
    $this->props = array(
      'content' => $content,
      'type'    => 'custom',
    );
    if (!is_null($label)) {
      $this->props['label'] = $label;
    }
    if (!is_null($extra)) {
      $this->props = $this->props + $extra;
    }
    if (!isset($this->props['id'])) {
      $this->props['id'] = 'form-custom'.(++self::$customUniqueCounter);
    }
    if (!isset($this->props['class'])) {
      $this->props['class'] = $this->props['type'];
    } elseif (!preg_match('/(?:^| )'.preg_quote($this->props['type']).'(?:$| )/', $this->props['class'])) {
      $this->props['class'] .= ' '.$this->props['type'];
    }
    ksort($this->props);
  }

  /**
   * Render a custom block of HTML.
   *
   * @param  string  $error  An error message to show, if applicable.
   *
   * @return  string
   */
  public function render($error = null) {
    $out = '';
    if (isset($this['label'])) {
      $labelcontent = String::escape($this['label']).':';
      if (isset($this['required']) && $this['required']) {
        $labelcontent .= ' <em>Required</em>';
      }
      $out .= Tag::renderTag('label', array('for' => $this['id']), $labelcontent)."\n";
    }
    if (isset($this['content'])) {
      $content = $this['content'];
    } else {
      $content = '';
    }
    if ($error) {
      $out .= self::renderError($this->props, $error);
      if (!isset($this->props['class'])) {
        $this->props['class'] = 'haserror';
      } elseif (!preg_match('/(?:^| )haserror(?:$| )/', $this->props['class'])) {
        $this->props['class'] .= ' haserror';
      }
    }
    $attrs = array_diff_key($this->props, array_flip(array('content', 'escape', 'name', 'type')));
    $out .= Tag::renderTag('div', $attrs, $content)."\n";
    return $out;
  }

}

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class GeneratorElementException extends Exception {

  /**
   *
   */
  public function __construct($message, Generator_Element $element) {
    $this->message = $message;
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
