<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.layout
 */

namespace Jerity\Layout;

use \Jerity\Core\Renderable;
use \Jerity\Util\String;

/**
 * A layout class that handles creation of columns.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.layout
 */
class Columns implements Renderable {

  /**
   *
   */
  const UNIT_PIXELS = 0;

  /**
   *
   */
  const UNIT_PERCENTAGE = 1;

  /**
   *
   */
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
  protected $current_column = 0;

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
   * Creates a new columns layout with the specified column widths.
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
   * Converts the object to a string by calling the render function.
   *
   * @return  string  The rendered layout.
   *
   * @see  self::render()
   */
  public function __toString() {
    return $this->render();
  }

  /**
   * Create a new columns layout in a fluent API manner.
   *
   * @param  array  $columns  An array of column widths.
   * @param  int    $units    How the column widths have been specified.
   *
   * @return  \Jerity\Layout\Columns
   *
   * @see     self::__construct()
   */
  public static function create(array $columns, $units = self::UNIT_CLASS_NAME) {
    return new Columns($columns, $units);
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
   * Returns the number of columns in this layout.
   *
   * @return  int
   */
  public function countColumns() {
    return count($this->columns);
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
   * $object can be any of Columns or Content objects or string, or an array of
   * any combination of the above.
   *
   * $index can be used to specify the location in the column that $object
   * should placed.  0 will place at the start, null will place at the end,
   * positive values will place from the start and negative from the end.
   *
   * @param  mixed  $object  The content to add to the column.
   * @param  int    $index   Where to place the content.
   *
   * @return  \Jerity\Layout\Columns  The current object, for method chaining.
   */
  public function addContent($object, $index = null) {
    $column = &$this->content[$this->current_column];
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
   * Sets the column to add content to the first column.
   *
   * @return  \Jerity\Layout\Columns  The current object, for method chaining.
   */
  public function firstColumn() {
    $this->current_column = 0;
    return $this;
  }

  /**
   * Sets the column to add content to the last column.
   *
   * @return  \Jerity\Layout\Columns  The current object, for method chaining.
   */
  public function lastColumn() {
    $this->current_column = count($this->columns) - 1;
    return $this;
  }

  /**
   * Sets the column to add content to the previous column.
   *
   * @param  bool  $cycle  Whether to cycle back to the last column.
   *
   * @return  \Jerity\Layout\Columns  The current object, for method chaining.
   */
  public function previousColumn($cycle = false) {
    $this->current_column--;
    if ($this->current_column < 0) {
      if ($cycle) {
        $this->current_column = count($this->columns) - 1;
      } else {
        $this->current_column = 0;
        trigger_error('No previous column available.', E_USER_WARNING);
      }
    }
    return $this;
  }

  /**
   * Sets the column to add content to the next column.
   *
   * @param  bool  $cycle  Whether to cycle forward to the first column.
   *
   * @return  \Jerity\Layout\Columns  The current object, for method chaining.
   */
  public function nextColumn($cycle = false) {
    $this->current_column++;
    if ($this->current_column >= count($this->columns)) {
      if ($cycle) {
        $this->current_column = 0;
      } else {
        $this->current_column = count($this->columns) - 1;
        trigger_error('No next column available.', E_USER_WARNING);
      }
    }
    return $this;
  }

  /**
   * Gets the currently selected column.
   *
   * @return  int  The current column index.
   */
  public function currentColumn() {
    return $this->current_column;
  }

  /**
   * Selects the column to add content to by index.
   *
   * @param  int  $index
   *
   * @return  \Jerity\Layout\Columns  The current object, for method chaining.
   */
  public function selectColumn($index) {
    $this->current_column = abs(intval($index));
    if ($this->current_column >= count($this->columns)) {
      $this->current_column = count($this->columns) - 1;
      trigger_error('Cannot select column out of range.', E_USER_WARNING);
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
    return $this;
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
    return $this;
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
    return $this;
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
    return array_walk_recursive($this->content, function (&$v, $k) {
      return (is_array($v) || is_string($v) || $v instanceof Content || $v instanceof Columns);
    });
  }

  /**
   * Render the layout using the current global rendering context, and return
   * it as a string.
   *
   * @return  string
   *
   * @throws  \RuntimeException
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
          if ($item instanceof Renderable) {
            $output .= $item->render();
          } else {
            $output .= $item;
          }
        }
      }
      $output .= '</div>' . PHP_EOL;
    }
    $output .= '</div>' . PHP_EOL;
    return $output;
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
