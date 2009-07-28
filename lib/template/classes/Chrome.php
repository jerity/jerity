<?php
/**
 * @package    JerityTemplate
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * The Chrome template is used to specify the default look and feel for a site
 * and can be manipulated using various methods provided to make it easier to
 * add general styling, scripting and metadata to a page.
 *
 * @todo  Support IE conditional comments for various chrome items.
 * @todo  Allow addition of 'custom' head content.
 *
 * @package    JerityTemplate
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Chrome extends Template {

  ##############################################################################
  # chrome content item management {{{

  /**
   * @var  mixed  One or more content items added to this template.
   */
  protected $content = null;

  /**
   * @var  ArrayIterator  An iterator over this template's content.
   */
  protected $contentIterator = null;

  # }}} chrome content item management
  ##############################################################################

  ##############################################################################
  # chrome general settings management {{{

  /**
   * Used when adding metadata so that we know which key attribute to use.
   */
  const META_HTTP = 'http-equiv';
  const META_NAME = 'name';

  /**
   * MIME types for favourites icon (favicon).
   *
   * - Most browsers support .ico, .png, .gif
   * - Firefox additionally supports .jpg
   * - IE is bad and uses rel="shortcut icon" + type="image/x-icon" regardless.
   *   - All other browsers use rel="icon" and correct a MIME type.
   *
   * Jerity will automatically add specific tags for IE.
   */
  const ICON_ICO     = 'image/vnd.microsoft.icon';
  const ICON_PNG     = 'image/png';
  const ICON_GIF     = 'image/gif';
  const ICON_JPG     = 'image/jpeg';
  const ICON_X_ICON  = 'image/x-icon';

  /**
   * @var  array  Favourites icons (favicon) ordering.
   */
  protected static $ICON_ORDER = array(
    self::ICON_ICO,
    self::ICON_PNG,
    self::ICON_GIF,
    self::ICON_JPG,
    self::ICON_X_ICON,
  );

  /**
   * Some common XML namespaces.
   *
   * Other namespaces with prefixes:
   * -------------------------------
   * http://www.w3.org/1999/XSL/Format            xmlns:fo
   * http://www.w3.org/1999/XSL/Transform         xmlns:xsl
   * http://www.w3.org/1999/02/22-rdf-syntax-ns#  xmlns:rdf
   * http://www.w3.org/2001/SMIL20                xmlns:smil
   * http://www.w3.org/2001/XMLSchema             xmlns:xs     xmlns:xsd
   * http://www.w3.org/2001/XMLSchema-instance    xmlns:xsi
   * http://www.w3.org/2001/xml-events            xmlns:ev
   * http://www.w3.org/2001/12/soap-envelope      xml:env      xmlns:soap
   * http://www.w3.org/2002/xforms                xmlns:xf
   * http://www.w3.org/2005/Atom                  xmlns:atom
   * http://www.w3.org/2005/xpath-functions       xmlns:fn
   * http://www.w3.org/2005/xqt-errors            xmlns:err
   * urn:schemas-microsoft-com:office:office      xmlns:o
   * urn:schemas-microsoft-com:time               xmlns:t      xmlns:time
   *
   * @see  http://validator.w3.org/feed/docs/howto/declare_namespaces.html
   */
  const XMLNS_FBML   = 'http://www.facebook.com/2008/fbml';
  const XMLNS_MATHML = 'http://www.w3.org/1998/Math/MathML';
  const XMLNS_SVG    = 'http://www.w3.org/2000/svg';
  const XMLNS_VML    = 'urn:schemas-microsoft-com:vml';
  const XMLNS_WML    = 'http://www.wapforum.org/2001/wml';
  const XMLNS_XHTML  = 'http://www.w3.org/1999/xhtml';
  const XMLNS_XLINK  = 'http://www.w3.org/1999/xlink';

  /**
   * @var  array  Common XML namespace prefixes.
   */
  protected static $XMLNS_PREFIXES = array(
    self::XMLNS_FBML   => 'fb',
    self::XMLNS_MATHML => 'mathml',
    self::XMLNS_SVG    => 'svg',
    self::XMLNS_VML    => 'v',
    self::XMLNS_WML    => 'wml',
    self::XMLNS_XHTML  => 'html',
    self::XMLNS_XLINK  => 'xlink',
  );

  /**
   * Store for metadata.
   *
   * @var  array
   */
  protected static $metadata = array();

  /**
   * Store for scripts.
   *
   * @var  array
   */
  protected static $scripts = array();

  /**
   * Store for stylesheets.
   *
   * @var  array
   */
  protected static $stylesheets = array();

  /**
   * Store for title parts.
   *
   * @var  array
   */
  protected static $title = array();

  /**
   * Store for favourites icons (favicon).
   *
   * @var  array
   */
  protected static $icons = array();

  /**
   * Store for XML namespaces.
   *
   * @var  array
   */
  protected static $xml_namespaces = array();

  /**
   * The language that the page is displayed in.
   *
   * @var  string
   */
  protected static $language = null;

  # }}} chrome general settings management
  ##############################################################################

  /**
   * Passes the template name with appropriate prefix up to the template
   * constructor.
   *
   * @param  string  $t  The template to use.
   */
  public function __construct($t) {
    parent::__construct('chrome/'.$t);
  }

  ##############################################################################
  # chrome-specific rendering {{{

  /**
   * Render the item using the current global rendering context, and return it
   * as a string.
   *
   * @return  string
   */
  public function render() {
    # Call Renderable::render() for content items as necessary.
    if (is_array($this->content)) {
      foreach ($this->content as &$content) {
        if (!($content instanceof Renderable)) continue;
        $content = $content->render();
      }
      unset($content); # break last element reference.
    } elseif ($this->content instanceof Renderable) {
      $this->content = $this->content->render();
    }

    # Call default render method.
    return parent::render();
  }

  # }}} chrome specific rendering
  ##############################################################################

  ##############################################################################
  # chrome content item management {{{

  /**
   * Returns one or more content items added to this template.
   *
   * @return  mixed
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * Returns the next available content item, or null if there are no further
   * items.
   *
   * @return  mixed
   */
  public function getNextContent() {
    if (!$this->contentIterator instanceof Iterator) {
      if (is_array($this->content)) {
        $this->contentIterator = new ArrayIterator($this->content);
      } else {
        $this->contentIterator = new ArrayIterator(array($this->content));
      }
    } else {
      $this->contentIterator->next();
    }
    if (!$this->contentIterator->valid()) {
      return null;
    }
    return $this->contentIterator->current();
  }

  /**
   * Adds one or more content items to this template.
   * You may add content in the following ways:
   *   $c->setContent($content);
   *   $c->setContent($content0, $content1, ...);
   *   $c->setContent(array($content0, $content1, ...));
   *
   * @param  mixed  ...  The content item(s) to add.
   *
   * @throws  InvalidArgumentException
   */
  public function setContent() {
    if (func_num_args() < 1) {
      throw new InvalidArgumentException('You must specify a content item.');
    }
    $items = ArrayUtil::flatten(func_get_args());
    foreach ($items as $i) {
      if (!($i instanceof Renderable) && !is_string($i)) {
        throw new TemplateException('Non-renderable or non-string item set as content.');
      }
    }
    $this->content = (count($items) > 1 ? $items : $items[0]);
    $this->contentIterator = null;
  }

  /**
   * Clears the content item list for this template.
   */
  public function clearContent() {
    $this->content = null;
  }

  # }}} chrome content item management
  ##############################################################################

  ##############################################################################
  # chrome general settings management {{{

  /**
   * Adds some metadata to the page.  HTTP header equivalent metadata is stored
   * separately.
   *
   * @param  string  $name   The name of the information.
   * @param  string  $value  The value to assign.
   * @param  string  $key    The key attribute.
   */
  public static function addMetadata($name, $value, $key = self::META_NAME) {
    self::$metadata[$key][$name] = $value;
  }

  /**
   * Removes some metadata from the page.
   *
   * @param  string  $name   The name of the information.
   * @param  string  $key    The key attribute.
   */
  public static function removeMetadata($name, $key = self::META_NAME) {
    unset(self::$metadata[$key][$name]);
  }

  /**
   * Clears all metadata currently added to the page.
   */
  public static function clearMetadata() {
    self::$metadata = array();
  }

  /**
   * Gets the array of metadata for the page
   *
   * @return  array  The metadata for the current page.
   */
  public static function getMetadata() {
    return self::$metadata;
  }

  /**
   * Adds a script to the page.  Will add the script once at most.
   *
   * Note that 'charset' and 'defer' attributes can be passed into $attrs.
   *
   * @param  string  $href      The href of the file.
   * @param  string  $priority  Defines the order that scripts are loaded [0-99]
   * @param  string  $type      The type of script.
   * @param  array   $attrs     An array of additional attributes.
   *
   * @throws  OutOfRangeException
   */
  public static function addScript($href, $priority = 50, $type = RenderContext::CONTENT_JS, array $attrs = array()) {
    if ($priority < 0 || $priority > 99) {
      throw new OutOfRangeException('Script priority must be in the range [0-99]');
    }
    $attrs['type'] = $type;
    $attrs['src']  = $href;
    self::$scripts[$type][$href] = array(
      'priority' => $priority,
      'attrs'    => $attrs,
    );
  }

  /**
   * Removes a script from the page.
   *
   * @param  string  $href  The href of the file.
   * @param  string  $type  The type of script.
   */
  public static function removeScript($href, $type = RenderContext::CONTENT_JS) {
    unset(self::$scripts[$type][$href]);
  }

  /**
   * Clears all scripts currently added to the page.
   */
  public static function clearScripts() {
    self::$scripts = array();
  }

  /**
   * Gets the array of scripts for the page.  The scripts are returned in
   * priority order by type.
   *
   * @return  array  The scripts for the current page.
   */
  public static function getScripts() {
    $s = self::$scripts;
    foreach ($s as $t => $a) {
      uasort($a, create_function('$a,$b', 'return strcmp($a[\'priority\'], $b[\'priority\']);'));
      $s[$t] = array_map(create_function('$a', 'return $a[\'attrs\'];'), $a);
    }
    return $s;
  }

  /**
   * Adds a stylesheet to the page.  The stylesheets are stored by href which
   * is intended to provide a simple solution to duplicate stylesheets.
   *
   * The media parameter should be a comma-separated string of media types.
   *
   * @param  string  $href      The href of the file.
   * @param  string  $priority  Defines the order that scripts are loaded [0-99]
   * @param  string  $type      The type of stylesheet.
   * @param  array   $attrs     An array of additional attributes.
   *
   * @throws  OutOfRangeException
   */
  public static function addStylesheet($href, $priority = 50, $type = RenderContext::CONTENT_CSS, array $attrs = array()) {
    if ($priority < 0 || $priority > 99) {
      throw new OutOfRangeException('Stylesheet priority must be in the range [0-99]');
    }
    $attrs = array_merge(
      array('rel' => 'stylesheet', 'type' => $type),
      $attrs,
      array('href' => $href)
    );
    self::$stylesheets[$type][$href] = array(
      'priority' => $priority,
      'attrs'    => $attrs,
    );
  }

  /**
   * Removes a stylesheet from the page.
   *
   * @param  string  $href  The href of the file.
   * @param  string  $type  The type of stylesheet.
   */
  public static function removeStylesheet($href, $type = RenderContext::CONTENT_CSS) {
    unset(self::$stylesheet[$type][$href]);
  }

  /**
   * Clears all stylesheets currently added to the page.
   */
  public static function clearStylesheets() {
    self::$stylesheets = array();
  }

  /**
   * Gets the array of stylesheets for the page.  The stylesheets are returned
   * in priority order by type.
   *
   * @return  array  The stylesheets for the current page.
   */
  public static function getStylesheets() {
    $s = self::$stylesheets;
    foreach ($s as $t => $a) {
      uasort($a, create_function('$a,$b', 'return strcmp($a[\'priority\'], $b[\'priority\']);'));
      $s[$t] = array_map(create_function('$a', 'return $a[\'attrs\'];'), $a);
    }
    return $s;
  }

  /**
   * Gets the title for the page and joins parts together with $separator
   * unless null is passed.
   *
   * @param  string  $separator  The string to implode() with or null
   *
   * @return  mixed
   */
  public static function getTitle($separator = ' &raquo; ') {
    if (is_null($separator)) {
      return self::$title;
    }
    if (!self::$title) {
      return '';
    }
    return implode($separator, self::$title);
  }

  /**
   * Sets the title for the page.
   *
   * @param  array  $title  The parts of the title.
   */
  public static function setTitle(array $title = array()) {
    self::$title = $title;
  }

  /**
   * Appends/prepends one or more parts to the page title.
   *
   * @param  mixed  $part  Part(s) to add to the title.
   */
  public static function addToTitle($part, $prepend = false) {
    if (!is_array($part)) {
      $part = array($part);
    }
    if ($prepend) {
      self::$title = array_merge($part, self::$title);
    } else {
      self::$title = array_merge(self::$title, $part);
    }
  }

  /**
   * Add a favourites icon (favicon) based on MIME type.
   *
   * Note that adding additional icons with the same MIME type will currently
   * overwrite the existing.
   *
   * You can choose to explicitly add an icon with the MIME type image/x-icon
   * to force an icon for Internet Explorer.  Otherwise Jerity will attempt to
   * automatically choose one of the defined icons to use with that MIME type.
   *
   * @param  string  $href  The location of the icon.
   * @param  string  $type  The MIME type of the icon to remove.
   */
  public static function addIcon($href, $type = self::ICON_ICO) {
    self::$icons[$type] = $href;
  }

  /**
   * Remove a favourites icon (favicon) based on MIME type.
   *
   * @param  string  $type  The MIME type of the icon to remove.
   */
  public static function removeIcon($type) {
    unset(self::$icons[$type]);
  }

  /**
   * Clears all favourites icons (favicons) from the page.
   */
  public static function clearIcons() {
    self::$icons = array();
  }

  /**
   * Gets the array of favourites icons for the page.  Will attempt to add an
   * additional item for Internet Explorer using the image/x-icon MIME type
   * which is now obsolete.
   *
   * @return  array  The icons for the current page.
   */
  public static function getIcons() {
    $i = array();
    foreach (self::$ICON_ORDER as $t) {
      if (isset(self::$icons[$t])) $i[$t] = self::$icons[$t];
    }
    if (!empty($i) && !isset($i[self::ICON_X_ICON])) {
      $i[self::ICON_X_ICON] = reset($i);
    }
    return $i;
  }

  /**
   * Add an XML namespace to the current page.  Note that these will only be
   * rendered if an XML-based render context is active.
   *
   * Note that if you add a new namespace with an existing prefix, the original
   * namespace will be clobbered.
   *
   * @param  string  $ns      The namespace to add.
   * @param  string  $prefix  The prefix to use.  We attempt a default if null.
   */
  public static function addXMLNamespace($ns, $prefix = null) {
    if (is_null($prefix)) {
      if (!isset(self::$XMLNS_PREFIXES[$ns])) {
        throw new InvalidArgumentException('No prefix supplied and no default available.');
      }
      $prefix = self::$XMLNS_PREFIXES[$ns];
    }
    self::$xml_namespaces['xmlns:'.$prefix] = $ns;
  }

  /**
   * Removes an XML namespace from the page.
   *
   * @param  string  $ns  The namespace to remove.
   */
  public static function removeXMLNamespace($ns) {
    $key = array_search($ns, self::$xml_namespaces, true);
    if ($key === false) return;
    unset(self::$xml_namespaces[$key]);
  }

  /**
   * Clears all XML namespaces currently added to the page.
   */
  public static function clearXMLNamespaces() {
    self::$xml_namespaces = array();
  }

  /**
   * Gets the array of XML namespaces for the page.  A default namespace is
   * automatically added if one can be chosen.
   *
   * @return  array  The XML namespaces for the current page.
   */
  public static function getXMLNamespaces() {
    return array_merge(self::getDefaultXMLNamespace(), self::$xml_namespaces);
  }

  /**
   * Gets the default namespace based on the render context.
   *
   * @return  array  A key-value pair.
   */
  protected static function getDefaultXMLNamespace() {
    $ns = array();
    $ctx = RenderContext::getGlobalContext();
    switch ($ctx->getLanguage()) {
      case RenderContext::LANG_XHTML:
        $ns['xmlns'] = self::XMLNS_XHTML;
        break;
      case RenderContext::LANG_WML:
        $ns['xmlns'] = self::XMLNS_WML;
        break;
    }
    return $ns;
  }

  /**
   * Get the language for this page.
   *
   * @return  string
   */
  public static function getLanguage() {
    return self::$language;
  }

  /**
   * Set the language for this page.
   *
   * @param  string  $language  The language to set.
   */
  public static function setLanguage($language) {
    self::$language = $language;
  }

  /**
   * Renders the head of the page.
   */
  public static function outputHead() {
    $ctx = RenderContext::getGlobalContext();
    echo $ctx->renderPreContent();
    $languages  = '';
    $namespaces = '';
    $profiles   = '';
    if ($l = self::getLanguage()) {
      $languages .= ' lang="'.$l.'"';
      if ($ctx->isXMLSyntax()) {
        $languages .= ' xml:lang="'.$l.'"';
      }
    }
    if ($ctx->isXMLSyntax()) {
      foreach (self::getXMLNamespaces() as $k => $v) {
        $namespaces .= ' '.$k.'="'.$v.'"';
      }
    }
    if (self::getIcons()) {
      # If we have favicons then add in a profile as defined by W3.
      # See: http://www.w3.org/2005/10/howto-favicon
      $profiles = ' profile="http://www.w3.org/2005/10/profile"';
    }
    echo '<html', $languages, $namespaces, '>', PHP_EOL;
    echo '<head', $profiles, '>', PHP_EOL;
    #Debug::comment('Metadata');
    #Debug::mark();
    #Debug::out(self::getMetadata());
    foreach (self::getMetadata() as $type => $a) {
      foreach ($a as $name => $content) {
        echo Tag::meta($name, $content, ($type === self::META_HTTP)), PHP_EOL;
      }
    }
    #Debug::comment('Title');
    #Debug::mark();
    #Debug::out(self::getTitle(null));
    echo '<title>', self::getTitle(), '</title>', PHP_EOL;
    #Debug::comment('Stylesheets');
    #Debug::mark();
    #Debug::out(self::getStylesheets());
    foreach (self::getStylesheets() as $type => $a) {
      foreach ($a as $href => $attrs) {
        echo Tag::link($href, $type, $attrs), PHP_EOL;
      }
    }
    #Debug::comment('Scripts');
    #Debug::mark();
    #Debug::out(self::getScripts());
    foreach (self::getScripts() as $type => $a) {
      foreach ($a as $href => $attrs) {
        $attrs['src'] = $href;
        echo Tag::script($type, null, $attrs), PHP_EOL;
      }
    }
    #Debug::comment('Other Resources');
    #Debug::mark();
    #Debug::out(self::getIcons());
    foreach (self::getIcons() as $type => $href) {
      if ($type === self::ICON_X_ICON) {
        $icon = Tag::link($href, $type, array('rel' => 'shortcut icon'));
        echo Tag::ieConditionalComment('IE', $icon);
      } else {
        echo Tag::link($href, $type, array('rel' => 'icon')), PHP_EOL;
      }
    }
    echo '</head>', PHP_EOL;
  }

  /**
   * Renders the foot of the page.
   */
  public static function outputFoot() {
    echo '</html>', PHP_EOL;
  }

  # }}} chrome general settings management
  ##############################################################################

}
