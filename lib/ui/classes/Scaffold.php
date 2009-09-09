<?php
/**
 * @package    JerityUI
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */

/**
 * Creates scaffolding from a database table definition.
 *
 * Scaffolding is useful for rapid prototyping -- it provides everything
 * required for interaction with a database table: listing of contents,
 * creation of new records, editing of existing records, and deletion of
 * existing records. This class provides ways of generating all of this data.
 *
 * The schema should be defined as a multidimensional array. An example schema
 * is given below:
 *
 *  array(
 *    'post' => array(
 *      'fields' => array(
 *        'id'        => array('uint', 'primary'),
 *        'title'     => array('varchar(50)', 'display'),
 *        'content'   => 'text',
 *        'author_id' => 'uint',
 *        'posted'    => 'datetime',
 *        'private'   => 'boolean',
 *      ),
 *      'hasMany' => array(
 *        'comment' => 'post_id',
 *      ),
 *      'belongsTo' => array(
 *        'user' => 'author_id',
 *      ),
 *    ),
 *    'user' => array(
 *      'fields' => array(
 *        'id'         => array('uint', 'primary'),
 *        'username'   => 'varchar(50)',
 *        'name'       => array('varchar(75)', 'display'),
 *        'joined'     => 'datetime',
 *        'reputation' => 'int',
 *        'gender'     => array('enum', '', array('Male', 'Female', 'DNR')),
 *        'role'       => array('int', '', array(0=>'Reader', 1=>'Poster', 2=>'Editor', 9=>'Admin')),
 *      ),
 *      'hasMany' => array(
 *        'comment' => 'author_id',
 *        'post'    => 'author_id',
 *      ),
 *    )
 *  );
 *
 * @package    JerityUI
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */
class Scaffold {

  const FORM_PREFIX = '__scaffold';

  protected $db = null;
  protected $schema = array();

  /**
   * Create a scaffold based upon the given schema.
   *
   * @param  array  $schema  The database schema to be used.
   * @param  PDO    $db      The database connection to be used.
   */
  public function __construct(array $schema, PDO $db) {
    $this->schema = $this->transformSchema($schema);
    $this->db = $db;
  }

  /**
   * Normalise the schema and pull out important fields (e.g. primary key and
   * display field).
   *
   * @todo  Automatically fetch field definitions from database.
   *
   * @param   array  $schema  The database schema definition.
   *
   * @return  array
   */
  protected function transformSchema(array $schema) {
    // go through the schema and precalculate some things we'll find useful
    foreach (array_keys($schema) as $table) {
      // primary key field (only one supported per table)
      $schema[$table]['_primary'] = null;
      // display field (only one supported per table)
      $schema[$table]['_display'] = null;

      foreach ($schema[$table]['fields'] as $field => $data) {
        // just a simple field; normalise it
        if (!is_array($data)) {
          $schema[$table]['fields'][$field] = array($data, '', array());
          continue;
        }
        // field attributes
        if (!isset($data[1])) {
          $data[1] = '';
        } else {
          $data[1] = strtolower($data[1]);
          switch ($data[1]) {
            case 'primary':
              $schema[$table]['_primary'] = $field;
              break;
            case 'display':
              $schema[$table]['_display'] = $field;
              break;
          }
        }
        // field values
        if (!isset($data[2])) {
          $data[2] = null;
        } elseif (!is_array($data[2])) {
          $data[2] = array($data[2]);
        }
        // save back to schema
        $schema[$table]['fields'][$field] = $data;
      }

      if (isset($schema[$table]['belongsTo'])) {
        $schema[$table]['_belongsTo'] = array_flip($schema[$table]['belongsTo']);
      } else {
        $schema[$table]['_belongsTo'] = array();
      }
    }

    return $schema;
  }

