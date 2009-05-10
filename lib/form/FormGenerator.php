<?php

/**
 * This class creates valid and accessible HTML forms.
 */
class FormGenerator {
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
   * Whether to generate XHTML or just HTML
   */
  protected $xhtml = false;
  /**
   * Whether we need a top-level list
   */
  protected $topLevelList = true;


  /**
   * Initialise the form generator.
   *
   * @param bool $xhtml Whether to generate XHTML or just HTML.
   */
  public function __construct($xhtml = false, $topLevelList=false) {
    $this->xhtml = $xhtml;
    $this->topLevelList = $topLevelList;
  }

  public function setAttribute($name, $value) {
    $this->formProperties[$name] = $value;
  }

  public function getAttribute($name) {
    return isset($this->formProperties[$name]) ? $this->formProperties[$name] : null;
  }

  public function hasAttribute($name) {
    return isset($this->formProperties[$name]);
  }

  public function delAttribute($name) {
    unset($this->formProperties[$name]);
  }

  protected function &addElement($name, $label, $type, array $extra = null) {
    $newObj = new FormGenerator_Element($name, $label, $type, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function addInput($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'text', $extra);
  }

  public function addPassword($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'password', $extra);
  }

  public function addCheckbox($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'checkbox', $extra);
  }

  public function addRadio($name, $label, $value, array $extra = null) {
    return $this->addElement($name, $label, 'radio', array('value'=>$value) + ($extra?$extra:array()));
  }

  public function addSubmit($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'submit', $extra);
  }

  public function addReset($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'reset', $extra);
  }

  public function addFieldset($label, array $extra = null) {
    $newObj = new FormGenerator_Fieldset($label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function addSelect($name, $label, $options, array $extra = null) {
    $newObj = new FormGenerator_Select($name, $label, $options, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function addTextarea($name, $label, array $extra = null) {
    $newObj = new FormGenerator_Textarea($name, $label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function addHint($content, array $extra = null) {
    $newObj = new FormGenerator_Hint($content, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function getError($name) {
    if (!isset($this->errors[$name])) {
      return null;
    } else {
      return $this->errors[$name];
    }
  }

  public function clearError($name) {
    unset($this->errors[$name]);
  }

  public function setError($name, $msg) {
    $this->errors[$name] = $msg;
  }

  public function hasErrors() {
    return count($this->errors);
  }

  public function clearData() {
    $this->data = array();
  }

  public function populateData(array $data, $replace = true) {
    if ($replace) {
      $this->data = $data;
    } else { // merge and overwrite
      $this->data = $data + $this->data; # array addition is NOT commutative
    }
  }

  public function populateFromGet($replace = true) {
    $this->populateData($_GET, $replace);
  }

  public function populateFromPost($replace = true) {
    $this->populateData($_POST, $replace);
  }

  public function populateFromRequest($replace = true) {
    $this->populateData($_REQUEST, $replace);
  }

  public function renderElementList($elements) {
    if (!count($elements)) {
      return '';
    }
    $out = ($this->topLevelList) ? "<ul>\n" : '';
    foreach ($elements as $e) {
      if ($this->topLevelList) $out .= "<li>\n";
      if (isset($e['name']) && $e['name']) {
        if (isset($this->data[$e['name']])) {
          $e->populate($this->data[$e['name']]);
        }
        $out .= $e->render($this->xhtml, $this->getError($e['name']));
      } else {
        if ($e instanceof FormGenerator_Fieldset) {
          $e->populate($this->data);
          $e->propagateErrors($this->errors);
        }
        $out .= $e->render($this->xhtml);
      }
      if ($this->topLevelList) $out .= "</li>\n";
    }
    if ($this->topLevelList) $out .= "</ul>\n";

    return $out;
  }

  /**
   * Render the form.
   *
   * @param string $action URL to submit to, defaults to self.
   * @param string $method HTTP method to use: POST or GET.
   * @return string
   */
  public function render($action=null, $method=null) {
    $props = $this->formProperties;
    if (!is_null($action))            $props['action'] = $action;
    elseif (!isset($props['action'])) $props['action'] = $_SERVER['REQUEST_URI'];
    if (!is_null($method))            $props['method'] = strtoupper($method);
    elseif (!isset($props['method'])) $props['method'] = 'POST';
    $out = '<form';
    foreach ($props as $k=>$v) {
      $out .= ' '.$k.'="'.htmlentities($v).'"';
    }
    $out .= ">\n";

    // render elements
    $out .= $this->renderElementList($this->fields);

    $out .= "</form>\n";

    return $out;
  }
}


class FormGenerator_Element extends ArrayObject {
  /**
   * Unique ID number counter
   */
  protected static $uniqueCounter = 0;
  protected $props = array();
  protected $data = null;
  protected $dataOnce = false;
  protected $errors = array();

  public function __construct($name, $label, $type, array $extra = null) {
    $this->props = array(
      'type'  => $type,
    );
    if (!is_null($name)) {
      $this->props['name'] = $name;
    }
    if (!is_null($label)) {
      if ($type=='submit' || $type=='reset') {
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

  public function offsetExists($k) {
    return isset($this->props[$k]);
  }

  public function offsetGet($k) {
    return $this->props[$k];
  }

  public function offsetUnset($k) {
    unset($this->props[$k]);
  }

  public function getIterator() {
    return new ArrayIterator($this->props);
  }

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
   * @param array  $attrs Associative array of attributes for the errored element.
   * @param string $error The error string to display for the errored element.
   * @param bool   $xhtml Whether to generate XHTML or HTML
   */
  protected static function renderError(array $attrs = null, $error, $xhtml) {
    $attrs['id'] .= '-error';
    $attrs['class'] = 'error';
    $attrs = array_intersect_key($attrs, array_flip(array('id', 'class')));
    return self::renderTag('div', $attrs, $error, $xhtml)."\n";
  }

  /**
   * Render an HTML tag.
   *
   * Note that \a $content is \b not escaped, but is output verbatim.
   *
   * @param string $tag         The name of the tag to render
   * @param array  $attrs       Associative array of attributes for the tag
   * @param mixed  $content     Tag content; false to force an empty tag in XHTML mode (single tag in HTML mode), null to force open tag.
   * @param bool   $xhtml       Whether to generate XHTML or HTML
   * @param array  $ignoreAttrs Array of keys to ignore from \a $attrs
   */
  protected static function renderTag($tag, array $attrs = null, $content = null, $xhtml = false, array $ignoreAttrs = null) {
    if (is_null($attrs)) {
      $attrs = array();
    }
    if (!is_null($ignoreAttrs) && count($ignoreAttrs)) {
      $attrs = array_diff_key($attrs, array_flip($ignoreAttrs));
    }

    $out = '<'.$tag;
    foreach ($attrs as $k=>$v) {
      if ($v === false) {
        continue;
      }
      if ($v===true) {
        $v = $k;
      }
      $out .= ' '.htmlentities($k).'="'.htmlentities($v).'"';
    }
    $out .= ($xhtml && $content===false) ? " />" : ">";
    if (!is_null($content) && $content !== false) {
      $out .= $content;
      $out .= '</'.$tag.'>';
    }

    return $out;
  }

  /**
   * Render a form element.
   *
   * @param bool   $xhtml Whether to generate XHTML or HTML.
   * @param string $error An error message to show, if applicable.
   * @return string
   */
  public function render($xhtml=false, $error=null) {
    $out = '';
    # add label, and remove from properties array
    if (isset($this['label']) && !in_array($this['type'], array('checkbox', 'radio'))) {
      $labelcontent = htmlentities($this['label']).':';
      if (isset($this['required']) && $this['required']) {
        $labelcontent .= ' <em>Required</em>';
      }
      $out .= self::renderTag('label', array('for'=>$this['id']), $labelcontent)."\n";
    }

    if (!is_null($this->data)) {
      switch ($this['type']) {
        case 'text':
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
        $this->dataOnce=false;
        $this->data = null;
      }
    }

    if ($error) {
      $out .= self::renderError($this->props, $error, $xhtml);
    }
    $out .= self::renderTag('input', $this->props, false, $xhtml, array('label', 'required'))."\n";

    if (isset($this['label']) && in_array($this['type'], array('checkbox', 'radio'))) {
      $out .= self::renderTag('label', array('for'=>$this['id']), htmlentities($this['label']))."\n";
    }

    return $out;
  }
}

class FormGenerator_Fieldset extends FormGenerator_Element {
  protected $fields = array();
  protected static $fsUniqueCounter = 0;

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

  protected function &addElement($name, $label, $type, array $extra = null) {
    $newObj = new FormGenerator_Element($name, $label, $type, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function addInput($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'text', $extra);
  }

  public function addPassword($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'password', $extra);
  }

  public function addCheckbox($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'checkbox', $extra);
  }

  public function addRadio($name, $label, $value, array $extra = null) {
    return $this->addElement($name, $label, 'radio', array('value'=>$value) + ($extra?$extra:array()));
  }

  public function addSubmit($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'submit', $extra);
  }

  public function addReset($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'reset', $extra);
  }

  public function addFieldset($label, array $extra = null) {
    $newObj = new FormGenerator_Fieldset($label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function addSelect($name, $label, $options, array $extra = null) {
    $newObj = new FormGenerator_Select($name, $label, $options, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function addTextarea($name, $label, array $extra = null) {
    $newObj = new FormGenerator_Textarea($name, $label, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function addHint($content, array $extra = null) {
    $newObj = new FormGenerator_Hint($content, $extra);
    $this->fields[] = $newObj;
    return $newObj;
  }

  public function renderElementList($elements, $xhtml=false) {
    if (!count($elements)) {
      return '';
    }
    $out = "<ul>\n";
    foreach ($elements as $e) {
      $out .= "<li>\n";
      if (isset($e['name']) && $e['name']) {
        if (isset($this->data[$e['name']])) {
          $e->populate($this->data[$e['name']]);
        }
        $out .= $e->render($xhtml , $this->getError($e['name']));
      } else {
        if ($e instanceof FormGenerator_Fieldset) {
          $e->populate($this->data);
          $e->propagateErrors($this->errors);
        }
        $out .= $e->render($xhtml);
      }
      $out .= "</li>\n";
    }
    $out .= "</ul>\n";

    return $out;
  }

  public function render($xhtml=false, $error=null) {
    $out = self::renderTag('fieldset', $this->props, null, false, array('label', 'type'))."\n";
    if (isset($this['label']) && $this['label']) {
      # TODO: required flag
      $out .= '<legend><span>'.htmlentities($this['label'])."</span></legend>\n";
    }

    // render elements
    $out .= $this->renderElementList($this->fields, $xhtml);

    $out .= "</fieldset>\n";

    return $out;
  }
}

class FormGenerator_Textarea extends FormGenerator_Element {
  public function __construct($name, $label, array $extra = null) {
    parent::__construct($name, $label, 'textarea', $extra);
  }

  /**
   * Render a form element.
   *
   * @param bool   $xhtml Whether to generate XHTML or HTML.
   * @param string $error An error message to show, if applicable.
   * @return string
   */
  public function render($xhtml=false, $error=null) {
    $out = '';
    # add label, and remove from properties array
    if (isset($this['label'])) {
      $labelcontent = htmlentities($this['label']).':';
      if (isset($this['required']) && $this['required']) {
        $labelcontent .= ' <em>Required</em>';
      }
      $out .= self::renderTag('label', array('for'=>$this['id']), $labelcontent)."\n";
    }
    if (!is_null($this->data)) {
      $data = $this->data;
    } elseif (isset($this['value'])) {
      $data = $this['value'];
    } else {
      $data = '';
    }
    if ($error) {
      $out .= self::renderError($this->props, $error, $xhtml);
    }
    $out .= self::renderTag('textarea', $this->props, htmlentities($data), false, array('label', 'value', 'type'))."\n";

    return $out;
  }
}

class FormGenerator_Select extends FormGenerator_Element {
  protected $options = array();
  const OPTIONS_KEY_VALUE  = 0;
  const OPTIONS_VALUE_ONLY = 1;
  const OPTIONS_FULL       = 2;

  public function __construct($name, $label, array $options = null, array $extra = null) {
    parent::__construct($name, $label, 'select', $extra);
    if (!is_null($options)) {
      $this->setOptions($options, self::OPTIONS_KEY_VALUE);
    }
  }

  public function setOptions(array $options, $arraytype = self::OPTIONS_KEY_VALUE) {
    switch ($arraytype) {
      case self::OPTIONS_FULL:
        $this->options = $options;
        return; # NOTE: this is a return
      case self::OPTIONS_KEY_VALUE:
        foreach ($options as $k => $v) {
          $this->options[$k] = array('value'=>$k, 'label'=>$v);
        }
        break;
      case self::OPTIONS_VALUE_ONLY:
        $this->options = array_combine($options, array_map($val_creator, $options));
        foreach ($this->options as $k => $v) {
          $this->options[$k] = array('value'=>$k, 'label'=>$v);
        }
        break;
      default:
        throw new InvalidArgumentException('Invalid argument types');
    }
  }

  /**
   * Render a form element.
   *
   * @param bool   $xhtml Whether to generate XHTML or HTML.
   * @param string $error An error message to show, if applicable.
   * @return string
   */
  public function render($xhtml=false, $error=null) {
    $out = '';
    # add label, and remove from properties array
    if (isset($this['label'])) {
      $labelcontent = htmlentities($this['label']).':';
      if (isset($this['required']) && $this['required']) {
        $labelcontent .= ' <em>Required</em>';
      }
      $out .= self::renderTag('label', array('for'=>$this['id']), $labelcontent)."\n";
    }
    if ($error) {
      $out .= self::renderError($this->props, $error, $xhtml);
    }
    $out .= self::renderTag('select', $this->props, null, false, array('label', 'value', 'type'))."\n";
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
      $out .= self::renderTag('option', $option, htmlentities($option['label']), false, array('label'))."\n";
    }
    $out .= "</select>\n";

    return $out;
  }
}


class FormGenerator_Hint extends FormGenerator_Element {
  protected static $hintUniqueCounter = 0;

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
   * @param bool   $xhtml Whether to generate XHTML or HTML.
   * @param string $error An error message to show, if applicable.
   * @return string
   */
  public function render($xhtml=false, $error=null) {
    $out = '';
    if (isset($this['content'])) {
      if (!isset($this['escape']) || $this['escape']) {
        $content = htmlentities($this['content']);
      } else {
        $content = $this['content'];
      }
    } else {
      $content = '';
    }

    $out .= self::renderTag('div', $this->props, $content, false, array('content', 'escape', 'type'))."\n";

    return $out;
  }
}


class FormGeneratorElementException extends Exception {
  public function __construct($message, FormGenerator_Element $element) {
    $this->message = $message;
  }
}
