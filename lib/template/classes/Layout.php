<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

/**
 * @package    jerity.template
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010 Nick Pope
 */

/**
 * A layout class that handles creation of columns.
 *
 * @package    jerity.template
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010 Nick Pope
 */
class Layout implements Renderable {

  /**
   * The unit types that columns can be specified in.
   */
  const UNIT_PIXELS     = 0;
  const UNIT_PERCENTAGE = 1;
  const UNIT_CLASS_NAME = 2;

  /**
   * The units in use for the column widths.
   *
   * @var  int
   */
  protected $units = self::UNIT_CLASS_NAME;

  /**
   * The column widths for this layout.
   *
   * @var  array
   */
  protected $columns = array();

  /**
   * The content within the columns.
   *
   * @var  array
   */
  protected $content = array();

  /**
   * The current column to add content to.
   *
   * @var  int
   */
  protected $current_column = 1;

  /**
   * The ID to use for the layout element.
   *
   * @var  string
   */
  protected $layout_id = '';

  /**
   * The CSS class to use for the layout.
   *
   * @var  string
   */
  protected $layout_class = 'layout';

  /**
   * The CSS class to use for the columns.
   *
   * @var  string
   */
  protected $column_class = 'column';

  /**
   * Creates a new layout with the specified column widths.
   *
   * @param  array  $columns  An array of column widths.
   * @param  int    $units    How the column widths have been specified.
   */
  public function __construct(array $columns, $units = self::UNIT_CLASS_NAME) {
    $this->columns = $columns;
    $this->units   = $units;
    $this->content = array_fill(0, count($columns), array());
  }

  /**
   * Create a new layout in a fluent API manner.
   *
   * @param  array  $columns  An array of column widths.
   * @param  int    $units    How the column widths have been specified.
   *
   * @return  Layout
   *
   * @see     self::__construct()
   */
  public static function create(array $columns, $units = self::UNIT_CLASS_NAME) {
    return new Layout($columns, $units);
  }

  /**
   * Returns the columns specified by this layout.
   *
   * @return  array
   */
  public function getColumns() {
    return $this->columns;
  }

  /**
   * Returns the units that the columns have been specified in.
   *
   * @return  int
   */
  public function getUnits() {
    return $this->units;
  }

  /**
   * Returns the content for the columns.
   *
   * @return  array
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * Adds content to the current column.
   *
   * $object can be any of Layout or Content objects or string, or an array of
   * any combination of the above.
   *
   * $index can be used to specify the location in the column that $object
   * should placed.  0 will place at the start, null will place at the end,
   * positive values will place from the start and negative from the end.
   *
   * @param  mixed  $object  The content to add to the column.
   * @param  int    $index   Where to place the content.
   *
   * @return  Layout  The current Layout object, for method chaining.
   */
  public function addContent($object, $index = null) {
    $column = &$this->content[$this->current_column-1];
    switch (true) {
      # Add to end of column.
      case (is_null($index)):
        $index = count($column);
        break;
      # Add to start of column.
      case ($index === 0):
        $index = 0;
        break;
      # Insert into column from end.
      case ($index < 0):
        $index = max($index, count($column));
        break;
      # Insert into column from start.
      case ($index > 0):
        $index = min($index, count($column));
        break;
    }
    if (!is_array($object)) $object = array($object);
    array_splice($column, $index, 0, $object);
    return $this;
  }

  /**
   * Sets the column to add content to to the first column.
   *
   * @return  Layout  The current Layout object, for method chaining.
   */
  public function firstColumn() {
    $this->current_column = 1;
    return $this;
  }

  /**
   * Sets the column to add content to to the last column.
   *
   * @return  Layout  The current Layout object, for method chaining.
   */
  public function lastColumn() {
    $this->current_column = count($this->columns);
    return $this;
  }

  /**
   * Sets the column to add content to to the previous column.
   *
   * @return  Layout  The current Layout object, for method chaining.
   */
  public function previousColumn() {
    $this->current_column--;
    if ($this->current_column < 1) {
      $this->current_column = 1;
      trigger_error('No previous column available.', E_USER_WARNING);
    }
    return $this;
  }

  /**
   * Sets the column to add content to to the next column.
   *
   * @return  Layout  The current Layout object, for method chaining.
   */
  public function nextColumn() {
    $this->current_column++;
    if ($this->current_column > count($this->columns)) {
      $this->current_column = count($this->columns);
      trigger_error('No next column available.', E_USER_WARNING);
    }
    return $this;
  }

