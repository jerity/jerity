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
  protected $content = array();

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
   * Store for links.
   *
   * @var  array
   */
  protected static $links = array();

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
   * Store for title separator. Defaults to ' &raquo; '.
   *
   * @var  string
   */
  protected static $titleSeparator = ' &raquo; ';

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

  /**
   * Create a new chrome template in a fluent API manner.
   *
   * @param  string  $t  The template to use.
   *
   * @return  Chrome
   * @see     self::__construct()
   *
   * @todo  Replace with PHP 5.3 late static binding support?
   */
  public static function create($t) {
    return new Chrome($t);
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
    # Call Renderable::render() for content items as necessary to ensure they are all strings.
    foreach ($this->content as &$content) {
      if (!($content instanceof Renderable)) continue;
      $content = $content->render();
    }
    unset($content); # break last element reference.

    # restart content iterator
    if (!is_null($this->contentIterator)) $this->contentIterator = null;
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
      $this->contentIterator = new ArrayIterator($this->content);
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
      if (!($i instanceof Renderable) && !is_string($i) && !(is_object($i) && method_exists($i, '__toString'))) {
        throw new InvalidArgumentException('Non-renderable or non-string item set as content.');
      }
    }
    $this->content = $items;
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
   * @param  string  $name        The name of the information.
   * @param  string  $value       The value to assign.
   * @param  string  $http_equiv  Whether this is HTTP header-equivalent metadata
   */
  public static function addMetadata($name, $value, $http_equiv = false) {
    # transition properties
    if ($http_equiv === self::META_HTTP) { trigger_error('Third argument to addMetadata() is now a boolean'); $http_equiv = true;  }
    if ($http_equiv === self::META_NAME) { trigger_error('Third argument to addMetadata() is now a boolean'); $http_equiv = false; }
    if ($http_equiv && strtolower($name) == 'content-type') {
      # munge "Content-Type" meta header so we can find it later
      $name = 'Content-Type';
    }
    self::$metadata[$http_equiv ? self::META_HTTP : self::META_NAME][$name] = $value;
  }

  /**
   * Removes some metadata from the page.
   *
   * @param  string  $name        The name of the information.
   * @param  string  $http_equiv  Whether this is HTTP header-equivalent metadata
   */
  public static function removeMetadata($name, $http_equiv = false) {
    if ($http_equiv === self::META_HTTP) { trigger_error('Third argument to addMetadata() is now a boolean'); $http_equiv = true;  }
    if ($http_equiv === self::META_NAME) { trigger_error('Third argument to addMetadata() is now a boolean'); $http_equiv = false; }
    if ($http_equiv && strtolower($name) == 'content-type') {
      # munge "Content-Type" meta header so we can find it later
      $name = 'Content-Type';
    }
    unset(self::$metadata[$http_equiv ? self::META_HTTP : self::META_NAME][$name]);
  }

  /**
   * Clears all metadata currently added to the page.
   *
   * @param  boolean  $http_equiv  If true, clears just HTTP-equivalent metadata. If false, clears
   *                               just named metadata. If null, clears all metadata.
   */
  public static function clearMetadata($http_equiv = null) {
    if (is_null($http_equiv)) {
      self::$metadata = array(self::META_HTTP=>array(), self::META_NAME=>array());
    } elseif ($http_equiv) {
      self::$metadata[self::META_HTTP] = array();
    } else {
      self::$metadata[self::META_NAME] = array();
    }
  }

  /**
   * Gets the array of metadata for the page.
   *
   * @param  boolean  $http_equiv  Whether to retrieve named or HTTP-equivalent metadata.
   *
   * @return  array  The metadata for the current page.
   */
  public static function getMetadata($http_equiv = false) {
    if (is_null($http_equiv)) {
      return self::$metadata;
    } elseif ($http_equiv) {
      if (!isset(self::$metadata[self::META_HTTP])) self::$metadata[self::META_HTTP] = array();
      return self::$metadata[self::META_HTTP];
    } else {
      if (!isset(self::$metadata[self::META_NAME])) self::$metadata[self::META_NAME] = array();
      return self::$metadata[self::META_NAME];
    }
  }

  /**
   * Adds a link element to the page.
   *
   * Note that this should <b>not</b> be used for stylesheets -- see
   * Chrome::addStylesheet instead. We also do not check for duplicates.
   *
   * @param  string  $rel      The relationship of the link (e.g. "alternate").
   * @param  string  $href     The target of the link.
   * @param  bool    $reverse  Whether this is a reverse ("rev") link.
   * @param  array   $attrs    Additional attributes for the link.
   *
   * @see Chrome::addStylesheet()
   */
  public static function addLink($rel, $href, $reverse = false, array $attrs = array()) {
    $type = $reverse ? 'rev' : 'rel';
    $attrs = array_merge(array( $type => $rel, 'href' => $href ), $attrs);
    self::$links[] = $attrs;
  }

  /**
   * Clears all links currently added to the page.
   */
  public static function clearLinks() {
    self::$links = array();
  }

  /**
   * Gets the array of links for the page
   *
   * @return  array  The links for the current page.
   */
  public static function getLinks() {
    return self::$links;
  }


  /**
   * Adds a script to the page, at most once. Scripts will be loaded in
   * order of ascending priority (i.e. priority 5 will be loaded before
   * priority 15).
   *
   * Note that 'charset' and 'defer' attributes can be passed into $attrs.
   *
   * @param  string  $href      The href of the file.
   * @param  int     $priority  Defines the order that scripts are loaded
   * @param  string  $type      The type of script.
   * @param  array   $attrs     An array of additional attributes.
   *
   * @throws  OutOfRangeException
   */
  public static function addScript($href, $priority = 50, $type = RenderContext::CONTENT_JS, array $attrs = array()) {
    if ($priority < 0) {
      throw new OutOfRangeException('Script priority must be zero or greater');
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
   * ascending priority order. If null is given for the $type argument, then
   * all scripts will be returned, indexed by type and then ordered by
   * priority.
   *
   * @param  string  $type  The type of script, or null for all scripts.
   *
   * @return  array  The scripts for the current page.
   */
  public static function getScripts($type = RenderContext::CONTENT_JS) {
    if (is_null($type)) {
      $s = self::$scripts;
      foreach ($s as $t => $a) {
        uasort($a, create_function('$a,$b', 'return Number::intcmp($a[\'priority\'], $b[\'priority\']);'));
        $s[$t] = array_values(array_map(create_function('$a', 'return $a[\'attrs\'];'), $a));
      }

    } elseif (!isset(self::$scripts[$type])) {
      $s = array();

    } else {
      $a = self::$scripts[$type];
      uasort($a, create_function('$a,$b', 'return Number::intcmp($a[\'priority\'], $b[\'priority\']);'));
      $s = array_values(array_map(create_function('$a', 'return $a[\'attrs\'];'), $a));
    }
    return $s;
  }

  /**
   * Adds a stylesheet to the page, at most once. Stylesheets will be loaded
   * in order of ascending priority (i.e. priority 5 will be loaded before
   * priority 15). This means that rules in stylesheets with higher priority
   * values will override lower-priority stylesheets.
   *
   * The media attribute should be a comma-separated string of one or more
   * media types.
   *
   * @param  string  $href      The href of the file.
   * @param  int     $priority  Defines the order that stylesheets are loaded.
   * @param  string  $type      The type of stylesheet.
   * @param  array   $attrs     An array of additional attributes.
   *
   * @throws  OutOfRangeException
   */
  public static function addStylesheet($href, $priority = 50, $type = RenderContext::CONTENT_CSS, array $attrs = array()) {
    if ($priority < 0) {
      throw new OutOfRangeException('Stylesheet priority must be zero or greater');
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
   * in ascending priority order by type.
   *
   * @return  array  The stylesheets for the current page.
   */
  public static function getStylesheets() {
    $s = self::$stylesheets;
    foreach ($s as $t => $a) {
      uasort($a, create_function('$a,$b', 'return Number::intcmp($a[\'priority\'], $b[\'priority\']);'));
      $s[$t] = array_map(create_function('$a', 'return $a[\'attrs\'];'), $a);
    }
    return $s;
  }

  /**
   * Gets the separator to be used when joining multi-part titles together.
   *
   * @return  string
   */
  public static function getTitleSeparator() {
    return self::$titleSeparator;
  }

  /**
   * Sets the separator to be used when joining multi-part titles together.
   * Note that this string is used exactly, and so should probably contain
   * whitespace at the start and end.
   *
   * @param  string  $separator  The string to use.
   */
  public static function setTitleSeparator($separator) {
    self::$titleSeparator = $separator;
  }

  /**
   * Gets the title for the page. If <kbd>false</kbd> is passed, the title is
   * returned as an array. Otherwise, it is joined together with
   * <var>$separator</var> (or the default separator if <kbd>null</kbd> is
   * passed).
   *
   * @param  string  $separator  The string to implode() with, null, or false
   *
   * @return  mixed
   */
  public static function getTitle($separator = null) {
    if ($separator === false) {
      return self::$title;
    } elseif (!self::$title) {
      return '';
    } elseif (is_null($separator)) {
      $separator = self::getTitleSeparator();
    }
    return implode($separator, self::$title);
  }

  /**
   * Sets the title for the page. This can either be an array of parts, or a
   * string representing a single part.
   *
   * @param  mixed  $title  The page title.
   */
  public static function setTitle($title) {
    if (!is_array($title)) {
      $title = array($title);
    }
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
   * Render the opening HTML and HEAD tags, with namespaces and profiles as
   * appropriate.
   */
  public static function outputOpeningTags() {
    $ctx = RenderContext::getGlobalContext();
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
  }

  /**
   * Render any metadata tags, ensuring that the "Content-Type" HTTP-equivalent
   * tag is rendered first if present.
   */
  public static function outputMetaTags() {
    $metadata = self::getMetadata(null);
    # ensure content-type is output first, if we have one
    if (isset($metadata[self::META_HTTP]['Content-Type'])) {
      # stop ourselves processing it again
      $content_type = $metadata[self::META_HTTP]['Content-Type'];
      unset($metadata[self::META_HTTP]['Content-Type']);
      # output the tag
      echo Tag::meta('Content-Type', $content_type, true), PHP_EOL;
    }
    # render other metadata
    foreach ($metadata as $type => $a) {
      foreach ($a as $name => $content) {
        echo Tag::meta($name, $content, ($type === self::META_HTTP)), PHP_EOL;
      }
    }
    if (isset($content_type)) {
      $metadata[self::META_HTTP]['Content-Type'] = $content_type;
    }
  }

  /**
   * Renders the TITLE tag for the page.
   */
  public static function outputTitleTag() {
    echo '<title>', self::getTitle(), '</title>', PHP_EOL;
  }

  /**
   * Renders any custom LINK tags for the page header.
   */
  public static function outputLinkTags() {
    foreach (self::getLinks() as $link) {
      echo Tag::renderTag('link', $link), PHP_EOL;
    }
  }

  /**
   * Renders any stylesheets attached to the page, taking their priorities into
   * account.
   */
  public static function outputStylesheetTags() {
    foreach (self::getStylesheets() as $type => $a) {
      foreach ($a as $href => $attrs) {
        echo Tag::link($href, $type, $attrs), PHP_EOL;
      }
    }
  }

  /**
   * Renders the script tags that reference an external file.
   */
  public static function outputExternalScriptTags() {
    foreach (self::getScripts(null) as $type => $a) {
      foreach ($a as $href => $attrs) {
        echo Tag::script($type, '', $attrs), PHP_EOL;
      }
    }
  }

  /**
   * Render any favicon tags, including special handling for Internet Explorer
   * to allow it to properly recognise .ico format icons.
   */
  public static function outputFaviconTags() {
    foreach (self::getIcons() as $type => $href) {
      if ($type === self::ICON_X_ICON) {
        $icon = Tag::link($href, $type, array('rel' => 'shortcut icon'));
        echo Tag::ieConditionalComment('IE', $icon);
      } else {
        echo Tag::link($href, $type, array('rel' => 'icon')), PHP_EOL;
      }
    }
  }

  /**
   * Output the closing HEAD tag.
   */
  public static function outputEndHead() {
    echo '</head>', PHP_EOL;
  }

  /**
   * Outputs the HTML head of the page.
   */
  public static function outputHead() {
    $ctx = RenderContext::getGlobalContext();
    echo $ctx->renderPreContent();

    # Opening <html> and <head> tags
    self::outputOpeningTags();

    # Any <meta> tags
    self::outputMetaTags();

    # page title
    self::outputTitleTag();

    # links
    self::outputLinkTags();

    # stylesheets
    self::outputStylesheetTags();

    # external scripts
    self::outputExternalScriptTags();

    # favicons
    self::outputFaviconTags();

    self::outputEndHead();
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
