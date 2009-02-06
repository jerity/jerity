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
   * Attributes for the form element
   */
  protected $formProperties = array();
  /**
   * Whether to generate XHTML or just HTML
   */
  protected $xhtml = false;


  /**
   * Initialise the form generator.
   *
   * @param bool $xhtml Whether to generate XHTML or just HTML.
   */
  public function __construct($xhtml = false) {
    $this->xhtml = $xhtml;
  }

  protected function addElement($name, $label, $type, array $extra = null) {
    $this->fields[] = new FormGenerator_Element($name, $label, $type, $extra);
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

  public function addRadio($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'radio', $extra);
  }

  public function addSubmit($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'submit', $extra);
  }

  public function addReset($name, $label, array $extra = null) {
    return $this->addElement($name, $label, 'reset', $extra);
  }

  public function addFieldset($label, array $extra = null) {
    return $this->addElement($name, $label, 'reset', $extra);
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

  /**
   * Render a form element.
   *
   * @param array  $element Array of element properties.
   * @param string $error   Error message for the element, if applicable.
   * @return string
   */
  public function renderElement(FormGenerator_Element $element, $error=null) {
    $out = '';
    if (!isset($element['type'])) {
      throw new FormGeneratorElementException('Element without type', $element);
    }
    switch ($element['type']) {
      case 'text':
      case 'password':
      case 'checkbox':
      case 'radio':
      case 'submit':
      case 'reset':
        # TODO: add label, and remove from array
        if (isset($element['label'])) {
          $out .= '<label for="'.htmlentities($element['id']).'">'.htmlentities($element['label'])."</label>\n";
          unset($element['label']);
        }
        $out .= '<input';
        foreach ($element as $k=>$v) {
          $out .= ' '.$k.'="'.htmlentities($v).'"';
        }
        $out .= ($this->xhtml) ? " />\n" : ">\n";
        break;
      case 'select':
        break;
      case 'textarea':
        break;
      case 'button':
        break;
      case 'fieldset':
        # render sub-elements
        break;
      default:
        throw new FormGeneratorElementException('Unknown element type', $element);
    }
    return $out;
  }

  public function renderElementList($elements) {
    if (!count($elements)) {
      return '';
    }
    $out = "<ul>\n";
    foreach ($elements as $e) {
      $out .= "<li>\n";
      if (isset($e['name']) && $e['name']) {
        $out .= $this->renderElement($e, $this->getError($e['name']));
      } else {
        $out .= $this->renderElement($e);
      }
      $out .= "</li>\n";
    }
    $out .= "</ul>\n";

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

  public function __construct($name, $label, $type, array $extra = null) {
    $this->props = array(
      'name'  => $name,
      'label' => $label,
      'type'  => $type,
    );
    if (!is_null($extra)) {
      $this->props = $this->props + $extra;
    }
    if (!isset($this->props['id'])) {
      $this->props['id'] = 'form-el'.(++$this->uniqueCounter).'-'.$name;
    }
    if (!isset($newEl['class'])) {
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
}

class FormGenerator_Fieldset extends FormGenerator_Element {
}


class FormGeneratorElementException extends Exception {
  public function __construct($message, FormGenerator_Element $element) {
    $this->message = $message;
  }
}
