<?php

class NavigationMenu implements Renderable {
  protected $exactUrlClass  = null;
  protected $bestMatchClass = null;
  protected $besturl = null;
  protected $urls = array();
  protected $url_cache = array();
  protected $attrs = array();
  protected $ourUrl = '';
  protected $levelHints = false;

  public function __construct($urls = array()) {
    $this->urls = $urls;
    $this->refreshUrlCache();
  }

  public function setBestUrlClass($c) {
    $this->bestMatchClass = $c;
  }

  public function setOurUrl($url) {
    $this->ourUrl = $url;
  }

  public function getOurUrl() {
    if ($this->ourUrl) {
      return $this->ourUrl;
    }
    return $_SERVER['REQUEST_URI'];
  }

  public function setLevelHints($hint) {
    $this->levelHints = $hint;
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

  protected static function renderTag($tag, array $attrs = null, $content = null, array $ignoreAttrs = null) {
    if (is_null($attrs)) {
      $attrs = array();
    }
    if (!is_null($ignoreAttrs) && count($ignoreAttrs)) {
      $attrs = array_diff_key($attrs, array_flip($ignoreAttrs));
    }

    $out = '<'.$tag;
    foreach ($attrs as $k=>$v) {
      if ($v === false || substr($k, 0, 1)=='_') {
        continue;
      }
      if ($v===true) {
        $v = $k;
      }
      $out .= ' '.htmlentities($k, ENT_QUOTES, 'UTF-8').'="'.htmlentities($v, ENT_QUOTES, 'UTF-8').'"';
    }
    $out .= (RenderContext::getGlobalContext()->getLanguage() == RenderContext::LANG_XHTML && $content===false) ? " />" : ">";
    if (!is_null($content) && $content !== false) {
      $out .= $content;
      $out .= '</'.$tag.'>';
    }

    return $out;
  }

  protected function renderURLs($urls, $top_attrs, $level=0) {
    $out = '';
    if ($this->levelHints) {
      if (isset($top_attrs['class'])) {
        $top_attrs['class'] .= ' level'.$level;
      } else {
        $top_attrs['class'] = 'level'.$level;
      }
    }
    $out .= self::renderTag('ul', $top_attrs)."\n";
    $besturl = $this->getBestUrl($level);
    foreach ($urls as $url) {
      $a_text = htmlentities($url[0], ENT_QUOTES, 'UTF-8');
      $a_attrs = array('href'=>$url[1]);
      $i_attrs = isset($url[2]) ? $url[2] : array();

      $i_class = isset($i_attrs['class']) ? array($i_attrs['class']) : array();

      if (!is_null($this->bestMatchClass)) {
        if (
          ($besturl == false && isset($i_attrs['_default']) && $i_attrs['_default']) ||
          ($url[1] == $besturl)
        ) {
          $i_class[] = $this->bestMatchClass;
        }
      }

      if (count($i_class)) {
        $i_attrs['class'] = implode(' ', $i_class);
      }

      $content = self::renderTag('a', $a_attrs, $a_text);
      if (isset($i_attrs['_children']) && count($i_attrs['_children'])) {
        $children = $i_attrs['_children'];
        $child_attrs = isset($i_attrs['_child_attrs']) ? $i_attrs['_child_attrs'] : array();
        $content .= "\n".$this->renderURLs($children, $child_attrs, $level + 1);
      }
      $out .= self::renderTag('li', $i_attrs, $content)."\n";
    }
    $out .= "</ul>\n";
    return $out;
  }

  public function render() {
    return $this->renderURLs($this->urls, $this->attrs);
  }

  public function __toString() {
    return $this->render();
  }
}
