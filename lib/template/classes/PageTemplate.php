<?php
/**
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

/**
 * A basic page template.
 *
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
class PageTemplate extends Template {
  protected $siteTemplate = null;

  /**
   * Return the template directory appropriate to this template class.
   *
   * @return string
   */
  public static function getTemplateDir() {
    return self::getPageTemplateDir();
  }

  protected function loadTemplate($f) {
    $f = $this->preLoadChecks($f);
    $_SITE_TEMPLATE = false;
    $_SITE = array();
    $_PARAMS = array();
    $_RENDER = null;
    include($f);
    $this->templateRender = $_RENDER;
    $this->templateParams = new TemplateVars($_PARAMS);
    if ($_SITE_TEMPLATE === false) $_SITE_TEMPLATE = 'default';
    $this->siteTemplate = new SiteTemplate($_SITE_TEMPLATE);
    $this->siteTemplate->setParams($_SITE);
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
  public static function useTemplate($file, array $params = array()) {
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
    $content = '';
    ob_start();
    $content = call_user_func_array($this->templateRender, array($this->templateParams));
    if (!$content) {
      $this->siteTemplate->setContent(ob_get_clean());
    } else {
      ob_end_clean();
      $this->siteTemplate->setContent($content);
    }
    unset($content);
    return $this->siteTemplate->render();
  }

  /**
   * Gets the site template used with this page template.
   *
   * @return SiteTemplate
   */
  public function getSiteTemplate() {
    return $this->siteTemplate;
  }

}

?>
