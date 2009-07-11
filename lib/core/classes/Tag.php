<?php
/**
 * @package  JerityCore
 * @author  Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Contains static helper methods for rendering tags appropriately in the
 * current render context.
 *
 * Note that the attribute hints in the comments for each method largely
 * adhere to the strict dialect.
 *
 * @package  JerityCore
 * @author  Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Tag {

  /**
   * Static helper class.  Non-instantiable.
   */
  protected function __construct() {
  }

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
   * @param  string  $alt    The alternate text.
   * @param  array   $attrs  An associative array of addional attributes.
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
   * @param  string  $href    The base href.
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
   * @param  array  $attrs  An associative array of addional attributes.
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
   * @param  array  $attrs  An associative array of addional attributes.
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
   * @param  array  $attrs  An associative array of addional attributes.
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
   * @param  string  $src    The URL of an image.
   * @param  string  $alt    The alternate text.
   * @param  array   $attrs  An associative array of addional attributes.
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
   * @param  string  $type   The type of the input element.
   * @param  string  $name   The name of the input element.
   * @param  string  $value  The initial value of the input element.
   * @param  array   $attrs  An associative array of addional attributes.
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
   * @param  string  $type   The type of the resource being linked to.
   * @param  string  $href   The URL of the resource being linked to.
   * @param  array   $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function link($href, $type, array $attrs = array()) {
    $attrs['href'] = $href;
    $attrs['type'] = $type;
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
   * @param  string   $name     The name of the metadata.
   * @param  string   $content  The meta content.
   * @param  boolean  $http     Whether to take $name as 'http-equiv' (true) or
   *                            'name' (false).
   * @param  array    $attrs    An associative array of addional attributes.
   *
   * @return  string
   */
  public static function meta($name, $content, $http = false, array $attrs = array()) {
    if ($http) {
      $attrs['http-equiv'] = $name;
    } else {
      $attrs['name'] = $name;
    }
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
   * @param  string   $name     The name of the parameter.
   * @param  array    $attrs    An associative array of addional attributes.
   *
   * @return  string
   */
  public static function param($name, array $attrs = array()) {
    $allowed = array('id', 'type', 'value', 'valuetype');
    $attrs = array_intersect_key($attrs, array_flip($allowed));
    $attrs['name'] = $name;
    return self::renderTag('param', $attrs);
  }

  /**
   * Renders a SCRIPT tag according to the current render context.
   *
   * This script tag is provided for including scripts in the BODY of the page
   * and not the head.  Thus it can take in content for an inline script or a
   * source attribute.  The source attribute will take precedence.
   *
   * @param  string  $src      The source of the content.
   * @param  string  $type     The MIME type of the script.
   * @param  string  $content  The inline content.
   * @param  array   $attrs    An associative array of addional attributes.
   *
   * @return  string
   */
  public static function script($type, $content = null, array $attrs = array()) {
    $attrs['type'] = $type;
    return self::renderTag('script', $attrs, $content);
  }

  /**
   * Renders a WBR tag according to the current render context.
   *
   * Note:  WBR is an unofficial tag not present in W3C standards, but is
   *        included because optional linebreak support between browsers is
   *        somewhat flakey.
   *        WBR functions the same as a zero width space (&#8203;)
   *
   * @param  array  $attrs  An associative array of addional attributes.
   *
   * @return  string
   */
  public static function wbr(array $attrs = array()) {
    return self::renderTag('wbr', $attrs);
  }

  /**
   * Renders a tag according the the current render context.
   *
   * @param  string  $tag      The tag to render.
   * @param  array   $attrs    An associative array of additional attributes.
   * @param  string  $content  The inline content.
   *
   * @return  string
   *
   * @throws UnexpectedValueException
   */
  protected static function renderTag($tag, array $attrs = array(), $content = null) {
    $tag = strtolower($tag);
    ksort($attrs);

    $r = '<'.$tag;
    foreach ($attrs as $k => $v) {
      $r .= ' '.strtolower($k).'="'.$v.'"';
    }
    if ($tag === 'script') {
      $r .= '>';
      if (!is_null($content) && !isset($attrs['src'])) {
        $r .= $content;
      }
      $r .= '</'.$tag.'>';
    } else {
      $ctx = RenderContext::getGlobalContext();
      switch ($ctx->getLanguage()) {
        case RenderContext::LANG_HTML:
        case RenderContext::LANG_MHTML:
        case RenderContext::LANG_TEXT:
          $r .= '>';
          break;
        case RenderContext::LANG_FBML:
        case RenderContext::LANG_XHTML:
        case RenderContext::LANG_WML:
        case RenderContext::LANG_XML:
          $r .= ' />';
          break;
        default:
          throw new UnexpectedValueException('Cannot generate tag for render context language: \''.$ctx->getLanguage().'\'');
      }
    }
    return $r;
  }

}
