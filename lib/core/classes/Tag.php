<?php
/**
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Contains static helper methods for rendering tags appropriately in the
 * current render context.
 *
 * Note that the attribute hints in the comments for each method largely
 * adhere to the strict dialect.
 *
 * @todo  Support IE conditional comments for various items.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Tag {

  /**
   * Static helper class.  Non-instantiable.
   */
  protected function __construct() {
  }

  # empty tag helpers {{{

  /**
   * Renders an AREA tag according to the current render context.
   *
   * Should only come in the MAP element.
   *
   * Attributes:
   * -----------
   * Required: alt
   * Optional: coords, href, nohref, shape
   * Standard: Yes
   * Event:    Yes
   *
   * @param   string  $alt    The alternate text.
   * @param   array   $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function area($alt = '', array $attrs = array()) {
    $attrs['alt'] = $alt;
    return self::renderTag('area', $attrs);
  }

  /**
   * Renders a BASE tag according to the current render context.
   *
   * Should only come in the HEAD element.
   *
   * Attributes:
   * -----------
   * Required: href
   * Optional: -
   * Standard: No
   * Event:    No
   *
   * @param   string  $href    The base href.
   *
   * @return  string
   */
  public static function base($href) {
    $attrs = array('href' => $href);
    return self::renderTag('base', $attrs);
  }

  /**
   * Renders a BR tag according to the current render context.
   *
   * Attributes:
   * -----------
   * Required: -
   * Optional: -
   * Standard: Partial (class, id, style, title)
   * Event:    No
   *
   * @param   array  $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function br(array $attrs = array()) {
    $allowed = array('class', 'id', 'style', 'title');
    $attrs = array_intersect_key($attrs, array_flip($allowed));
    return self::renderTag('br', $attrs);
  }

  /**
   * Renders a COL tag according to the current render context.
   *
   * Attributes:
   * -----------
   * Required: -
   * Optional: align, char, charoff, span, valign, width
   * Standard: Yes
   * Event:    Yes
   *
   * @param   array  $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function col(array $attrs = array()) {
    return self::renderTag('col', $attrs);
  }

  /**
   * Renders an HR tag according to the current render context.
   *
   * Attributes:
   * -----------
   * Required: -
   * Optional: -
   * Standard: Yes
   * Event:    Yes
   *
   * @param   array  $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function hr(array $attrs = array()) {
    return self::renderTag('hr', $attrs);
  }

  /**
   * Renders an IMG tag according to the current render context.
   *
   * Attributes:
   * -----------
   * Required: alt, src
   * Optional: height, ismap, longdesc, usemap, width
   * Standard: Yes
   * Event:    Yes
   *
   * @param   string  $src    The URL of an image.
   * @param   string  $alt    The alternate text.
   * @param   array   $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function img($src, $alt, array $attrs = array()) {
    $attrs['src'] = $src;
    $attrs['alt'] = $alt;
    return self::renderTag('img', $attrs);
  }

  /**
   * Renders an INPUT tag according to the current render context.
   *
   * Attributes:
   * -----------
   * Required: -
   * Optional: accept, alt, checked, disabled, maxlength, (name), readonly, size,
   *           src, (type), (value)
   * Standard: Yes
   * Event:    Yes
   *
   * @param   string  $type   The type of the input element.
   * @param   string  $name   The name of the input element.
   * @param   string  $value  The initial value of the input element.
   * @param   array   $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function input($type, $name, $value, array $attrs = array()) {
    $attrs['type']  = strtolower($type);
    $attrs['name']  = $name;
    $attrs['value'] = $value;
    if ($attrs['type'] !== 'file') {
      unset($attrs['accept']);
    }
    if ($attrs['type'] !== 'image') {
      unset($attrs['align'], $attrs['alt'], $attrs['src']);
    }
    if ($attrs['type'] !== 'checkbox' || $attrs['type'] !== 'radio') {
      unset($attrs['checked']);
    }
    if ($attrs['type'] !== 'text' || $attrs['type'] !== 'password') {
      unset($attrs['maxlength'], $attrs['readonly']);
    }
    return self::renderTag('input', $attrs);
  }

  /**
   * Renders a LINK tag according to the current render context.
   *
   * Attributes:
   * -----------
   * Required: -
   * Optional: charset, (href), hreflang, media, rel, rev, (type)
   * Standard: Yes
   * Event:    Yes
   *
   * @param   string  $type   The type of the resource being linked to.
   * @param   string  $href   The URL of the resource being linked to.
   * @param   array   $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function link($href, $type, array $attrs = array()) {
    $attrs = array_merge(array('type' => $type), $attrs, array('href' => $href));
    return self::renderTag('link', $attrs);
  }

  /**
   * Renders a META tag according to the current render context.
   *
   * Attributes:
   * -----------
   * Required: content
   * Optional: ( http-equiv | name, scheme )
   * Standard: Partial (dir, lang, xml:lang)
   * Event:    No
   *
   * @param   string   $name     The name of the metadata.
   * @param   string   $content  The meta content.
   * @param   boolean  $http     Whether to take $name as 'http-equiv' (true) or
   *                            'name' (false).
   * @param   array    $attrs    An associative array of additional attributes.
   *
   * @return  string
   */
  public static function meta($name, $content, $http = false, array $attrs = array()) {
    $key = ($http ? 'http-equiv' : 'name');
    $attrs = array_merge(array(
      $key      => $name,
      'content' => $content,
    ), $attrs);
    if (!isset($attrs['name'])) {
      unset($attrs['scheme']);
    }
    return self::renderTag('meta', $attrs);
  }

  /**
   * Renders a PARAM tag according to the current render context.
   *
   * Attributes:
   * -----------
   * Required: name
   * Optional: type, value, valuetype
   * Standard: Partial (id)
   * Event:    No
   *
   * @param   string   $name     The name of the parameter.
   * @param   array    $attrs    An associative array of addional attributes.
   *
   * @return  string
   */
  public static function param($name, array $attrs = array()) {
    $allowed = array('id', 'type', 'value', 'valuetype');
    $attrs = array_intersect_key($attrs, array_flip($allowed));
    $attrs = array_merge(array('name' => $name), $attrs);
    return self::renderTag('param', $attrs);
  }

  /**
   * Renders a WBR tag according to the current render context.
   *
   * Note:  WBR is an unofficial tag not present in W3C standards, but is
   *        included because optional linebreak support between browsers is
   *        somewhat flakey.
   *        WBR functions the same as a zero width space (&#8203;)
   *
   * @param   array  $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function wbr(array $attrs = array()) {
    return self::renderTag('wbr', $attrs);
  }

  # }}} empty tag helpers

  # inline content helpers {{{

  /**
   * Renders a SCRIPT tag according to the current render context.
   *
   * This script tag is provided for including scripts in the BODY of the page
   * and not the head.  Thus it can take in content for an inline script or a
   * source attribute.  The source attribute will take precedence.
   *
   * @param   string  $type     The MIME type of the script.
   * @param   string  $content  The inline content.
   * @param   array   $attrs    An associative array of addional attributes.
   *
   * @return  string
   */
  public static function script($type, $content = null, array $attrs = array()) {
    $attrs = array_merge(array('type' => $type), $attrs);
    if (isset($attrs['src'])) $content = '';
    return self::renderTag('script', $attrs, $content);
  }

  /**
   * Renders a STYLE tag according to the current render context.
   *
   * This is provided for inline stylesheets.
   * source attribute.  The source attribute will take precedence.
   *
   * @param   string  $content  The inline content.
   * @param   array   $attrs    An associative array of addional attributes.
   *
   * @return  string
   */
  public static function style($content = null, array $attrs = array()) {
    if (!isset($attrs['type'])) {
      $attrs = array_merge(array('type' => RenderContext::CONTENT_CSS), $attrs);
    }
    return self::renderTag('style', $attrs, $content);
  }

  # }}} inline content helpers

  # Internet Explorer conditional comments {{{

  /**
   * Renders content wrapped by an Internet Explorer conditional comment with
   * a provided expression.
   *
   * For more information on expression syntax, see:
   *   http://msdn.microsoft.com/en-us/library/ms537512%28VS.85%29.aspx#syntax
   *
   * @param   string   $expression  The condition to check for.
   * @param   string   $content     The content to wrap inside the comment.
   * @param   boolean  $newline     Put conditional comment tags on new lines.
   * @param   boolean  $revealed    Use a revealed comment
   *                                 - default = hidden comment (false).
   *
   * @return  HTML
   */
  public static function ieConditionalComment($expression, $content, $newline = false, $revealed = false) {
    $c = '';
    $c .= '<!'.($revealed ? '' : '--').'[if '.$expression.']>';
    if ($newline) $c .= PHP_EOL;
    $c .= $content;
    if ($newline) $c .= PHP_EOL;
    $c .= '<![endif]'.($revealed ? '' : '--').'>'.PHP_EOL;
    return $c;
  }

  # }}} Internet Explorer conditional comments

  /**
   * Renders a tag according the the current render context.
   *
   * Note that content is <b>never</b> escaped. Specifying <tt>false</tt> for the content will force
   * an object tag in XML-based languages. An object tag will also always be generated for XHTML tags
   * which cannot contain content.
   *
   * @param   string  $tag      The tag to render.
   * @param   array   $attrs    An associative array of additional attributes.
   * @param   string  $content  The inline content.
   *
   * @return  string
   *
   * @throws UnexpectedValueException
   */
  public static function renderTag($tag, array $attrs = array(), $content = null) {
    $tag = strtolower($tag);

    # Check whether we need to account for XML.
    $ctx = RenderContext::getGlobalContext();
    $is_xml = $ctx->isXMLSyntax();

    $r = '<'.$tag;
    foreach ($attrs as $k => $v) {
      if ($v === false || $k[0] === '_') {
        // attributes which should never be output
        continue;
      }
      if ($v === true) {
        // handle checked="checked", etc
        $v = $k;
      }
      $r .= ' '.strtolower($k).'="'.String::escape($v).'"';
    }
    if ($is_xml && ($content === false || self::isAlwaysEmpty($tag))) {
      $r .= ' />';
    } else {
      $r .= '>';
    }
    if (!is_null($content) && $content !== false) {
      if ($content !== '') {
        if ($is_xml && self::isImpliedCData($tag)) $r .= PHP_EOL.'//<![CDATA['.PHP_EOL;
        $r .= $content;
        if ($is_xml && self::isImpliedCData($tag)) $r .= PHP_EOL.'//]]>'.PHP_EOL;
      }
      $r .= '</'.$tag.'>';
    }
    return $r;
  }

  public static function isAlwaysEmpty($tag) {
    $tag = strtolower($tag);
    $is_xhtml = (RenderContext::getGlobalContext()->getLanguage() === RenderContext::LANG_XHTML);
    // don't know anything about non-XHTML
    if (!$is_xhtml) return false;
    switch ($tag) {
      case 'area':
      case 'base':
      case 'br':
      case 'col':
      case 'hr':
      case 'img':
      case 'input':
      case 'link':
      case 'meta':
      case 'param':
      case 'wbr':
        return true;
    }
    return false;
  }

  public static function isImpliedCData($tag) {
    $tag = strtolower($tag);
    $is_xhtml = (RenderContext::getGlobalContext()->getLanguage() === RenderContext::LANG_XHTML);
    // don't know anything about non-XHTML
    if (!$is_xhtml) return false;
    switch ($tag) {
      case 'script':
      case 'style':
        return true;
    }
    return false;
  }

}