  /**
   * Perform the actions dictated by the posted data.
   */
  public function processActions() {
    if (!isset($_POST[self::FORM_PREFIX.'_action'])) {
      return;
    }
    $action = $_POST[self::FORM_PREFIX.'_action'];
    $values = $_POST[self::FORM_PREFIX.'_args'];
    $primary_key = $_POST[self::FORM_PREFIX.'_primary'];

    try {
      $mode = $this->db->getAttribute(PDO::ATTR_ERRMODE);
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      switch ($action) {
        case 'create':
          foreach ($values as $table => $fields) {
            $sql = 'INSERT INTO `'.$table.'` (`'.implode('`, `', array_keys($fields)).'`) VALUES ('.implode(', ', array_fill(0, count($fields), '?')).')';
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(array_values($fields));
            if (!$result) {
              throw new Exception('Could not update table '.$table);
            }
          }
          break;
        case 'update':
          foreach ($values as $table => $values) {
            $primary_field = $this->schema[$table]['_primary'];

            $sql = 'UPDATE `'.$table.'` SET ';
            $sql_fragments = array();
            $sql_values = array();
            foreach (array_keys($this->schema[$table]['fields']) as $field) {
              if ($field === $primary_field) continue;
              $sql_fragments[] = '`'.$field.'` = ?';
              $sql_values[] = isset($values[$field]) ? $values[$field] : '';
            }
            $sql .= implode(', ', $sql_fragments);
            $sql .= ' WHERE `'.$primary_field.'` = ? LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $data = array_merge($sql_values, array($primary_key));
            $result = $stmt->execute($data);
            if (!$result) {
              throw new Exception('Could not update table '.$table);
            }
          }
          break;
        case 'delete':
          foreach ($values as $table => $values) {
            $primary_field = $this->schema[$table]['_primary'];

            $sql = 'DELETE FROM `'.$table.'` WHERE `'.$primary_field.'` = ? LIMIT 1';
            $stmt = $this->db->prepare($sql);
            if (is_array($values)) {
              foreach ($values as $value) {
                $result = $stmt->execute($value);
              }
            } else {
              $result = $stmt->execute($values);
            }
          }
          break;
        default:
      }

      $this->db->setAttribute(PDO::ATTR_ERRMODE, $mode);
    } catch (Exception $e) {
      $this->db->setAttribute(PDO::ATTR_ERRMODE, $mode);
      var_dump('Database error: '.$e->getMessage());
    }
  }

  /**
   * Generate an input element for a foreign key.
   *
   * @param  FormGenerator  $generator     The FormGenerator instance to which
   *                                       the field should be added.
   * @param  string         $table         The table containing the foreign key.
   * @param  string         $field         The name of the field containing the
   *                                       foreign key.
   * @param  string         $linked_table  The name of the table to which the
   *                                       foreign key is linked.
   *
   * @return  FormGenerator_Select
   */
  public function inputFromLinkedField(FormGenerator $generator, $table, $field, $linked_table) {
    $options = array();

    if (!isset($this->schema[$linked_table]['_link_cache'])) {
      $primary = $this->schema[$linked_table]['_primary'];
      $display = null;
      if (isset($this->schema[$linked_table]['_display'])) {
        $display = $this->schema[$linked_table]['_display'];
      }

      if (is_null($display)) {
        $q = $this->db->prepare('SELECT `'.$primary.'`, `'.$primary.'` FROM `'.$linked_table.'` ORDER BY `'.$primary.'`');
      } else {
        $q = $this->db->prepare('SELECT `'.$primary.'`, `'.$display.'` FROM `'.$linked_table.'` ORDER BY `'.$display.'`');
      }
      $q->execute();
      $this->schema[$linked_table]['_link_cache'] = $q->fetchAll(PDO::FETCH_NUM);
    }

    $rs = $this->schema[$linked_table]['_link_cache'];

    foreach ($rs as $r) {
      $options[$r[0]] = $r[1];
    }
    $formname = self::FORM_PREFIX.'_args['.$table.']['.$field.']';
    return $generator->addSelect($formname, $field, $options);
  }

