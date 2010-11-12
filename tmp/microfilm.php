<?php
  /**
   * Microfilm Image Gallery
   * Microfilm -- An Image Gallery Plugin for Joomla! 1.5.x
   *
   * @version   0.1.0
   * @author    Nick Pope <microfilm [at] nickpope [dot] me [dot] uk>
   * @copyright Copyright (c) 2008 Nick Pope
   *
   * @todo Modify to use more of the Joomla! Framework API [?]
   */

  # -------------
  # Requirements:
  # -------------
  # Joomla! 1.5.x
  # PHP 5.x
  # GD 2.x (required for the thumbnail script)

  # No direct access to plugin.
  defined('_JEXEC') or die('Restricted access');
  # Import library dependencies.
  jimport('joomla.event.plugin');

  class plgContentMicrofilm extends JPlugin {

    /**
     * Keeps track of error status for the object.
     *
     * @var boolean
     * @access private
     */
    var $error_flag = false;

    /**
     * Keeps track of the number of images that couldn't be displayed.
     *
     * @var int
     * @access private
     */
    var $warn_image = 0;

    # Joomla! 1.5.x Paths

    var $base_dir = JPATH_SITE;

    var $base_url = null;

    # Plugin Information and Paths

    var $plugin_dir_rel = '/plugins/content/microfilm';

    var $plugin_dir_abs = null;

    var $plugin_name    = 'Microfilm';

    var $plugin_version = '0.1.0';

    /**
     * Constructor
     *
     * For PHP4 compatability, we must not use __constructor() for plugins
     * because func_get_args() returns a copy of all passed arguments, not
     * references.  This causes problems with cross-referencing necessary for
     * the observer design pattern.
     *
     * @param mixed &$subject
     * @see func_get_args()
     */
    function plgContentMicrofilm(&$subject) {
      parent::__construct($subject);
      $this->base_url = rtrim(JURI::base(), '/');
      $this->plugin_dir_abs = $this->base_url.$this->plugin_dir_rel;
      # Load parameters and languages.
      $this->_plugin = JPluginHelper::getPlugin('content', 'microfilm');
      $this->_params = new JParameter($this->_plugin->params);
      JPlugin::loadLanguage('plg_content_microfilm');
      # Add styles and scripts for gallery.
      $doc =& JFactory::getDocument();
      #$doc->addScript($this->plugin_dir_abs.'/slimbox/js/mootools.js');
      #$doc->addScript($this->plugin_dir_abs.'/slimbox/js/slimbox.js');
      $doc->addScript($this->plugin_dir_abs.'/slimbox2/js/jquery-1.3.1.min.js');
      $doc->addScript($this->plugin_dir_abs.'/slimbox2/js/slimbox2.js');
      $doc->addStyleSheet($this->plugin_dir_abs.'/slimbox2/css/slimbox2.css');
      $doc->addStyleSheet($this->plugin_dir_abs.'/microfilm.css');
      unset($doc);
    }

    /**
     * Displays an error message.
     *
     * @param string $msg
     */
    function error($msg) {
      $this->error_flag = true;
      $prefix = JText::_('ERROR_PREFIX');
      $text = JText::_($msg);
      return <<<EOHTML
<p class="mf_error"><strong>{$prefix}</strong>: {$text}</p>
EOHTML;
    }

    /**
     * Displays a warning message.
     *
     * @param string $msg
     */
    function warn($msg) {
      $prefix = JText::_('WARN_PREFIX');
      $text = JText::_($msg);
      return <<<EOHTML
<p class="mf_warn"><strong>{$prefix}</strong>: {$text}</p>
EOHTML;
    }

    /**
     * Modifies the content and replaces placeholders with the appropriate
     * markup for the gallery.
     *
     * @param mixed &$row
     * @param mixed &$params
     * @param mixed $limitstart
     */
    function onPrepareContent(&$row, &$params, $limitstart) {

      # Joomla! Version Check
      $version = new JVersion();
      if ($version->PRODUCT == 'Joomla!' && $version->RELEASE != '1.5') {
        echo $this->error('ERROR_JOOMLA_VERSION');
        return;
      }

      # GD2 Library Check
      if (extension_loaded('gd') && function_exists('gd_info')) {
        $info = gd_info();
        $info['GD Version'] = intval(preg_replace('/[^\.\d]+/', '', $info['GD Version']));
        if ($info['GD Version'] < 2)      echo $this->error('ERROR_GD_VERSION');
        if (!$info['GIF Create Support']) echo $this->error('ERROR_GD_SUPPORT_GIF');
        if (!$info['JPG Support'])        echo $this->error('ERROR_GD_SUPPORT_JPG');
        if (!$info['PNG Support'])        echo $this->error('ERROR_GD_SUPPORT_PNG');
        if ($this->error_flag) return;
      } else {
        echo $this->error('ERROR_GD_MISSING');
        return;
      }

      # Thumbnail Generation Parameters
      $thumb['width']     = $this->_params->get('thumb_width', 200);
      $thumb['height']    = $this->_params->get('thumb_height', 200);
      $thumb['quality']   = $this->_params->get('thumb_quality', 80);
      $thumb['method']    = $this->_params->get('thumb_method', 'scale');
      $thumb['nocache']   = $this->_params->get('thumb_nocache', 0);

      # Other Parameters
      $placeholder   = $this->_params->get('placeholder', 'gallery');
      $root_dir      = $this->_params->get('root_dir', '/images/stories');
      $def_caption   = $this->_params->get('def_caption', 1);
      $warn_empty    = $this->_params->get('warn_empty', 1);

      $placeholder_pattern = '#(?:<p>\W*?)?\{' . $placeholder . '\}(.*?)\{/' . $placeholder . '\}(?:\W*?</p>)?#s';
      $valid_image_exts = array('.gif', '.jpg', '.png');
      $valid_image_mime = array('image/gif', 'image/jpeg', 'image/png');

      $mf_gallery_count = 0;

      # Check that we have something to replace...
      if (!preg_match_all($placeholder_pattern, $row->text, $out, PREG_PATTERN_ORDER)) return;

      foreach ($out[1] as $image_dir) {
        $image_array = array();
        $image_count = 0;

        $image_dir_rel = '/'.trim($root_dir, '/').'/'.trim($image_dir, '/');
        $image_dir_abs = rtrim($this->base_dir, '/').$image_dir_rel;

        # Read directory contents and extract image information
        if (!($dh = @opendir($image_dir_abs))) {
          switch (true) {
            case (!file_exists($image_dir_abs)):   echo $this->error('ERROR_IMAGE_FOLDER_MISSING');     return;
            case (!is_readable($image_dir_abs)):   echo $this->error('ERROR_IMAGE_FOLDER_PERMISSIONS'); return;
            case (!is_executable($image_dir_abs)): echo $this->error('ERROR_IMAGE_FOLDER_PERMISSIONS'); return;
            default:                               echo $this->error('ERROR_IMAGE_FOLDER_UNKNOWN');     return;
          }
        }
        while (($file = readdir($dh)) !== false) {
          # Check extension (if wrong, may display incorrectly in browser)
          $ext = strtolower(substr($file, strrpos($file, '.')));
          if (!in_array($ext, $valid_image_exts)) continue;
          # Check image type (as image extension could be wrong)
          if (!($ii = @getimagesize($image_dir_abs.'/'.$file))) {
            if (!$this->warn_image) $html .= $this->warn('WARN_IMAGE_NOT_DISPLAYED');
            $this->warn_image++;
            continue;
          }
          if (!in_array($ii['mime'], $valid_image_mime)) {
            if (!$this->warn_image) $html .= $this->warn('WARN_IMAGE_NOT_DISPLAYED');
            $this->warn_image++;
            continue;
          }
          # Store image information
          $ii['filename'] = $file;
          $image_array[] = $ii;
          $image_count++;
        }
        closedir($dh);

        if (!$image_count) { # No images to display in this gallery
          if ($warn_empty) $html .= $this->warn('WARN_GALLERY_EMPTY');
        } else {
          # Sort images by filename
          foreach ($image_array as $k => $v) {
            $order[$k] = $v['filename'];
          }
          # XXX: Find a nicer solution...  Currently suppressing a warning.
          @array_multisort($order, SORT_ASC, SORT_REGULAR, $image_array);

          # Read in captions (if available)
          if ($labels = file($image_dir_abs.'/labels.txt')) {
            foreach ($labels as $label) {
              $part = split('\|', $label, 2);
              $captions[$part[0]] = preg_replace('/"/', '\'', trim($part[1]));
            }
          }

          $html = <<<EOHTML

<!-- [begin] {$this->plugin_name} {$this->plugin_version} -->
<div class="mf_gallery">

EOHTML;

          foreach ($image_array as $image) {
            $title = '';
            if (array_key_exists($image['filename'], $captions)) {
              $title = $captions[$image['filename']];
            } else if ($def_caption) {
              $title = $image['filename'];
            }
            JFilterOutput::cleanText($title);
            # XXX: Fix entities + quotes:
            #if ($title !== '') $title = '<p class="mf_caption">'.$title.'</p>';
            $alt = $image['filename'];
            JFilterOutput::cleanText($alt);
            $image_url = $image_dir_rel.'/'.$image['filename'];
            $thumb_url = $this->plugin_dir_abs."/thumbnail.php?img={$image_url}&".array_to_query_string($thumb);
            $html .= <<<EOHTML
<div class="mf_frame"><div class="mf_thumbnail"><a href="{$image_url}" rel="lightbox-mf_gallery-{$mf_gallery_count}" title="{$title}" alt="{$alt}"><img src="{$thumb_url}" width="{$thumb['width']}" height="{$thumb['height']}"></a></div></div>

EOHTML;
          }

          $html .= <<<EOHTML
</div>
<!--  [end]  {$this->plugin_name} {$this->plugin_version} -->

EOHTML;
        }

        $mf_gallery_count++;
        unset($image_array);
        $row->text = preg_replace($placeholder_pattern, $html, $row->text, 1);
      }
    }
  }

  function array_to_query_string($items) {
    $kvpair = create_function('$k,$v', 'return "$k=$v";');
    return implode('&', array_map($kvpair, array_keys($items), array_values($items)));
  }

  # vim:nowrap:ts=2:et
?>
