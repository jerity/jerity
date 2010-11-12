<?php
define('DEFAULT_TYPE', '1');
define('DEFAULT_SHELL', false);
define('WIDTH_2COL_A', 70);
define('WIDTH_2COL_B', 30);

class Layout {

  const CELL_PADDING = 7;

  var $type_default;
  var $shell_default;
  var $type;
  var $shell;

  var $widths = array();
  var $class_name = '';
  var $container_class_name = '';

  var $stack = array();

  public function __construct() {
    $this->type_default = defined('DEFAULT_TYPE') ? constant('DEFAULT_TYPE') : '1';
    $this->shell_default = defined('DEFAULT_SHELL') ? constant('DEFAULT_SHELL') : false;
    $this->reset();
  }

  function reset() {
    $this->type = $this->type_default;
    $this->shell = $this->shell_default;
    $this->class_name = '';
    $this->container_class_name = '';
    $this->widths = array();
  }

  function setType($type) {
    $this->type = $type;
  }

  function setShell($shell) {
    $this->shell = $shell;
  }

  function setClass($class) {
    $this->class_name = $class;
  }

  function setContainerClass($class) {
    $this->container_class_name = $class;
  }

  function setColWidths(array $widths) {
    $this->widths = $widths;
  }

  function start() {
    echo $this->getStart();
  }

  function getStart() {
    // start to build output
    $o = '';

    $widths = $this->widths;

    $default_widths = array();
    $column_count = 1;

    switch ($this->type) {
      case '2ab':
        $default_widths = array(
          defined('WIDTH_2COL_A') ? constant('WIDTH_2COL_A') : 50,
          defined('WIDTH_2COL_B') ? constant('WIDTH_2COL_B') : 50,
        );
        $column_count = 2;
        break;
      case '2ba':
        $default_widths = array(
          defined('WIDTH_2COL_A') ? constant('WIDTH_2COL_A') : 50,
          defined('WIDTH_2COL_B') ? constant('WIDTH_2COL_B') : 50,
        );
        $column_count = 2;
        break;
      case '3abc':
        $default_widths = array(
          defined('WIDTH_3COL_A') ? constant('WIDTH_3COL_A') : 20,
          defined('WIDTH_3COL_B') ? constant('WIDTH_3COL_B') : 60,
          defined('WIDTH_3COL_C') ? constant('WIDTH_3COL_C') : 20,
        );
        $column_count = 3;
        break;
      case '1':
      default:
        $this->type = '1';
        $column_count = 1;
    }

    for ($i = 0; $i < $column_count; $i++) {
      if (!isset($widths[$i])) {
        if (isset($default_widths[$i])) {
          $widths[$i] = $default_widths[$i];
        } else {
          $widths[$i] = '';
        }
      }

      if ($widths[$i] != '') {
        $widths[$i] = rtrim($widths[$i], '%');
        $width = rtrim($widths[$i], 'px');
        $width2 = null;
        if (is_numeric($widths[$i])) {
          $width .= '%';
        } else {
          $width2 = $width + 2 * self::CELL_PADDING;
          $width .= 'px';
          $width2 .= 'px';
        }
        if ($width2) {
          # Hack for consistent behaviour in all browsers: use *-hack to serve width excluding padding to IE
          $widths[$i] = ' width="' . $width . '" style="width: ' . $width2 . '; *width: ' . $width . ';"'; 
        } else {
          $widths[$i] = ' width="' . $width . '" style="width: ' . $width . ';"'; 
        }
      }
    }
    
    $class_name = trim($this->class_name);
    $class_ext = '';
    $table_class_name = $class_name;
    if ($class_name != '') {  
      $class_ext = '-' . $class_name;
      $class_name = ' ' . $class_name;
      $table_class_name = ' l-' . $table_class_name;
    }
    $container_class_name = trim($this->container_class_name);
    if ($container_class_name != '') {  
      $container_class_name = ' ' . $container_class_name;
    }
    
    # Push new layout onto layout stack.
    $this->stack[] = array(
      'type'           => $this->type,
      'shell'          => $this->shell,
      'class_name'     => $class_name,
      'column_count'   => $column_count,
      'current_column' => 1,
      'widths'         => $widths
    );

    $o .= '<div class="lc lc' . $class_ext . $container_class_name . '">';

    $container_classes = '';
    $container_class_name = trim($this->container_class_name);
    if ($container_class_name != '') {  
      $container_classes = ' lci-' . implode(' lci-', explode(' ', $container_class_name));
    }
    if ($this->shell) {
      $o .= '<div class="lci' . $class_name . $container_classes . ' lcA' . $class_ext . '"><div class="lcB' . $class_ext . '">';
    } else {
      $o .= '<div class="lci' . $class_name . $container_classes . '">';
    }

    $className_td = ($class_name == '') ? '' : $class_name . '-a';

    switch ($this->type) {
      case '1':
        $o .= '<table class="l' . $table_class_name . '" width="100%" summary=""><tr><td class="ltd' . $class_name_td . '"><div class="ll' . $class_name_td . '">';
        break;
    
      case '3abc': 
        $o .= '<table class="l' . $table_class_name . '" width="100%" summary="">';
        $o .= '<col' . $widths[0] . '><col' . $widths[1] . '><col' . $widths[2] . '>';
        $o .= '<tr><td valign="top" class="ltd' . $class_name_td . '"><div class="ll' . $class_name_td . '">';
        break;
    
      case '2ab':
        $o .= '<table class="l' . $table_class_name . '" width="100%" summary="">';
        $o .= '<col' . $widths[0] . '><col' . $widths[1] . '>';
        $o .= '<tr><td valign="top" class="ltd' . $class_name_td . '"><div class="ll' . $class_name_td . '">';
        break;
    
      case '2ba':
      default:
        $o .= '<table class="l' . $table_class_name . '" width="100%" summary="">';
        $o .= '<col' . $widths[1] . '><col' . $widths[0] . '>';
        $o .= '<tr><td class="ld">&nbsp;</td><td rowspan="2" valign="top" class="ltd' . $class_name_td . '"><div class="ll' . $class_name_td . '">';
    }

    # Reset for subsequent layouts.
    $this->reset();

    return $o;
  }

  function endCol() {
    echo $this->getEndCol();
  }

  function getEndCol() {
    if (!count($this->stack)) return '';
    $o = '</div></td>';
    $current_layout = end($this->stack);
    if ($current_layout['current_column'] >= $current_layout['column_count']) {
      # End of current layout reached.
      unset($this->stack[key($this->stack)]);
      $o .= '</tr></table>';
      if ($current_layout['shell']) {
        $o .= '</div>';
      }
      $class_name = trim($current_layout['class_name']);
      if (empty($class_name)) {  
        $class_name = 'l';
      }
      # Start next column.
      $o .= '<td valign="top" class="ltd ' . $class_name . '-'
        . chr(ord('a') + $current_layout['current_column']) . '"><div class="ll '
        . $class_name . '-' . chr(ord('a') + $current_layout['current_column'])
        . '">';
      $this->stack[key($this->stack)]['current_column']++;
    }
    return $o;
  }
}
