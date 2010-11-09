<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.ui
 */

/**
 * Navigation menu component.
 *
 * This class will create a navigation menu as (nested) unordered lists. It
 * has the capability to automatically highlight the current or "best match"
 * item.
 *
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.ui
 */
class NavigationMenu implements Renderable {
  protected $exact_url_class  = null;
  protected $best_match_class = null;
  protected $besturl = array();
  protected $urls = array();
  protected $url_cache = array();
  protected $attrs = array();
  protected $our_url = '';
  protected $level_hints = false;
  protected $static_best = false;
  protected $static_exact = true;

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
   * @param  array  $urls  The URLs that should be part of this navigation menu.
   */
  public function __construct(array $urls = array()) {
    $this->urls = $urls;
    $this->refreshUrlCache();
  }

  /**
   * Return the class(es) added to the "best match" menu item at each level.
   *
   * @return  string
   */
  public function getBestUrlClass() {
    return $this->best_match_class;
  }

  /**
   * Set the class(es) added to the "best match" menu item at each level.
   *
   * @param  string|array  $c  The class or classes to be used.
   */
  public function setBestUrlClass($c) {
    if (is_array($c)) $c = implode(' ', $c);
    $this->best_match_class = $c;
  }

  /**
   * Return the class(es) added to the item that exactly matches the current URL.
   *
   * @return  string
   */
  public function getExactUrlClass() {
    return $this->exact_url_class;
  }

  /**
   * Set the class(es) added to the item that exactly matches the current URL.
   *
   * @param  string|array  $c  The class or classes to be used.
   */
  public function setExactUrlClass($c) {
    if (is_array($c)) $c = implode(' ', $c);
    $this->exact_url_class = $c;
  }

  /**
   * Returns whether or not level hints are enabled. Level hints are
   * additional classes added to the \t <ul> element in order to distinguish
   * between multiple levels. They are of the form "level0", "level1", etc.
   *
   * @return  bool
   */
  public function getStaticBest() {
    return $this->static_best;
  }

  /**
   * Enable or disable level hints. Level hints are additional classes added
   * to the \t <ul> element in order to distinguish between multiple levels.
   * They are of the form "level0", "level1", etc.
   *
   * @param  bool  $hint
   */
  public function setStaticBest($hint) {
    $this->static_best = $hint;
  }

  /**
   * Returns whether or not level hints are enabled. Level hints are
   * additional classes added to the \t <ul> element in order to distinguish
   * between multiple levels. They are of the form "level0", "level1", etc.
   *
   * @return  bool
   */
  public function getStaticExact() {
    return $this->static_exact;
  }

  /**
   * Enable or disable level hints. Level hints are additional classes added
   * to the \t <ul> element in order to distinguish between multiple levels.
   * They are of the form "level0", "level1", etc.
   *
   * @param  bool  $hint
   */
  public function setStaticExact($hint) {
    $this->static_exact = $hint;
  }

  /**
   * Return the URL used for matching.
   *
   * @return  string
   */
  public function getOurUrl() {
    if ($this->our_url) {
      return $this->our_url;
    }
    $url = explode('?', $_SERVER['REQUEST_URI'], 2);
    return $url[0];
  }

  /**
   * Override the URL used for matching and highlighting. This should only be
   * overridden for debugging purposes.
   *
   * @param  string  $url
   */
  public function setOurUrl($url) {
    $this->our_url = $url;
  }

  /**
   * Returns whether or not level hints are enabled. Level hints are
   * additional classes added to the \t <ul> element in order to distinguish
   * between multiple levels. They are of the form "level0", "level1", etc.
   *
   * @return  bool
   */
  public function getLevelHints() {
    return $this->level_hints;
  }

  /**
   * Enable or disable level hints. Level hints are additional classes added
   * to the \t <ul> element in order to distinguish between multiple levels.
   * They are of the form "level0", "level1", etc.
   *
   * @param  bool  $hint
   */
  public function setLevelHints($hint) {
    $this->level_hints = $hint;
  }

