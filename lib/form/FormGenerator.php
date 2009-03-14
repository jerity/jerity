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
  public function __construct($xhtml = false, $topLevelList=true) {
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

  public function clearData() {
    $this->data = array();
  }

  public function populateData(array $data, $replace = true) {
    if ($replace) {
      $this->data = $data;
    } else { // merge and overwrite
      $this->data += $data;
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
    if ($this->topLevelList) $out = "<ul>\n";
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
      $out .= '<label for="'.htmlentities($this['id']).'">'.htmlentities($this['label']).":</label>\n";
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

    $out .= '<input';
    foreach ($this as $k=>$v) {
      if ($v===true) {
        $v = $k;
      }
      if ($k != 'label' && $v) {
        $out .= ' '.$k.'="'.htmlentities($v).'"';
      }
    }
    $out .= $xhtml ? " />\n" : ">\n";
    if (isset($this['label']) && in_array($this['type'], array('checkbox', 'radio'))) {
      $out .= '<label for="'.htmlentities($this['id']).'">'.htmlentities($this['label'])."</label>\n";
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

  public function renderElementList($elements) {
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
        $out .= $e->render($this->xhtml /*, $this->getError($e['name']) */);
      } else {
        if ($e instanceof FormGenerator_Fieldset) {
          $e->populate($this->data);
        }
        $out .= $e->render($this->xhtml);
      }
      $out .= "</li>\n";
    }
    $out .= "</ul>\n";

    return $out;
  }

  public function render() {
    $out = '<fieldset';
    foreach ($this as $k=>$v) {
      if ($k != 'label' && $k != 'type') {
        $out .= ' '.$k.'="'.htmlentities($v).'"';
      }
    }
    $out .= ">\n";
    if (isset($this['label']) && $this['label']) {
      $out .= '<legend><span>'.htmlentities($this['label'])."</span></legend>\n";
    }

    // render elements
    $out .= $this->renderElementList($this->fields);

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
      $out .= '<label for="'.htmlentities($this['id']).'">'.htmlentities($this['label']).":</label>\n";
    }
    $out .= '<textarea';
    foreach ($this as $k=>$v) {
      if ($k != 'label' && $k != 'value' && $k != 'type') {
        $out .= ' '.$k.'="'.htmlentities($v).'"';
      }
    }
    $out .= '>';
    if (!is_null($this->data)) {
      $out .= htmlentities($this->data);
    } elseif (isset($this['value'])) {
      $out .= htmlentities($this['value']);
    }
    $out .= "</textarea>\n";

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
        $this->options = array_map($val_creator, $options);
        break;
      case self::OPTIONS_VALUE_ONLY:
        $this->options = array_combine($options, array_map($val_creator, $options));
        break;
      default:
        throw new InvalidArgumentException('Invalid argument types');
    }
    foreach ($this->options as $k => $v) {
      $this->options[$k] = array('value'=>$k, 'label'=>$v);
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
      $out .= '<label for="'.htmlentities($this['id']).'">'.htmlentities($this['label']).":</label>\n";
    }
    $out .= '<select';
    foreach ($this as $k=>$v) {
      if ($k != 'label' && $k != 'value' && $k != 'type') {
        $out .= ' '.$k.'="'.htmlentities($v).'"';
      }
    }
    $out .= ">\n";
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
      $out .= '<option';
      foreach ($option as $k=>$v) {
        if ($k != 'label') {
          $out .= ' '.$k.'="'.htmlentities($v).'"';
        }
      }
      $out .= '>';
      $out .= htmlentities($option['label']);
      $out .= "</option>\n";
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
    $out .= '<div';
    foreach ($this as $k=>$v) {
      if ($k != 'content' && $k != 'type') {
        $out .= ' '.$k.'="'.htmlentities($v).'"';
      }
    }
    $out .= '>';
    if (isset($this['content'])) {
      if (!isset($this['escape']) || $this['escape']) {
        $out .= htmlentities($this['content']);
      } else {
        $out .= $this['content'];
      }
    }
    $out .= "</div>\n";

    return $out;
  }
}


class FormGeneratorElementException extends Exception {
  public function __construct($message, FormGenerator_Element $element) {
    $this->message = $message;
  }
}
