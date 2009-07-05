<?php

class NavigationMenu implements Renderable {
  protected $exactUrlClass  = null;
  protected $bestMatchClass = null;
  protected $besturl = null;
  protected $urls = array();
  protected $attrs = array();
  protected $ourUrl = '';

  public function __construct($urls = array()) {
    $this->urls = $urls;
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

  protected function getBestUrl() {
    if (!is_null($this->besturl)) {
      return $this->besturl;
    }
    $this->besturl = false;
    $cururl = $this->getOurUrl();
    $matchlen = -1;
    foreach ($this->urls as $u) {
      if (strlen($u[1]) > $matchlen && substr($cururl, 0, strlen($u[1])) == $u[1]) {
        $this->besturl = $u[1];
        $matchlen = strlen($u[1]);
      }
    }
    return $this->besturl;
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

  public function render() {
    $out = '';
    $out .= self::renderTag('ul', $this->attrs)."\n";
    $besturl = $this->getBestUrl();
    foreach ($this->urls as $url) {
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

      $out .= self::renderTag('li', $i_attrs, self::renderTag('a', $a_attrs, $a_text))."\n";
    }
    $out .= "</ul>\n";
    return $out;
  }

  public function __toString() {
    return $this->render();
  }
}