  public function getTopAttrs() {
    return $this->attrs;
  }

  public function setTopAttrs(array $attrs) {
    $this->attrs = $attrs;
  }

  /**
   * Refresh the cache of URLs.
   *
   * @param  array  $urls   The list of URLs to process.
   * @param  int    $level  The level of the tree being processed
   */
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

  /**
   * Find the "best match" URL at the given level.
   *
   * @param  int $level  The level to be seearched.
   *
   * @return  string
   */
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

  /**
   * Render a list of URLs and their child lists, applying highlight classes
   * to the \t <li> items as necessary.
   *
   * @param  array  $urls       The list of URLs to be rendered.
   * @param  array  $top_attrs  The attributes for the \t <ul> parent.
   * @param  int    $level      The current level in the URL tree.
   *
   * @return  string
   */
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
      if (!isset($url[1])) {
        $a_text = htmlentities($url[0], ENT_QUOTES, 'UTF-8');
        $content = Tag::renderTag('span', array('class'=>'nolink'), $a_text);
        if (isset($i_attrs['_children']) && count($i_attrs['_children'])) {
          $children = $i_attrs['_children'];
          $child_attrs = isset($i_attrs['_child_attrs']) ? $i_attrs['_child_attrs'] : array();
          $content .= "\n".$this->renderURLs($children, $child_attrs, $level + 1);
        }
        $out .= Tag::renderTag('li', $i_attrs, $content)."\n";

        continue;
      }

      $a_text = htmlentities($url[0], ENT_QUOTES, 'UTF-8');
      $a_attrs = array('href'=>$url[1]);
      $i_attrs = isset($url[2]) ? $url[2] : array();

      $i_class = isset($i_attrs['class']) ? array($i_attrs['class']) : array();

      if (isset($i_attrs['accesskey'])) {
        $a_attrs['accesskey'] = &$i_attrs['accesskey'];
        unset($i_attrs['accesskey']);
      }

      $static_link = false;

      if (!is_null($this->exact_url_class) && $url[1] == $cururl) {
        $i_class[] = $this->exact_url_class;
        $static_link |= $this->static_exact;

      } elseif (!is_null($this->best_match_class)) {
        if (
          ($besturl == false && isset($i_attrs['_default']) && $i_attrs['_default']) ||
          ($url[1] == $besturl)
        ) {
          $i_class[] = $this->best_match_class;
          $static_link |= $this->static_best;
        }
      }

      if (count($i_class)) {
        $i_attrs['class'] = implode(' ', $i_class);
      }

      if ($static_link) {
        $content = Tag::renderTag('span', array(), $a_text);
        if (isset($i_attrs['_children']) && count($i_attrs['_children'])) {
          $children = $i_attrs['_children'];
          $child_attrs = isset($i_attrs['_child_attrs']) ? $i_attrs['_child_attrs'] : array();
          $content .= "\n".$this->renderURLs($children, $child_attrs, $level + 1);
        }

      } else {
        $content = Tag::renderTag('a', $a_attrs, $a_text);
        if (isset($i_attrs['_children']) && count($i_attrs['_children'])) {
          $children = $i_attrs['_children'];
          $child_attrs = isset($i_attrs['_child_attrs']) ? $i_attrs['_child_attrs'] : array();
          $content .= "\n".$this->renderURLs($children, $child_attrs, $level + 1);
        }
      }
      $out .= Tag::renderTag('li', $i_attrs, $content)."\n";
    }
    $out .= "</ul>\n";
    return $out;
  }

  /**
   * Render this navigation menu using the current render context and return it as a string.
   *
   * @return  string
   */
  public function render() {
    return $this->renderURLs($this->urls, $this->attrs);
  }

  /**
   * Render this navigation menu using the current render context and return it as a string.
   *
   * @return  string
   * @see     NavigationMenu::render()
   */
  public function __toString() {
    return $this->render();
  }
}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
