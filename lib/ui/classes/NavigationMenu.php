<?php
/**
 * @package JerityUI
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

/**
 * Navigation menu component.
 *
 * This class will create a navigation menu as (nested) unordered lists. It
 * has the capability to automatically highlight the current or "best match"
 * item.
 *
 * @package JerityUI
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
class NavigationMenu implements Renderable {
  protected $exact_url_class  = null;
  protected $best_match_class = null;
  protected $besturl = null;
  protected $urls = array();
  protected $url_cache = array();
  protected $attrs = array();
  protected $our_url = '';
  protected $level_hints = false;

  /**
   * Create a navigation menu from an array of URLs.
   *
   * Each array element should be an array of the form:
   *  - Title
   *  - URL (or \t null)
   *  - Optional associative array of attributes to apply to the \t <li>
   *  element.
   *
   * There are (currently) two special attributes. The \t _default attribute
   * specifies a default item to be highlighted if no others match. The \t
   * _children attribute can contain a further array of URLs with each item in
   * the same form.
   *
   * @param array $urls The URLs that should be part of this navigation menu.
   */
  public function __construct(array $urls = array()) {
    $this->urls = $urls;
    $this->refreshUrlCache();
  }

  public function getBestUrlClass() {
    return $this->best_match_class;
  }

  public function setBestUrlClass($c) {
    $this->best_match_class = $c;
  }

  public function getExactUrlClass() {
    return $this->exact_url_class;
  }

  public function setExactUrlClass($c) {
    $this->exact_url_class = $c;
  }

  public function getOurUrl() {
    if ($this->our_url) {
      return $this->our_url;
    }
    return $_SERVER['REQUEST_URI'];
  }

  public function setOurUrl($url) {
    $this->our_url = $url;
  }

  public function getLevelHints() {
    return $this->level_hints;
  }

  public function setLevelHints($hint) {
    $this->level_hints = $hint;
  }

  protected function refreshUrlCache($urls = null, $level = 0) {
    if (is_null($urls) && $level == 0) {
      $this->url_cache = array();
    }
    if (is_null($urls)) {
      $urls = $this->urls;
    }
    foreach ($urls as $u) {
      $this->url_cache[$level][] = $u[1];
      if (isset($u[2]) && is_array($u[2]) && isset($u[2]['_children']) && is_array($u[2]['_children'])) {
        $this->refreshUrlCache($u[2]['_children'], $level + 1);
      }
    }
  }

  protected function getBestUrl($level = 0) {
    if (isset($this->besturl[$level]) && !is_null($this->besturl[$level])) {
      return $this->besturl[$level];
    }
    $this->besturl[$level] = false;
    $cururl = $this->getOurUrl();
    $matchlen = -1;
    foreach ($this->url_cache[$level] as $u) {
      if (strlen($u) <= strlen($cururl) && strlen($u) > $matchlen && preg_match('!^'.preg_quote(rtrim($u,'/')).'(/|$)!', $cururl)) {
        $this->besturl[$level] = $u;
        $matchlen = strlen($u);
      }
    }
    return $this->besturl[$level];
  }

  protected function renderURLs($urls, $top_attrs, $level=0) {
    $out = '';
    if ($this->level_hints) {
      if (isset($top_attrs['class'])) {
        $top_attrs['class'] .= ' level'.$level;
      } else {
        $top_attrs['class'] = 'level'.$level;
      }
    }
    $out .= Tag::renderTag('ul', $top_attrs)."\n";
    $cururl = $this->getOurUrl();
    $besturl = $this->getBestUrl($level);
    foreach ($urls as $url) {
      $a_text = htmlentities($url[0], ENT_QUOTES, 'UTF-8');
      $a_attrs = array('href'=>$url[1]);
      $i_attrs = isset($url[2]) ? $url[2] : array();

      $i_class = isset($i_attrs['class']) ? array($i_attrs['class']) : array();

      if (!is_null($this->exact_url_class) && $url[1] == $cururl) {
        $i_class[] = $this->exact_url_class;

      } elseif (!is_null($this->best_match_class)) {
        if (
          ($besturl == false && isset($i_attrs['_default']) && $i_attrs['_default']) ||
          ($url[1] == $besturl)
        ) {
          $i_class[] = $this->best_match_class;
        }
      }

      if (count($i_class)) {
        $i_attrs['class'] = implode(' ', $i_class);
      }

      $content = Tag::renderTag('a', $a_attrs, $a_text);
      if (isset($i_attrs['_children']) && count($i_attrs['_children'])) {
        $children = $i_attrs['_children'];
        $child_attrs = isset($i_attrs['_child_attrs']) ? $i_attrs['_child_attrs'] : array();
        $content .= "\n".$this->renderURLs($children, $child_attrs, $level + 1);
      }
      $out .= Tag::renderTag('li', $i_attrs, $content)."\n";
    }
    $out .= "</ul>\n";
    return $out;
  }

  /**
   * Render this navigation menu using the current render context and return it as a string.
   *
   * @return string
   */
  public function render() {
    return $this->renderURLs($this->urls, $this->attrs);
  }

  /**
   * Render this navigation menu using the current render context and return it as a string.
   *
   * @return string
   * @see NavigationMenu::render()
   */
  public function __toString() {
    return $this->render();
  }
}