  /**
   * Generate an input element for a given table field. Will automatically
   * choose the correct input type based on the schema, including foreign key
   * fields.
   *
   * @param  FormGenerator  $generator     The FormGenerator instance to which
   *                                       the field should be added.
   * @param  string         $table         The table containing the foreign key.
   * @param  string         $field         The name of the field containing the
   *                                       foreign key.
   *
   * @return  FormGenerator_Element
   */
  public function inputFromField(FormGenerator $generator, $table, $field) {
    $table_schema = $this->schema[$table];
    list($type, $attributes, $additional) = $table_schema['fields'][$field];

    // table which provides values for this field
    $belongsTo = null;
    if (isset($table_schema['_belongsTo'][$field])) {
      $linked_table = $table_schema['_belongsTo'][$field];
      if (isset($this->schema[$linked_table]['_primary'])) {
        return $this->inputFromLinkedField($generator, $table, $field, $linked_table);
      }
    }

    $type = strtolower($type);

    if (preg_match('/^([^0-9]+)\((\d+)\)$/', $type, $matches)) {
      $type = $matches[1];
      $length = $matches[2];
    }

    $formname = self::FORM_PREFIX.'_args['.$table.']['.$field.']';

    switch ($type) {
      case 'char':
      case 'varchar':
        if (isset($length) && $length) {
          return $generator->addInput($formname, $field, array('maxlength'=>$length, 'size'=>$length));
        } else {
          return $generator->addInput($formname, $field);
        }
      case 'int':
      case 'uint':
        if (is_array($additional) && count($additional)) {
          return $generator->addSelect($formname, $field, $additional);
        } else {
          return $generator->addInput($formname, $field, array('size'=>5));
        }
      case 'date':
        return $generator->addHint($field.' ('.$type.') goes here.');
      case 'time':
        return $generator->addHint($field.' ('.$type.') goes here.');
      case 'datetime':
        return $generator->addHint($field.' ('.$type.') goes here.');
      case 'text':
        return $generator->addTextarea($formname, $field);
      case 'enum':
        return $generator->addSelect($formname, $field, $additional);
      case 'boolean':
        return $generator->addCheckbox($formname, $field, array('value'=>'1'));
    }
  }

  /**
   * Generate a form of the given type from the given table.
   *
   * @param  string  $table             The table for which the form should be
   *                                    generated.
   * @param  string  $action            The form type: create or update.
   * @param  FormGenerator  $generator  An existing FormGenerator that should
   *                                    be added to. If not provided, a new
   *                                    instance is created.
   * @param  mixed   $primary_key       The primary key value for an update
   *                                    form.
   *
   * @return  FormGenerator  The generator with other fields added.
   */
  protected function generateForm($table, $action, FormGenerator $generator = null, $primary_key=null) {
    if (is_null($generator)) {
      $generator = new FormGenerator(true);
    }
    $generator->addHidden(self::FORM_PREFIX.'_action', $action);
    $fields = $this->schema[$table]['fields'];
    if (!is_null($primary_key)) {
      $generator->addHidden(self::FORM_PREFIX.'_primary', $primary_key);
      $fields = array_diff_key($fields, array($this->schema[$table]['_primary'] => ''));
      $generator->addCustomHTML('<label>'.$this->schema[$table]['_primary'].':</label> '.$primary_key);
    }
    foreach ($fields as $field => $data) {
      $this->inputFromField($generator, $table, $field);
    }
    return $generator;
  }

  /**
   * Generate form elements suitable for creating a new record in the given
   * table.
   *
   * @param  string         $table      The table for which elements should be
   *                                    generated.
   * @param  FormGenerator  $generator  An existing FormGenerator that should
   *                                    be added to. If not provided, a new
   *                                    instance is created.
   *
   * @return  FormGenerator  The generator with other fields added.
   */
  public function generateCreateForm($table, FormGenerator $generator = null) {
    return $this->generateForm($table, 'create', $generator);
  }

  /**
   * Generate form elements suitable for updating the given record in the
   * given table.
   *
   * @param  string         $table      The table for which elements should be
   *                                    generated.
   * @param  FormGenerator  $generator  An existing FormGenerator that should
   *                                    be added to. If not provided, a new
   *                                    instance is created.
   *
   * @return  FormGenerator  The generator with other fields added.
   */
  public function generateUpdateForm($table, $primary_key, FormGenerator $generator = null) {
    $generator = $this->generateForm($table, 'update', $generator, $primary_key);
    // fetch row and populate form
    $primary_field = $this->schema[$table]['_primary'];
    $sql = 'SELECT * FROM `'.$table.'` WHERE `'.$primary_field.'` = ?';
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array($primary_key));
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (count($data)) {
      $generator->populateData(ArrayUtil::collapseKeys($data, self::FORM_PREFIX.'_args['.$table.']'));
    }
    return $generator;
  }

}