  /**
   * Gets the ID for the layout element.
   *
   * @return  string
   */
  public function getLayoutId() {
    return $this->layout_id;
  }

  /**
   * Sets the ID for the layout element.
   *
   * @param  string  $v  The ID to use.
   */
  public function setLayoutId($v) {
    $this->layout_id = $v;
  }

  /**
   * Gets the CSS class for the layout.
   *
   * @return  string
   */
  public function getLayoutClass() {
    return $this->layout_class;
  }

  /**
   * Sets the CSS class for the layout.
   *
   * @param  string  $v  The CSS class to use.
   */
  public function setLayoutClass($v) {
    $this->layout_class = $v;
  }

  /**
   * Gets the CSS class for the columns.
   *
   * @return  string
   */
  public function getColumnClass() {
    return $this->column_class;
  }

  /**
   * Sets the CSS class for the columns.
   *
   * @param  string  $v  The CSS class to use.
   */
  public function setColumnClass($v) {
    $this->column_class = $v;
  }

  /**
   * Validates the layout, checking the column widths and content.
   *
   * @return  bool
   */
  public function validate() {
    # Check column based on the units:
    switch ($this->units) {
      case self::UNIT_PIXELS:
        foreach ($this->columns as $column) {
          if (!String::isInteger($column)) return false;
        }
        break;
      case self::UNIT_PERCENTAGE:
        foreach ($this->columns as $column) {
          if (!String::isInteger($column)) return false;
        }
        if (array_sum($this->columns) !== 100) return false;
        break;
      case self::UNIT_CLASS_NAME:
        foreach ($this->columns as $column) {
          if (!preg_match('/^-?[_a-z][-\w]*$/i', $column)) return false;
        }
        break;
      default:
        trigger_error('Invalid column unit.', E_USER_NOTICE);
    }

    # Check CSS classes and IDs are valid.
    if ($this->layout_id) {
      if (!preg_match('/^-?[_a-z][-\w]*$/i', $this->layout_id))    return false;
    }
    if ($this->layout_class) {
      if (!preg_match('/^-?[_a-z][-\w]*$/i', $this->layout_class)) return false;
    }
    if ($this->column_class) {
      if (!preg_match('/^-?[_a-z][-\w]*$/i', $this->column_class)) return false;
    }

    # Check number of content groups provided matches specified column count.
    if (count($this->content) !== count($this->columns)) return false;

    # Check for valid content objects.
    $check_class = create_function('&$v,$k', 'return (is_array($v) || is_string($v) || $v instanceof Content || $v instanceof Layout);');
    return array_walk_recursive($this->content, $check_class);
  }

  /**
   * Render the layout using the current global rendering context, and return
   * it as a string.
   *
   * @return  string
   *
   * @throws  RuntimeException
   */
  public function render() {
    if (!$this->validate()) {
      trigger_error('Failed to validate the layout.', E_USER_NOTICE);
    }
    $output = '<div';
    if ($this->layout_id)    $output .= " id=\"{$this->layout_id}\"";
    if ($this->layout_class) $output .= " class=\"{$this->layout_class}\"";
    $output .= '>' . PHP_EOL;
    for ($i = 0; $i < count($this->columns); $i++) {
      $column  = &$this->columns[$i];
      $content = &$this->content[$i];
      $output .= '<div';
      switch ($this->units) {
        case self::UNIT_PIXELS:
          if ($this->column_class) {
            $output .= ' class="'.$this->column_class.'"';
          }
          $output .= ' style="width: '.$column.'px;"';
          break;
        case self::UNIT_PERCENTAGE:
          if ($this->column_class) {
            $output .= ' class="'.$this->column_class.'"';
          }
          $output .= ' style="width: '.$column.'%;"';
          break;
        case self::UNIT_CLASS_NAME:
          $output .= ' class="';
          if ($this->column_class) {
            $output .= $this->column_class.' ';
          }
          $output .= $column.'"';
          break;
        default:
          trigger_error('Invalid column unit.', E_USER_NOTICE);
      }
      $output .= '>' . PHP_EOL;
      if ($content) {
        foreach ($content as $item) {
          $output .= $item->render();
        }
      }
      $output .= '</div>' . PHP_EOL;
    }
    $output .= '</div>' . PHP_EOL;
    return $output;
  }

}
