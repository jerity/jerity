<?php
/**
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

/**
 * A basic site template.
 *
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
class SiteTemplate extends Template {
  /**
   * The content to be rendered.
   *
   * @var mixed
   */
  protected $content = null;

  /**
   * Return the template directory appropriate to this template class.
   *
   * @return string
   */
  public static function getTemplateDir() {
    return self::getSiteTemplateDir();
  }

  public function getContent() {
    if (!$this->content) {
      $this->setContent('');
    }
    return $this->content;
  }

  public function setContent($content) {
    if (!is_object($content)) {
      $content = new SimpleContent($content);
    }
    $this->content = $content;
  }

  /**
   * Include a template with the given parameters.
   *
   * @param string $file  Path to the template.
   * @param array $params Template parameters, if any.
   * @return string
   *
   * @see Template::__construct()
   */
  public function useTemplate($file, array $params = array()) {
    $tpl = new self($file);
    $tpl->setParams($params);
    return $tpl->render();
  }

  /**
   * Render the item using the current global rendering context, and return it
   * as a string.
   *
   * @return string
   */
  public function render() {
    return call_user_func_array($this->templateRender, array($this->templateParams, $this->getContent()));
  }
}

?>
