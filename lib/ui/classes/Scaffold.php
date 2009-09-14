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
    $this->db = $db;
    $this->schema = $this->transformSchema($schema);
  }

  /**
   * Normalise the schema and pull out important fields (e.g. primary key and
   * display field).
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

      if (isset($schema[$table]['fields']) && is_array($schema[$table]['fields']) && count($schema[$table]['fields'])) {
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

      } else {
        // fetch from DB

        try {
          $mode = $this->db->getAttribute(PDO::ATTR_ERRMODE);
          $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $sql = 'DESCRIBE `'.$table.'`';
          $stmt = $this->db->prepare($sql);
          $stmt->execute();
          while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Field, Type, 'Key' => 'PRI'
            $data = array();
            $data[0] = $r['Type'];
            $data[0] = preg_replace('/^(.*).[0-9]+. unsigned/', 'u\1', $data[0]);
            if ($r['Key'] === 'PRI') {
              $data[1] = 'primary';
              $schema[$table]['_primary'] = $r['Field'];
            } else {
              $data[1] = '';
            }
            if (!isset($schema[$table]['_display']) && preg_match('/^(?:name|title|description)$/i', $r['Field'])) {
              $schema[$table]['_display'] = $r['Field'];
            }
            $data[2] = null;

            $schema[$table]['fields'][$r['Field']] = $data;
          }
        } catch (Exception $e) {
          $this->db->setAttribute(PDO::ATTR_ERRMODE, $mode);
          var_dump('Database error: '.$e->getMessage());
        }
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
   *
   * @return  boolean  Whether actions were successfully processed.
   */
  public function processActions() {
    if (!isset($_POST[self::FORM_PREFIX.'_action'])) {
      return null;
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
                $result = $stmt->execute(array($value));
              }
            } else {
              $result = $stmt->execute(array($values));
            }
          }
          break;
        default:
      }

      $this->db->setAttribute(PDO::ATTR_ERRMODE, $mode);
    } catch (Exception $e) {
      $this->db->setAttribute(PDO::ATTR_ERRMODE, $mode);
      var_dump('Database error: '.$e->getMessage());
      return false;
    }
    return true;
  }

  /**
   * Create and output all HTML up to the beginning of the content, including a title.
   *
   * @param  string  $title  The title of the page, also output in an H1 at the top.
   */
  protected function outputPageHeader($title) {
    $title = String::escapeHTML($title);
    echo <<<EOHTML
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="generator" content="Jerity Scaffold Generator">
<title>Jerity Scaffold :: {$title}</title>
</head>
<body>
<h1>{$title}</h1>

EOHTML;
  }

  /**
   * Create and output all HTML that should go after the content.
   */
  protected function outputPageFooter() {
    echo <<<EOHTML
</body>
</html>
EOHTML;
  }

  /**
   * Create and return the URL for an action.
   *
   * @param   string  $action       The action to be added to the URL.
   * @param   string  $primary_key  The primary key value for the action.
   *
   * @return  string
   */
  protected function generateActionUrl($action, $primary_key=null) {
    $url = self::cleanUrl();
    if (strpos($url, '?') === false) {
      $url .= '?';
    } else {
      $url .= '&';
    }
    # not needed, as we don't clean the table from the URL in self::cleanUrl()
    #if (isset($_GET[self::FORM_PREFIX.'_table'])) {
    #  $url .= self::FORM_PREFIX.'_table='.rawurlencode($_GET[self::FORM_PREFIX.'_table']).'&';
    #}
    $url .= self::FORM_PREFIX.'_method='.rawurlencode($action);
    if (!is_null($primary_key)) {
      $url .= '&'.self::FORM_PREFIX.'_primary='.rawurlencode($primary_key);
    }
    return $url;
  }

  /**
   * Generate a page suitable for creating a record in the given table.
   *
   * @param   string  $table  The table in which a record should be created.
   *
   * @return  string
   */
  public function generateCreatePage($table) {
    if (is_null($table)) {
      return 'Need a table to create things.';
    }
    ob_start();
    $this->outputPageHeader('New item in '.$table);
    $f = $this->generateCreateForm($table);
    $f->addSubmit('', 'Create');
    echo $f->render();
    echo '<a href="'.String::escape(self::cleanUrl()).'">Cancel</a>', "\n";
    $this->outputPageFooter();
    return ob_get_clean();
  }

  /**
   * Generate a page suitable for updating a record in the given table.
   *
   * Note: takes the primary key for the record to update from the request.
   *
   * @param   string  $table  The table in which a record should be updated.
   *
   * @return  string
   */
  public function generateUpdatePage($table) {
    if (is_null($table)) {
      return 'Need a table to update things.';
    }
    if (!isset($_GET[self::FORM_PREFIX.'_primary'])) {
      return 'Need a primary key to update things.';
    }
    $primary_key = $_GET[self::FORM_PREFIX.'_primary'];
    ob_start();
    $this->outputPageHeader('Update item in '.$table);
    $f = $this->generateUpdateForm($table, $primary_key);
    $f->addSubmit('', 'Update');
    echo $f->render();
    echo '<a href="'.String::escape(self::cleanUrl()).'">Cancel</a>', "\n";
    $this->outputPageFooter();
    return ob_get_clean();
  }

  /**
   * Generate a page to confirm deleting a record in the given table.
   *
   * Note: takes the primary key for the record to delete from the request.
   *
   * @param   string  $table  The table in which a record should be deleted.
   *
   * @return  string
   */
  public function generateDeleteConfirmPage($table) {
    if (is_null($table)) {
      return 'Need a table to delete things.';
    }
    if (!isset($_GET[self::FORM_PREFIX.'_primary'])) {
      return 'Need a primary key to delete things.';
    }
    $primary_key = $_GET[self::FORM_PREFIX.'_primary'];
    ob_start();
    $this->outputPageHeader('Delete item in '.$table);
    echo '<p>Are you sure you want to delete this item?</p>', "\n";
    $data = $this->fetchRow($table, $primary_key);
    echo "<ul>\n";
    foreach ($data as $k => $v) {
      echo '<li>'.String::escape($k).': '.String::escape($v)."</li>\n";
    }
    echo "</ul>\n";
    $f = new FormGenerator();
    $f->addHidden(self::FORM_PREFIX.'_action', 'delete');
    $f->addHidden(self::FORM_PREFIX.'_args['.$table.']['.$this->schema[$table]['_primary'].']', $primary_key);
    $f->addSubmit('', 'Delete');
    echo $f->render();
    echo '<a href="'.String::escape(self::cleanUrl()).'">Cancel</a>', "\n";
    $this->outputPageFooter();
    return ob_get_clean();
  }

  /**
   * Generate a list of records in the given table.
   *
   * If the given table is null, then all tables defined in the schema will be
   * listed.
   *
   * @param   string  $table  The table from which records should be listed.
   *
   * @return  string
   */
  public function generateListPage($table) {
    ob_start();
    if (is_null($table)) {
      // TODO: list all tables
      $this->outputPageHeader('All tables');
      # $_GET[self::FORM_PREFIX.'_table']

    } else {
      // specific table
      $this->outputPageHeader('Table '.$table);
      $db = $this->db;
      $primary_field = $this->schema[$table]['_primary'];
      // TODO: join with belongsTo tables (and hasMany?)
      $sql = 'SELECT * FROM `'.$table.'` ORDER BY `'.$primary_field.'`';
      $stmt = $db->prepare($sql);
      $stmt->execute();
      if ($stmt->rowCount()) {
        $header = false;
        echo "<table>\n<thead>\n<tr>";
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        foreach (array_keys($row) as $col) {
          echo '<th>'.String::escapeHTML($col).'</th>';
        }
        echo '<th class="actions">Actions</th>';
        echo "</tr>\n</thead>\n<tfoot></tfoot>\n<tbody>\n";
        $i = 0;
        do {
          echo '<tr class="zebra'.($i++ % 2).'">';
          foreach ($row as $k => $v) {
            if ($k === $primary_field) {
              echo '<td class="primary">'.String::escapeHTML($v).'</td>';
            } else {
              echo '<td>'.String::escapeHTML($v).'</td>';
            }
          }
          echo '<td class="actions"><a href="'.$this->generateActionUrl('update', $row[$primary_field]).'">Edit</a> <a href="'.$this->generateActionUrl('delete', $row[$primary_field]).'">Delete</a></th>';
          echo "</tr>\n";
        } while ($row = $stmt->fetch(PDO::FETCH_ASSOC));
        echo "</tbody>\n</table>\n";
      } else {
        echo '<p>No rows to display.</p>', "\n";
      }
      echo '<p><a href="'.$this->generateActionUrl('create').'">New item</a></p>', "\n";
    }
    $this->outputPageFooter();
    return ob_get_clean();
  }

  /**
   * Generate a full page of the specified type.
   *
   * @param   string  $table  The table which the page should be based on.
   * @param   string  $type   The type of page: create, update, delete or list.
   *
   * @return  string
   */
  public function generatePage($table, $type) {
    switch ($type) {
      case 'create':
        return $this->generateCreatePage($table);
        break;
      case 'update':
        return $this->generateUpdatePage($table);
        break;
      case 'delete':
        return $this->generateDeleteConfirmPage($table);
        break;
      case 'list':
      default:
        return $this->generateListPage($table);
        break;
    }
  }

  /**
   * Clean the scaffold method and primary key value arguments from the query string of a URL.
   *
   * @param   string  $url  The URL to be cleaned. If empty, uses the current URL.
   *
   * @return  string
   */
  protected static function cleanUrl($url='') {
    if ($url === '') {
      $url = $_SERVER['REQUEST_URI'];
    }
    if (isset($_GET[self::FORM_PREFIX.'_method'])) {
      $url = preg_replace('/(\?.*)'.self::FORM_PREFIX.'_method=[^&]+/', '\1', $url);
      if (isset($_GET[self::FORM_PREFIX.'_primary'])) {
        $url = preg_replace('/(\?.*)'.self::FORM_PREFIX.'_primary=[^&]+/', '\1', $url);
      }
      $url = preg_replace('/[?&]+&/', '&', $url);
      $url = preg_replace('/[?&]$/',  '',   $url);
    }

    return $url;
  }

  /**
   * Do everything required for the scaffold, including processing actions and
   * creating/displaying the page.
   *
   * @param   array   $schema  The schema for the database.
   * @param   PDO     $db      The database connection to use.
   * @param   string  $table   The table to be used.
   *
   * @return  string  The generated page to be displayed.
   */
  public static function doScaffold(array $schema, PDO $db, $table=null) {
    $scaffold = new Scaffold($schema, $db);
    if ($scaffold->processActions() === true) {
      $url = self::cleanUrl();
      Redirector::redirect($url);
    }

    $action = isset($_GET[self::FORM_PREFIX.'_method']) ? $_GET[self::FORM_PREFIX.'_method'] : 'list';
    if (is_null($table) && isset($_GET[self::FORM_PREFIX.'_table'])) {
      $table = $_GET[self::FORM_PREFIX.'_table'];
    }

    return $scaffold->generatePage($table, $action);
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
          return $generator->addInput($formname, $field, array('maxlength'=>$length, 'size'=>min($length, 40)));
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
        return $generator->addTextarea($formname, $field, array('rows'=>7, 'cols'=>60, 'style'=>'vertical-align: bottom;'));
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
   * Fetch a row with the given primary key from the given table and return it.
   *
   * @param  string  $table        The table from which to fetch a row.
   * @param  string  $primary_key  The primary key value of the row to fetch.
   *
   * @return  array
   */
  protected function fetchRow($table, $primary_key) {
    $primary_field = $this->schema[$table]['_primary'];
    $sql = 'SELECT * FROM `'.$table.'` WHERE `'.$primary_field.'` = ?';
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array($primary_key));
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
    $data = $this->fetchRow($table, $primary_key);
    if (count($data)) {
      $generator->populateData(ArrayUtil::collapseKeys($data, self::FORM_PREFIX.'_args['.$table.']'));
    }
    return $generator;
  }

}
