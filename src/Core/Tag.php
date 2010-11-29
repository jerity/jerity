<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

namespace Jerity\Core;

use \Jerity\Util\String;

/**
 * Contains static helper methods for rendering tags appropriately in the
 * current render context.
 *
 * Note that the attribute hints in the comments for each method largely
 * adhere to the strict dialect.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 *
 * @method  Tag  class(string $v)  Adds a class attribute to the element.
 * @method  Tag  height(int $v)    Adds a height attribute to the element.
 * @method  Tag  width(int $v)     Adds a width attribute to the element.
 *
 * @method  Tag  size(int $width, int $height)  Shorthand to add height and width attributes.
 */
class Tag implements ConditionalProxy, Renderable {

  ##############################################################################
  # standards compliance tables {{{

  /**
   * Table of void elements.
   *
   * These are elements that are considered void, i.e. cannot have content.
   */
  protected static $void_elements = array(
    RenderContext::LANG_HTML => array(
      4.01 => array(
        'area', 'base', 'br', 'col', 'hr', 'img', 'input', 'link', 'meta',
        'param',
      ),
      5 => array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 
        'keygen', 'link', 'meta', 'param', 'source', 'wbr',
      ),
    ),
    RenderContext::LANG_XHTML => array(
      1.0 => array(
        'area', 'base', 'br', 'col', 'hr', 'img', 'input', 'link', 'meta',
        'param',
      ),
      5 => array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 
        'keygen', 'link', 'meta', 'param', 'source', 'wbr',
      ),
    ),
  );

  /**
   * Tables of allowed elements.
   *
   * Note that XHTML 1.0 inherits allowed elements from HTML 4.01.
   *
   * @var  array
   *
   * @link  http://www.w3.org/TR/html401/appendix/changes.html#h-A.3.1.1  New Elements in HTML 4.0
   * @link  http://www.w3.org/TR/html5-diff/#new-elements                 New Elements in (X)HTML5
   */
  protected static $allowed_elements = array(
    RenderContext::LANG_HTML => array(
      '4.01' => array(
        'abbr', 'acronym', 'bdo', 'button', 'col', 'colgroup', 'del', 
        'fieldset', 'frame', 'frameset', 'iframe', 'ins', 'label', 'legend', 
        'noframes', 'noscript', 'object', 'optgroup', 'param', 'span', 'tbody', 
        'tfoot', 'thead', 'q',
      ),
      '5' => array(
        'section', 'article', 'aside', 'hgroup', 'header', 'footer', 'nav',
        'figure', 'figcaption', 'video', 'audio', 'source', 'embed', 'mark', 
        'progress', 'meter', 'time', 'ruby', 'rt', 'rp', 'wbr', 'canvas', 
        'command', 'details', 'summary', 'datalist', 'keygen', 'output',
      ),
    ),
    RenderContext::LANG_XHTML => array(
      '1.0' => array(
        'abbr', 'acronym', 'bdo', 'button', 'col', 'colgroup', 'del', 
        'fieldset', 'frame', 'frameset', 'iframe', 'ins', 'label', 'legend', 
        'noframes', 'noscript', 'object', 'optgroup', 'param', 'span', 'tbody', 
        'tfoot', 'thead', 'q',
      ),
      '5' => array(
        'section', 'article', 'aside', 'hgroup', 'header', 'footer', 'nav',
        'figure', 'figcaption', 'video', 'audio', 'source', 'embed', 'mark', 
        'progress', 'meter', 'time', 'ruby', 'rt', 'rp', 'wbr', 'canvas', 
        'command', 'details', 'summary', 'datalist', 'keygen', 'output',
      ),
    ),
  );

  /**
   * Tables of allowed attributes.
   *
   * Note that XHTML 1.0 inherits allowed attributes from HTML 4.01.
   *
   * @var  array
   *
   * @link  http://www.w3.org/TR/html401/index/attributes.html  New Attributes in HTML 4.0
   * @link  http://www.w3.org/TR/html5-diff/#new-attributes     New Attributes in (X)HTML5
   */
  protected static $allowed_attributes = array(
    RenderContext::LANG_HTML => array(
      '4.01' => array(
        'form' => array('accept', 'name'),
        'img'  => array('name'),
        'input' => array('ismap'),
      ),
      '5' => array(
        '*'        => array('aria-*', 'class', 'contenteditable', 'contextmenu', 'data-*', 'dir', 'draggable', 'hidden', 'id', 'lang', 'role', 'spellcheck', 'style', 'tabindex', 'title'),
        'a'        => array('media', 'target'),
        'area'     => array('hreflang', 'media', 'rel', 'target'),
        'base'     => array('target'),
        'button'   => array('autofocus', 'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget'),
        'fieldset' => array('disabled', 'form'),
        'form'     => array('novalidate'),
        'html'     => array('manifest'),
        'iframe'   => array('sandbox', 'seamless', 'srcdoc'),
        'input'    => array('autocomplete', 'autofocus', 'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget', 'list', 'max', 'min', 'multiple', 'pattern', 'placeholder', 'required', 'step'),
        'li'       => array('value'),
        'link'     => array('sizes'),
        'meta'     => array('charset'),
        'menu'     => array('label', 'type'),
        'ol'       => array('reversed', 'start'),
        'output'   => array('form'),
        'script'   => array('async'),
        'select'   => array('autofocus', 'form'),
        'style'    => array('scoped'),
        'textarea' => array('autofocus', 'form', 'placeholder', 'required'),
      ),
    ),
    RenderContext::LANG_XHTML => array(
      '1.0' => array(
        # TODO
      ),
      '5' => array(
        '*'        => array('aria-*', 'class', 'contenteditable', 'contextmenu', 'data-*', 'dir', 'draggable', 'hidden', 'id', 'lang', 'role', 'spellcheck', 'style', 'tabindex', 'title'),
        'a'        => array('media', 'target'),
        'area'     => array('hreflang', 'media', 'rel', 'target'),
        'base'     => array('target'),
        'button'   => array('autofocus', 'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget'),
        'fieldset' => array('disabled', 'form'),
        'form'     => array('novalidate'),
        'html'     => array('manifest'),
        'iframe'   => array('sandbox', 'seamless', 'srcdoc'),
        'input'    => array('autocomplete', 'autofocus', 'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget', 'list', 'max', 'min', 'multiple', 'pattern', 'placeholder', 'required', 'step'),
        'li'       => array('value'),
        'link'     => array('sizes'),
        'meta'     => array('charset'),
        'menu'     => array('label', 'type'),
        'ol'       => array('reversed', 'start'),
        'output'   => array('form'),
        'script'   => array('async'),
        'select'   => array('autofocus', 'form'),
        'style'    => array('scoped'),
        'textarea' => array('autofocus', 'form', 'placeholder', 'required'),
      ),
    ),
  );

  /**
   * Tables of deprecated elements.
   *
   * Note that XHTML 1.0 inherits deprecated elements from HTML 4.01.
   *
   * @var  array
   *
   * @link  http://www.w3.org/TR/html401/appendix/changes.html#h-A.3.1.2  Deprecated Elements in HTML 4.0
   * @link  http://www.w3.org/TR/html5-diff/#changed-elements             Deprecated Elements in (X)HTML5
   */
  protected static $deprecated_elements = array(
    RenderContext::LANG_HTML => array(
      '4.01' => array(
        'applet', 'basefont', 'center', 'dir', 'font', 'isindex', 'menu', 's',
        'strike', 'u',
      ),
      '5' => array(
        # Note: No elements were deprecated in (X)HTML5 - only obsoleted.
      ),
    ),
    RenderContext::LANG_XHTML => array(
      '1.0' => array(
        'applet', 'basefont', 'center', 'dir', 'font', 'isindex', 'menu', 's',
        'strike', 'u',
      ),
      '5' => array(
        # Note: No elements were deprecated in (X)HTML5 - only obsoleted.
      ),
    ),
  );

  /**
   * Tables of deprecated attributes.
   *
   * Note that XHTML 1.0 inherits deprecated attributes from HTML 4.01.
   *
   * @var  array
   *
   * @link  http://www.w3.org/TR/html401/index/attributes.html   Deprecated Attributes in HTML 4.0
   * @link  http://www.w3.org/TR/html5-diff/#changed-attributes  Deprecated Attributes in (X)HTML5
   * @link  http://www.w3.org/TR/xhtml1/#h-4.10                  Deprecated Attributes in XHTML 1.0
   */
  protected static $deprecated_attributes = array(
    RenderContext::LANG_HTML => array(
      '4.01' => array(
        //'applet'   => array('align', 'archive', 'code', 'codebase', 'height', 'hspace', 'name', 'object', 'vspace', 'width'),
        //'basefont' => array('color', 'face', 'size'),
        'body'     => array('alink', 'background', 'bgcolor', 'link', 'text', 'vlink'),
        'br'       => array('clear'),
        'caption'  => array('align'),
        //'dir'      => array('compact'),
        'div'      => array('align'),
        'dl'       => array('compact'),
        //'font'     => array('color', 'face', 'size'),
        'h1'       => array('align'),
        'h2'       => array('align'),
        'h3'       => array('align'),
        'h4'       => array('align'),
        'h5'       => array('align'),
        'h6'       => array('align'),
        'hr'       => array('align', 'noshade', 'size', 'width'),
        'html'     => array('version'),
        'iframe'   => array('align'),
        'img'      => array('align', 'border', 'hspace', 'vspace'),
        'input'    => array('align'),
        //'isindex'  => array('prompt'),
        'legend'   => array('align'),
        'li'       => array('type', 'value'),
        //'menu'     => array('compact'),
        'object'   => array('align', 'border', 'hspace', 'vspace'),
        'ol'       => array('compact', 'start', 'type'),
        'p'        => array('align'),
        'pre'      => array('width'),
        'script'   => array('language'),
        'table'    => array('align', 'bgcolor'),
        'td'       => array('bgcolor', 'height', 'nowrap', 'width'),
        'th'       => array('bgcolor', 'height', 'nowrap', 'width'),
        'tr'       => array('bgcolor'),
        'ul'       => array('compact', 'type'),
      ),
      '5' => array(
        'a'      => array('name'),
        'img'    => array('border'),
        'script' => array('language'),
        'table'  => array('summary'),
      ),
    ),
    RenderContext::LANG_XHTML => array(
      '1.0' => array(
        'a'        => array('name'),
        //'applet'   => array('align', 'archive', 'code', 'codebase', 'height', 'hspace', 'name', 'object', 'vspace', 'width'),
        //'basefont' => array('color', 'face', 'size'),
        'body'     => array('alink', 'background', 'bgcolor', 'link', 'text', 'vlink'),
        'br'       => array('clear'),
        'caption'  => array('align'),
        //'dir'      => array('compact'),
        'div'      => array('align'),
        'dl'       => array('compact'),
        //'font'     => array('color', 'face', 'size'),
        'form'     => array('name'),
        'frame'    => array('name'),
        'h1'       => array('align'),
        'h2'       => array('align'),
        'h3'       => array('align'),
        'h4'       => array('align'),
        'h5'       => array('align'),
        'h6'       => array('align'),
        'hr'       => array('align', 'noshade', 'size', 'width'),
        'html'     => array('version'),
        'iframe'   => array('align', 'name'),
        'img'      => array('align', 'border', 'hspace', 'name', 'vspace'),
        'input'    => array('align'),
        //'isindex'  => array('prompt'),
        'legend'   => array('align'),
        'li'       => array('type', 'value'),
        'map'      => array('name'),
        //'menu'     => array('compact'),
        'object'   => array('align', 'border', 'hspace', 'vspace'),
        'ol'       => array('compact', 'start', 'type'),
        'p'        => array('align'),
        'pre'      => array('width'),
        'script'   => array('language'),
        'table'    => array('align', 'bgcolor'),
        'td'       => array('bgcolor', 'height', 'nowrap', 'width'),
        'th'       => array('bgcolor', 'height', 'nowrap', 'width'),
        'tr'       => array('bgcolor'),
        'ul'       => array('compact', 'type'),
      ),
      '5' => array(
        'a'      => array('name'),
        'img'    => array('border'),
        'script' => array('language'),
        'table'  => array('summary'),
      ),
    ),
  );

  /**
   * Tables of obsolete elements.
   *
   * Note that XHTML 1.0 inherits obsolete elements from HTML 4.01.
   *
   * @var  array
   *
   * @link  http://www.w3.org/TR/html401/appendix/changes.html#h-A.3.1.3  Obsolete Elements in HTML 4.0
   * @link  http://www.w3.org/TR/html5-diff/#absent-elements              Obsolete Elements in (X)HTML5
   */
  protected static $obsolete_elements = array(
    RenderContext::LANG_HTML => array(
      '4.01' => array(
        'listing', 'plaintext', 'xmp',
      ),
      '5' => array(
        'acronym', 'applet', 'basefont', 'big', 'center', 'dir', 'font',
        'frame', 'frameset', 'isindex', 'noframes', 's', 'strike', 'tt', 'u',
      ),
    ),
    RenderContext::LANG_XHTML => array(
      '1.0' => array(
        'listing', 'plaintext', 'xmp',
      ),
      '5' => array(
        'acronym', 'applet', 'basefont', 'big', 'center', 'dir', 'font',
        'frame', 'frameset', 'isindex', 'noframes', 'noscript', 's', 'strike',
        'tt', 'u',
      ),
    ),
  );

  /**
   * Tables of obsolete attributes.
   *
   * @var  array
   *
   * Note that XHTML 1.0 inherits obsolete attributes from HTML 4.01.
   *
   * @link  http://www.w3.org/TR/html401/index/attributes.html  Obsolete Attributes in HTML 4.0
   * @link  http://www.w3.org/TR/html5-diff/#absent-attributes  Obsolete Attributes in (X)HTML5
   *
   * @todo  Add HTML 4.01, XHTML 1.0
   */
  protected static $obsolete_attributes = array(
    RenderContext::LANG_HTML => array(
      '4.01' => array(
        # TODO
      ),
      '5' => array(
        'a'        => array('charset', 'coords', 'rev', 'shape'),
        'area'     => array('nohref'),
        'body'     => array('alink', 'background', 'bgcolor', 'link', 'text', 'vlink'),
        'br'       => array('clear'),
        'caption'  => array('align'),
        'col'      => array('align', 'char', 'charoff', 'valign', 'width'),
        'colgroup' => array('align', 'char', 'charoff', 'valign', 'width'),
        'div'      => array('align'),
        'dl'       => array('compact'),
        'h1'       => array('align'),
        'h2'       => array('align'),
        'h3'       => array('align'),
        'h4'       => array('align'),
        'h5'       => array('align'),
        'h6'       => array('align'),
        'head'     => array('profile'),
        'hr'       => array('align', 'noshade', 'size', 'width'),
        'html'     => array('version'),
        'iframe'   => array('align', 'frameborder', 'marginheight', 'longdesc', 'marginwidth', 'scrolling'),
        'img'      => array('align', 'hspace', 'longdesc', 'name', 'vspace'),
        'input'    => array('align'),
        'legend'   => array('align'),
        'li'       => array('type'),
        'link'     => array('charset', 'rev', 'target'),
        'menu'     => array('compact'),
        'meta'     => array('scheme'),
        'object'   => array('align', 'archive', 'border', 'classid', 'codebase', 'codetype', 'declare', 'hspace', 'standby', 'vspace'),
        'ol'       => array('compact', 'type'),
        'p'        => array('align'),
        'param'    => array('type', 'valuetype'),
        'pre'      => array('width'),
        'table'    => array('align', 'bgcolor', 'border', 'cellpadding', 'cellspacing', 'frame', 'rules', 'width'),
        'tbody'    => array('align', 'char', 'charoff', 'valign'),
        'td'       => array('abbr', 'align', 'axis', 'bgcolor', 'char', 'charoff', 'height', 'nowrap', 'scope', 'valign', 'width'),
        'tfoot'    => array('align', 'char', 'charoff', 'valign'),
        'th'       => array('abbr', 'align', 'axis', 'bgcolor', 'char', 'charoff', 'height', 'nowrap', 'valign', 'width'),
        'thead'    => array('align', 'char', 'charoff', 'valign'),
        'tr'       => array('align', 'bgcolor', 'char', 'charoff', 'valign'),
        'ul'       => array('compact', 'type'),
      ),
    ),
    RenderContext::LANG_XHTML => array(
      '1.0' => array(
        # TODO
      ),
      '5' => array(
        'a'        => array('charset', 'coords', 'rev', 'shape'),
        'area'     => array('nohref'),
        'body'     => array('alink', 'background', 'bgcolor', 'link', 'text', 'vlink'),
        'br'       => array('clear'),
        'caption'  => array('align'),
        'col'      => array('align', 'char', 'charoff', 'valign', 'width'),
        'colgroup' => array('align', 'char', 'charoff', 'valign', 'width'),
        'div'      => array('align'),
        'dl'       => array('compact'),
        'h1'       => array('align'),
        'h2'       => array('align'),
        'h3'       => array('align'),
        'h4'       => array('align'),
        'h5'       => array('align'),
        'h6'       => array('align'),
        'head'     => array('profile'),
        'hr'       => array('align', 'noshade', 'size', 'width'),
        'html'     => array('version'),
        'iframe'   => array('align', 'frameborder', 'marginheight', 'longdesc', 'marginwidth', 'scrolling'),
        'img'      => array('align', 'hspace', 'longdesc', 'name', 'vspace'),
        'input'    => array('align'),
        'legend'   => array('align'),
        'li'       => array('type'),
        'link'     => array('charset', 'rev', 'target'),
        'menu'     => array('compact'),
        'meta'     => array('scheme'),
        'object'   => array('align', 'archive', 'border', 'classid', 'codebase', 'codetype', 'declare', 'hspace', 'standby', 'vspace'),
        'ol'       => array('compact', 'type'),
        'p'        => array('align'),
        'param'    => array('type', 'valuetype'),
        'pre'      => array('width'),
        'table'    => array('align', 'bgcolor', 'border', 'cellpadding', 'cellspacing', 'frame', 'rules', 'width'),
        'tbody'    => array('align', 'char', 'charoff', 'valign'),
        'td'       => array('abbr', 'align', 'axis', 'bgcolor', 'char', 'charoff', 'height', 'nowrap', 'scope', 'valign', 'width'),
        'tfoot'    => array('align', 'char', 'charoff', 'valign'),
        'th'       => array('abbr', 'align', 'axis', 'bgcolor', 'char', 'charoff', 'height', 'nowrap', 'valign', 'width'),
        'thead'    => array('align', 'char', 'charoff', 'valign'),
        'tr'       => array('align', 'bgcolor', 'char', 'charoff', 'valign'),
        'ul'       => array('compact', 'type'),
      ),
    ),
  );

  # }}} standards compliance tables
  ##############################################################################

  ##############################################################################
  # main tag fields {{{

  /**
   * The element name for the tag.
   *
   * @var  string
   */
  protected $element = null;

  /**
   * The attributes for the tag.
   *
   * @var  array
   */
  protected $attributes = array();

  /**
   * The content of the tag.
   *
   * @var  Renderable|string
   */
  protected $content = ''; # Note: Must be empty string by default!

  /**
   * The expression used for a conditional comment.
   *
   * @var  string
   */
  protected $conditional = null;

  # }}} main tag fields
  ##############################################################################

  ##############################################################################
  # core tag creation methods {{{

  /**
   * Creates a new tag for the specified element.
   *
   * @param  string  $element  The name of the element to create.
   *
   * @todo  Check for valid characters in element name.
   */
  public function __construct($element) {
    $this->element = strtolower($element);
  }

  /**
   * A factory function for creating a new tag for the specified element.
   *
   * @param  string  $element  The name of the element to create.
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   */
  public static function create($element) {
    return new static($element);
  }

  # }}} core tag creation methods
  ##############################################################################

  ##############################################################################
  # shorthand tag creation methods {{{

  /**
   * A shorthand for creating a <code>&lt;br&gt;</code> tag.
   *
   * As this is one of the more commonly used elements a shorthand method has
   * been added.
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   */
  public static function br() {
    return static::create('br');
  }

  /**
   * A shorthand for creating a <code>&lt;hr&gt;</code> tag.
   *
   * As this is one of the more commonly used elements a shorthand method has
   * been added.
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   */
  public static function hr() {
    return static::create('hr');
  }

  /**
   * A shorthand for creating a <code>&lt;img&gt;</code> tag.
   *
   * As this is one of the more commonly used elements a shorthand method has
   * been added.
   *
   * @param  URL|string  $src  The source attribute for the image.
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   */
  public static function img($src) {
    return static::create('img')->src($src);
  }

  /**
   * A shorthand for creating a <code>&lt;script&gt;</code> tag.
   *
   * We automatically add the <code>type</code> attribute to the tag for some
   * versions of some markup languages that require it.
   *
   * @param  string  $src  The source attribute for the script.
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   */
  public static function script($src = null) {
    $tag = Tag::create('script');
    $ctx = RenderContext::get();
    switch ($ctx->getLanguage()) {
      case RenderContext::LANG_HTML:
        switch ($ctx->getVersion()) {
          case 4.01: $tag = $tag->type(RenderContext::CONTENT_JS); break;
        }
        break;
      case RenderContext::LANG_XHTML:
        switch ($ctx->getVersion()) {
          case 1.0:  $tag = $tag->type(RenderContext::CONTENT_JS); break;
        }
        break;
    }
    return ($src === null ? $tag : $tag->src($src));
  }

  /**
   * A shorthand for creating a <code>&lt;style&gt;</code> tag.
   *
   * We automatically add the <code>type</code> attribute to the tag for some
   * versions of some markup languages that require it.
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   */
  public static function style() {
    $tag = Tag::create('style');
    $ctx = RenderContext::get();
    switch ($ctx->getLanguage()) {
      case RenderContext::LANG_HTML:
        switch ($ctx->getVersion()) {
          case 4.01: $tag = $tag->type(RenderContext::CONTENT_CSS); break;
        }
        break;
      case RenderContext::LANG_XHTML:
        switch ($ctx->getVersion()) {
          case 1.0:  $tag = $tag->type(RenderContext::CONTENT_CSS); break;
        }
        break;
    }
    return $tag;
  }

  /**
   * A shorthand for creating a &lt;wbr&gt; tag.
   *
   * This tag functions the same as a zero width space (&amp;#8203;)
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   */
  public static function wbr() {
    return static::create('wbr');
  }

  # }}} shorthand tag creation methods
  ##############################################################################

  ##############################################################################
  # magic element handling method {{{

  /**
   *
   * @throws  \Jerity\Core\Exception
   */
  public static function __callStatic($name, array $args) {
    if (count($args)) {
      throw new Exception("Unable to handle unknown attributes for '{$name}'.");
    }
    return static::create($name);
  }

  # }}} magic element handling method
  ##############################################################################

  ##############################################################################
  # core and common attribute methods {{{

  # XXX: Cannot declare 'class' method, so use __call() to handle it.

  /**
   * Adds an <code>id</code> attribute to the current tag.
   *
   * As this is one of the more commonly used attributes a shorthand method has
   * been added.
   *
   * @param  string  $id  The value for the <code>id</code> attribute.
   *
   * @return  Tag  The current <code>Tag</code> object for method chaining.
   *
   * @todo  Check the format of the id string.
   */
  public function id($id) {
    $this->attributes['id'] = $id;
    return $this;
  }

  # }}} core and common attribute methods
  ##############################################################################

  ##############################################################################
  # magic attribute and shorthand handling method {{{

  /**
   *
   * @todo  Throw exception if $args is incorrect.
   * @todo  Check the format of the class string.
   */
  public function __call($name, array $args) {
    $name = strtolower($name);
    switch ($name) {
      # Handle arributes with '-' characters:
      case 'http_equiv':
        $name = str_replace('_', '-', $name);
        break;
      # Handle 'class' (reserved keyword) and support '+' prefix for appending a
      # class to the existing class list:
      case 'class':
        if ($args[0][0] === '+') {
          if (isset($this->attributes[$name])) {
            $classes = explode(' ', $this->attributes[$name]);
            $classes[] = substr($args[0], 1);
          } else {
            $classes = array($name);
          }
          $this->attributes[$name] = implode(' ', $classes);
          return $this;
        }
        break;
      # Check whether normal 'size' attribute or special shorthand for 'width'
      # and 'height' attributes:
      case 'size':
        if ($this->element == 'img') {
          $this->attributes['width']  = $args[0];
          $this->attributes['height'] = $args[1];
          return $this;
        }
        break;
    }
    $this->attributes[$name] = $args[0];
    return $this;
  }

  # }}} magic attribute and shorthand handling method
  ##############################################################################

  ##############################################################################
  # content handling methods {{{

  /**
   * Adds content to the tag.
   *
   * When using a render context for an XML-based markup language,
   * <code>false</code> can be passed to <code>$content</code> to force a 
   * self-closing tag for those elements that can usually contain content.
   *
   * <b>Note:</b> You <i>should</i> sanitize your content before adding it to
   * the tag as no escaping of content is done.
   *
   * This method accepts a string, a <code>Renderable</code> or an array of 
   * strings and/or <code>Renderable</code>s.
   *
   * @param  Renderable|string|array  $content  The content to add.
   * @param  bool                     $append   Whether to append or replace.
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   */
  public function _($content, $append = false) {
    # Check if we are forcing no content and short closing tag.
    if ($content === false) {
      $this->content = false;
      return $this;
    }
    # Set or append content to the tag.
    if (is_array($content)) $content = implode(PHP_EOL, $content);
    if ($append) {
      $this->content .= $content;
    } else {
      $this->content = $content;
    }
    return $this;
  }

  # }}} content handling methods
  ##############################################################################

  ##############################################################################
  # rendering and output methods {{{

  /**
   * Overrides the default object to string conversion to force the Renderable
   * item to be rendered in string context.
   *
   * @return  string
   */
  public function __toString() {
    return $this->render();
  }

  /**
   * Renders a tag according the the current render context.
   *
   * Note that content is <b>never</b> escaped. You must perform your own
   * sanitation of any content.  All attributes that were given a
   * <code>false</code> value are stripped from the generated tag.
   *
   * If an element cannot contain content, XML-based markup languages will have
   * a self-closing tag generated.  To force a tag that could have content to
   * be self closing you can explicitly set the content to <code>false</code>.
   *
   * All elements and attributes have their names converted to lowercase and
   * attributes are sorted in alphabetical order.
   *
   * This <code>Tag</code> class is designed to be very flexible.  Because of 
   * this one can generate invalid tags for a particular markup language.  It 
   * is possible to see warnings when debugging is enabled.  These warnings are
   * enabled by default but will only show if you have enabled debugging which
   * should only be in a development environment.  This can help you to
   * identify mistakes in your code.  To disable the errors use the core PHP
   * error reporting system.  Removing <code>E_DEPRECATED</code> will hide
   * deprecation notices for elements and attributes and removing
   * <code>E_WARNING</code> will hide warnings about invalid elements and
   * attributes.  Note that these warnings will be based on your current render
   * context.
   *
   * @return  string
   *
   * @see  \error_reporting()
   * @see  \Jerity\Core\Debug::isEnabled()
   * @see  \Jerity\Core\RenderContext
   *
   * @todo  Check for whitelisted elements.
   * @todo  Check for whitelisted attributes.
   */
  public function render() {
    # Check for and warn about deprecated elements and attributes.
    if (Debug::isEnabled()) {
      if (error_reporting() & E_DEPRECATED) {
        self::checkDeprecatedElements($this->element);
        self::checkDeprecatedAttributes($this->element, $this->attributes);
      }
      if (error_reporting() & E_WARNING) {
        self::checkObsoleteElements($this->element);
        self::checkObsoleteAttributes($this->element, $this->attributes);
      }
    }
    # Prepare element:
    $element = String::escape($this->element);
    # Prepare attributes:
    ksort($this->attributes);
    $attributes = array();
    foreach ($this->attributes as $k => $v) {
      # Strip attributes with 'false' - an unset variable.
      if ($v === false) continue;
      # Handle attribute="attribute".
      if ($v === true) $v = $k;
      $attributes[] = ' '.$k.'="'.String::escape($v).'"';
    }
    $attributes = implode($attributes);
    # Check for self-closing or empty tags:
    if (RenderContext::get()->isXMLSyntax() && ($this->isVoidElement() || $this->content === false)) {
      $tag_tail = ' /';
    } else {
      $tag_tail = '';
    }
    # Generate opening tag:
    $html = "<{$element}{$attributes}{$tag_tail}>";
    # Generate content and closing tag:
    if (!$this->isVoidElement() && $this->content !== null && $this->content !== false) {
      if ($this->content !== '') {
        if (in_array($this->element, array('script', 'style'))) {
          if (RenderContext::get()->getLanguage() == RenderContext::LANG_XHTML) {
            $html .= "<![CDATA[\n{$this->content}\n]]>";
          } else {
            $html .= "\n{$this->content}\n";
          }
        } else {
          $html .= $this->content;
        }
      }
      $html .= "</{$element}>";
    }
    # Check whether we need a conditional comment:
    if ($this->conditional !== null) {
      $html = self::wrapConditionalComment($this->conditional, $html);
    }
    # Return tag:
    return $html;
  }

  /**
   * Checks whether the current tag is for a void element which cannot have 
   * content.
   *
   * @return  bool  Whether this tag is for a void element.
   */
  protected function isVoidElement() {
    $ctx = RenderContext::get();
    if (isset(self::$void_elements[$ctx->getLanguage()][$ctx->getVersion()])) {
      return in_array($this->element, self::$void_elements[$ctx->getLanguage()][$ctx->getVersion()]);
    }
    return false;
  }

  # }}} rendering and output methods
  ##############################################################################

  ##############################################################################
  # conditional proxy methods {{{

  /**
   * Returns the current object if the condition is true, otherwise we return
   * a ConditionalProxyHandler instance.
   *
   * Allows for conditional statements in a fluid interface.
   *
   * @param  bool  $condition  The condition to check.
   *
   * @return  ConditionalProxyHandler|Tag
   */
  public function _if($condition) {
    return ConditionalProxyHandler::create($this, $condition);
  }

  /**
   * Returns a ConditionalProxyHandler instance to allow us to skip over all
   * subsequent method calls until we hit an _endif().
   *
   * Allows for conditional statements in a fluid interface.
   *
   * @param  bool  $condition  This condition is ignored.
   *
   * @return  ConditionalProxyHandler
   */
  public function _elseif($condition) {
    return ConditionalProxyHandler::progress($this, $condition);
  }

  /**
   * Returns a ConditionalProxyHandler instance to allow us to skip over all
   * subsequent method calls until we hit an _endif().
   *
   * Allows for conditional statements in a fluid interface.
   *
   * @return  ConditionalProxyHandler
   */
  public function _else() {
    return ConditionalProxyHandler::progress($this);
  }

  /**
   * Returns the current object and ends the conditional proxying.
   *
   * Allows for conditional statements in a fluid interface.
   *
   * @return  Tag
   */
  public function _endif() {
    return ConditionalProxyHandler::destroy($this);
  }

  # }}} conditional proxy methods
  ##############################################################################

  ##############################################################################
  # conditional comments {{{

  /**
   * Wraps the current tag in a conditional comment at render time.
   *
   * Nothing is done to check the expression for validity.
   *
   * @param  string  $expression  The condition.
   *
   * @return  Tag  A new <code>Tag</code> object for method chaining.
   *
   * @link  http://msdn.microsoft.com/en-us/library/ms537512%28VS.85%29.aspx#syntax
   */
  public function _cc($expression) {
    $this->conditional = $expression;
    return $this;
  }

  /**
   * Wraps the provided content in a conditional comment.
   *
   * Nothing is done to check the expression for validity.
   *
   * @param  string   $expression  The condition.
   * @param  string   $content     The content to wrap inside the comment.
   * @param  boolean  $newline     Put conditional comment tags on new lines.
   * @param  boolean  $revealed    Use a revealed conditional comment.
   *
   * @return  string  The content wrapped in conditional comments.
   *
   * @link  http://msdn.microsoft.com/en-us/library/ms537512%28VS.85%29.aspx#syntax
   */
  public static function wrapConditionalComment($expression, $content, $newline = false, $revealed = false) {
    $html = '';
    $html .= '<!'.($revealed ? '' : '--').'[if '.$expression.']>';
    if ($newline) $html .= PHP_EOL;
    $html .= $content;
    if ($newline) $html .= PHP_EOL;
    $html .= '<![endif]'.($revealed ? '' : '--').'>'.PHP_EOL;
    return $html;
  }

  # }}} conditional comments
  ##############################################################################

  ##############################################################################
  # deprecation checking methods {{{

  /**
   * Checks for deprecated elements based on the current render context and
   * warns the developer if they are using a deprecated element.
   *
   * Only displays a message if debugging is enabled.
   *
   * @param  string  $element  The element to check.
   *
   * @return  bool  Returns <code>true</code> if the element is deprecated.
   *
   * @todo  Move xdebug disabling code to \Jerity\Core\Error.
   */
  protected static function checkDeprecatedElements($element) {
    if (!Debug::isEnabled()) return false;
    if (empty($element)) return false;
    $ctx = RenderContext::get();
    $x = &self::$deprecated_elements;
    if (!array_key_exists($ctx->getLanguage(), $x)) return false;
    unset($x);
    $x = &self::$deprecated_elements[$ctx->getLanguage()];
    if (!array_key_exists("{$ctx->getVersion()}", $x)) return false;
    unset($x);
    $x = &self::$deprecated_elements[$ctx->getLanguage()]["{$ctx->getVersion()}"];
    if (!in_array($element, $x)) return false;
    $restore_xdebug = false;
    if (extension_loaded('xdebug')) {
      $restore_xebug = xdebug_is_enabled();
      # A complete stack trace is overkill for a deprecation error.
      xdebug_disable();
    }
    trigger_error("'{$element}' is deprecated in {$ctx->getLanguage()} {$ctx->getVersion()}", E_USER_DEPRECATED);
    if ($restore_xdebug) {
      xdebug_enable();
    }
    return true;
  }

  /**
   * Checks for deprecated attributes based on the current render context and
   * warns the developer if they are using a deprecated element.
   *
   * Only displays a message if debugging is enabled.
   *
   * @param  string  $element     The element to match the attributes against.
   * @param  array   $attributes  The attributes to check.
   *
   * @return  bool  Returns <code>true</code> if any attribute is deprecated.
   *
   * @todo  Use array_intersect to report all attributes in one message.
   * @todo  Move xdebug disabling code to \Jerity\Core\Error.
   */
  protected static function checkDeprecatedAttributes($element, array $attributes) {
    if (!Debug::isEnabled()) return false;
    if (empty($element) || empty($attributes)) return false;
    $ctx = RenderContext::get();
    $x = &self::$deprecated_attributes;
    if (!array_key_exists($ctx->getLanguage(), $x)) return false;
    unset($x);
    $x = &self::$deprecated_attributes[$ctx->getLanguage()];
    if (!array_key_exists("{$ctx->getVersion()}", $x)) return false;
    unset($x);
    $x = &self::$deprecated_attributes[$ctx->getLanguage()]["{$ctx->getVersion()}"];
    if (!in_array($element, array_keys($x))) return false;
    unset($x);
    $x = &self::$deprecated_attributes[$ctx->getLanguage()]["{$ctx->getVersion()}"][$element];
    $found = false;
    foreach ($attributes as $attribute) {
      if (!in_array($attribute, $x)) continue;
      $restore_xdebug = false;
      if (extension_loaded('xdebug')) {
        $restore_xebug = xdebug_is_enabled();
        # A complete stack trace is overkill for a deprecation error.
        xdebug_disable();
      }
      trigger_error("'{$element}[{$attribute}]' is deprecated in {$ctx->getLanguage()} {$ctx->getVersion()}", E_USER_DEPRECATED);
      if ($restore_xdebug) {
        xdebug_enable();
      }
      $found = true;
    }
    return $found;
  }

  /**
   * Checks for obsolete elements based on the current render context and
   * warns the developer if they are using an obsolete element.
   *
   * Only displays a message if debugging is enabled.
   *
   * @param  string  $element  The element to check.
   *
   * @return  bool  Returns <code>true</code> if the element is obsolete.
   *
   * @todo  Move xdebug disabling code to \Jerity\Core\Error.
   */
  protected static function checkObsoleteElements($element) {
    if (!Debug::isEnabled()) return false;
    if (empty($element)) return false;
    $ctx = RenderContext::get();
    $x = &self::$obsolete_elements;
    if (!array_key_exists($ctx->getLanguage(), $x)) return false;
    unset($x);
    $x = &self::$obsolete_elements[$ctx->getLanguage()];
    if (!array_key_exists("{$ctx->getVersion()}", $x)) return false;
    unset($x);
    $x = &self::$obsolete_elements[$ctx->getLanguage()]["{$ctx->getVersion()}"];
    if (!in_array($element, $x)) return false;
    $restore_xdebug = false;
    if (extension_loaded('xdebug')) {
      $restore_xebug = xdebug_is_enabled();
      # A complete stack trace is overkill for a deprecation error.
      xdebug_disable();
    }
    trigger_error("'{$element}' is obsolete in {$ctx->getLanguage()} {$ctx->getVersion()}", E_USER_DEPRECATED);
    if ($restore_xdebug) {
      xdebug_enable();
    }
    return true;
  }

  /**
   * Checks for obsolete attributes based on the current render context and
   * warns the developer if they are using an obsolete element.
   *
   * Only displays a message if debugging is enabled.
   *
   * @param  string  $element     The element to match the attributes against.
   * @param  array   $attributes  The attributes to check.
   *
   * @return  bool  Returns <code>true</code> if any attribute is obsolete.
   *
   * @todo  Use array_intersect to report all attributes in one message.
   * @todo  Move xdebug disabling code to \Jerity\Core\Error.
   */
  protected static function checkObsoleteAttributes($element, array $attributes) {
    if (!Debug::isEnabled()) return false;
    if (empty($element) || empty($attributes)) return false;
    $ctx = RenderContext::get();
    $x = &self::$obsolete_attributes;
    if (!array_key_exists($ctx->getLanguage(), $x)) return false;
    unset($x);
    $x = &self::$obsolete_attributes[$ctx->getLanguage()];
    if (!array_key_exists("{$ctx->getVersion()}", $x)) return false;
    unset($x);
    $x = &self::$obsolete_attributes[$ctx->getLanguage()]["{$ctx->getVersion()}"];
    if (!in_array($element, array_keys($x))) return false;
    unset($x);
    $x = &self::$obsolete_attributes[$ctx->getLanguage()]["{$ctx->getVersion()}"][$element];
    $found = false;
    foreach ($attributes as $attribute) {
      if (!in_array($attribute, $x)) continue;
      $restore_xdebug = false;
      if (extension_loaded('xdebug')) {
        $restore_xebug = xdebug_is_enabled();
        # A complete stack trace is overkill for a deprecation error.
        xdebug_disable();
      }
      trigger_error("'{$element}[{$attribute}]' is obsolete in {$ctx->getLanguage()} {$ctx->getVersion()}", E_USER_DEPRECATED);
      if ($restore_xdebug) {
        xdebug_enable();
      }
      $found = true;
    }
    return $found;
  }

  # }}} deprecation checking methods
  ##############################################################################

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
