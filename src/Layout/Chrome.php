<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.layout
 */

namespace Jerity\Layout;

use \Jerity\Core\RenderContext;
use \Jerity\Core\Renderable;
use \Jerity\Core\Tag;
use \Jerity\Util\Arrays;
use \Jerity\Util\Number;

/**
 * The Chrome template is used to specify the default look and feel for a site
 * and can be manipulated using various methods provided to make it easier to
 * add general styling, scripting and metadata to a page.
 *
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.layout
 */
class Chrome extends AbstractTemplate {

  ##############################################################################
  # chrome content item management {{{

  /**
   * @var  mixed  One or more content items added to this template.
   */
  protected $content = array();

  /**
   * @var  \ArrayIterator  An iterator over this template's content.
   */
  protected $contentIterator = null;

  # }}} chrome content item management
  ##############################################################################

  ##############################################################################
  # chrome general settings management {{{

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
   * HTTP response code.
   *
   * @var  int
   */
  protected static $response_code = 200;

  /**
   * Store for HTTP headers.
   *
   * @var  array
   */
  protected static $headers = array();

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
   * Store for title separator. Defaults to ' &laquo; '.
   *
   * @var  string
   */
  protected static $titleSeparator = ' &laquo; ';

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

  /**
   * Whether the <html> tag should be wrapped in conditional comments.
   *
   * @var  boolean
   */
  protected static $html_cc_wrap = null;

  /**
   * Used when matching filenames of external resources for wrapping in
   * Internet Explorer conditional comments.
   *
   * @var  string
   */
  protected static $resource_iecc_regex = '/\.ie(?:\.([gl]te?)?([_\d]+))?\./S';

  /**
   * Whether to group linked resources that are wrapped in Internet Explorer
   * conditional comments (true), or to only use priority sorting on resources
   * (false).
   *
   * @var  boolean
   */
  protected static $resource_iecc_group = true;

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
    if (!$this->contentIterator instanceof \Iterator) {
      $this->contentIterator = new \ArrayIterator($this->content);
    } else {
      $this->contentIterator->next();
    }
    if (!$this->contentIterator->valid()) {
      return null;
    }
    return $this->contentIterator->current();
  }

  /**
   * Returns true if there are more content items.
   *
   * @return  boolean
   */
  public function hasNextContent() {
    if (!$this->contentIterator instanceof \Iterator) {
      return (boolean) count($this->content);
    } else {
      return $this->contentIterator->valid();
    }
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
   * @return  Chrome  The current object, for method chaining.
   *
   * @throws  \InvalidArgumentException
   */
  public function setContent() {
    if (func_num_args() < 1) {
      throw new \InvalidArgumentException('You must specify a content item.');
    }
    $items = Arrays::flatten(func_get_args());
    foreach ($items as $i) {
      if (!($i instanceof Renderable) && !is_string($i) && !(is_object($i) && method_exists($i, '__toString'))) {
        throw new \InvalidArgumentException('Non-renderable or non-string item set as content.');
      }
    }
    $this->content = $items;
    $this->contentIterator = null;
    return $this;
  }

  /**
   * Clears the content item list for this template.
   *
   * @return  Chrome  The current object, for method chaining.
   */
  public function clearContent() {
    $this->content = null;
    return $this;
  }

  # }}} chrome content item management
  ##############################################################################

  ##############################################################################
  # chrome general settings management {{{

  public static function setResponseCode($code) {
    self::$response_code = $code;
  }

  public static function getResponseCode() {
    return self::$response_code;
  }

  public static function getResponseCodeText($code = null) {
    if ($code === null) {
      $code = self::$response_code;
    }
    $responses = array(
      200 => 'OK',
      201 => 'Created',
      400 => 'Bad Request',
      401 => 'Authorization Required',
      403 => 'Forbidden',
      404 => 'Not Found',
      500 => 'Internal Server Error',
      501 => 'Not Implemented',
    );
    if (!isset($responses[$code])) {
      throw new \InvalidArgumentException('Unrecognised HTTP response code: '.$code);
    }
    return $responses[$code];
  }

  /**
   * Adds an HTTP header to the page which will be output when the Chrome is
   * renderered.
   *
   * @param  string          $header   The header to set.
   * @param  string | array  $content  The content to set.
   * @param  boolean         $replace  Whether to replace all previously
   *                                   defined headers of this type.
   */
  public static function addHeader($header, $content, $replace = true) {
    if ($replace) {
      self::$headers[$header] = $content;
    } else {
      if (!is_array(self::$headers[$header])) self::$headers[$header] = array(self::$headers[$header]);
      if (!is_array($content)) $content = array($content);
      self::$headers[$header] = array_unique(array_merge(self::$headers[$header], $content));
    }
  }

  /**
   * Removes an HTTP header from the page.
   *
   * @param  string  $header   The header to remove.
   * @param  string  $content  If null remove all headers of type, else only
   *                           remove the one specified.
   */
  public static function removeHeader($header, $content = null) {
    if (!isset(self::$headers[$header])) return;
    if (is_null($content)) {
      unset(self::$headers[$header]);
    } else {
      if (is_array(self::$headers[$header])) {
        self::$headers[$header] = array_diff(self::$headers[$header], array($content));
      } else if (self::$headers[$header] == $content) {
        unset(self::$headers[$header]);
      }
    }
  }

  /**
   * Clears all HTTP headers set on the page.
   */
  public static function clearHeaders() {
    self::$headers = array();
  }

  /**
   * Returns the HTTP headers set on the page.
   *
   * @return  array  HTTP headers set on the page.
   */
  public static function getHeaders() {
    return self::$headers;
  }

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
    if ($http_equiv === Tag::META_HTTP) { trigger_error('Third argument to addMetadata() is now a boolean'); $http_equiv = true;  }
    if ($http_equiv === Tag::META_NAME) { trigger_error('Third argument to addMetadata() is now a boolean'); $http_equiv = false; }
    if ($http_equiv && strtolower($name) == 'content-type') {
      trigger_error('Content-Type should be set by changing render context.');
      return;
    }
    self::$metadata[$http_equiv ? Tag::META_HTTP : Tag::META_NAME][$name] = $value;
  }

  /**
   * Removes some metadata from the page.
   *
   * @param  string  $name        The name of the information.
   * @param  string  $http_equiv  Whether this is HTTP header-equivalent metadata
   */
  public static function removeMetadata($name, $http_equiv = false) {
    if ($http_equiv === Tag::META_HTTP) { trigger_error('Third argument to addMetadata() is now a boolean'); $http_equiv = true;  }
    if ($http_equiv === Tag::META_NAME) { trigger_error('Third argument to addMetadata() is now a boolean'); $http_equiv = false; }
    if ($http_equiv && strtolower($name) == 'content-type') {
      trigger_error('Content-Type should be set by changing render context.');
      return;
    }
    unset(self::$metadata[$http_equiv ? Tag::META_HTTP : Tag::META_NAME][$name]);
  }

  /**
   * Clears all metadata currently added to the page.
   *
   * @param  boolean  $http_equiv  If true, clears just HTTP-equivalent metadata. If false, clears
   *                               just named metadata. If null, clears all metadata.
   */
  public static function clearMetadata($http_equiv = null) {
    if (is_null($http_equiv)) {
      self::$metadata = array(Tag::META_HTTP => array(), Tag::META_NAME => array());
    } elseif ($http_equiv) {
      self::$metadata[Tag::META_HTTP] = array();
    } else {
      self::$metadata[Tag::META_NAME] = array();
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
      if (!isset(self::$metadata[Tag::META_HTTP])) self::$metadata[Tag::META_HTTP] = array();
      return self::$metadata[Tag::META_HTTP];
    } else {
      if (!isset(self::$metadata[Tag::META_NAME])) self::$metadata[Tag::META_NAME] = array();
      return self::$metadata[Tag::META_NAME];
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
   * Files that have names targeted at specific versions of Internet Explorer
   * will be wrapped in conditional comments.  If external resource grouping
   * is enabled, then the files will be grouped to reduce excess markup.
   *
   * Note that 'charset' and 'defer' attributes can be passed into $attrs.
   *
   * @see  setExternalResourceGrouping()
   *
   * @param  string  $href      The href of the file.
   * @param  int     $priority  Defines the order that scripts are loaded
   * @param  array   $attrs     An array of additional attributes.
   *
   * @throws  \OutOfRangeException
   */
  public static function addScript($href, $priority = 50, array $attrs = array()) {
    if ($priority < 0) {
      throw new \OutOfRangeException('Script priority must be zero or greater');
    }
    if (!isset($attrs['charset'])) {
      $charset = RenderContext::get()->getCharset();
      if (!is_null($charset)) $attrs['charset'] = strtolower($charset);
    }
    $attrs['src'] = $href;
    $attrs = array_merge(
      array('type' => Tag::getDefaultScriptContentType()),
      $attrs
    );
    self::$scripts[$attrs['type']][$href] = array(
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
   * Files that have names targeted at specific versions of Internet Explorer
   * will be wrapped in conditional comments.  If external resource grouping
   * is enabled, then the files will be grouped to reduce excess markup.
   *
   * @see setExternalResourceGrouping()
   *
   * @param  string   $type   The type of script, or null for all scripts.
   * @param  boolean  $group  Whether to group scripts by browser specific
   *                          information provided in filename.
   *
   * @return  array  The scripts for the current page.
   */
  public static function getScripts($type = RenderContext::CONTENT_JS, $group = false) {
    # Check if there are any scripts of any type at all and if not return an
    # empty array.
    if (empty(self::$scripts)) return array();
    # Check if there are any scripts of the specified type to retrieve and if
    # not return an empty array.
    if (!is_null($type) && empty(self::$scripts[$type])) return array();
    # Use either the entire script array, or just a specific type.
    if (is_null($type)) {
      $scripts = self::$scripts;
    } else {
      $scripts = array($type => self::$scripts[$type]);
    }
    # Perform the sorting.
    foreach ($scripts as $t => $a) {
      self::sortExternalsByPriority($a);
      if ($group) self::sortExternalsByGroup($a);
      $scripts[$t] = $a;
    }
    # If we are only expecting one type, return that type only.
    return (is_null($type) ? $scripts : array_shift($scripts));
  }

  /**
   * Adds a persistent stylesheet to the page, at most once. Stylesheets will
   * be loaded in order of ascending priority (i.e. priority 5 will be loaded
   * before priority 15). This means that rules in stylesheets with higher
   * priority values will override lower-priority stylesheets.
   *
   * The media attribute should be a comma-separated string of one or more
   * media types.
   *
   * Note that if alternate stylesheets are defined, stylesheets added by this
   * method will always be applied.
   *
   * Files that have names targeted at specific versions of Internet Explorer
   * will be wrapped in conditional comments.  If external resource grouping
   * is enabled, then the files will be grouped to reduce excess markup.
   *
   * @see setExternalResourceGrouping()
   *
   * @param  string  $href      The href of the file.
   * @param  int     $priority  Defines the order that stylesheets are loaded.
   * @param  array   $attrs     An array of additional attributes.
   *
   * @throws  \OutOfRangeException
   */
  public static function addStylesheet($href, $priority = 50, array $attrs = array()) {
    if ($priority < 0) {
      throw new \OutOfRangeException('Stylesheet priority must be zero or greater');
    }
    if (RenderContext::get()->getVersion() < 5) {
      if (!isset($attrs['charset'])) {
        $charset = RenderContext::get()->getCharset();
        if (!is_null($charset)) $attrs['charset'] = strtolower($charset);
      }
    }
    if (!isset($attrs['media'])) {
      $media = Tag::getDefaultStyleMediaType();
      if (!is_null($media)) $attrs['media'] = $media;
    }
    $attrs = array_merge(
      array('rel' => 'stylesheet', 'type' => Tag::getDefaultStyleContentType()),
      $attrs,
      array('href' => $href)
    );
    self::$stylesheets[$href] = array(
      'priority' => $priority,
      'attrs'    => $attrs,
    );
  }

  /**
   * Adds an alternate stylesheet to the page, at most once.
   *
   * There should be at most one preferred alternate stylesheet per page. There
   * can only be one active alternate stylesheet at a time, which may be
   * changed by the user agent.
   *
   * @param  string  $href       The href of the file.
   * @param  string  $title      The title for the stylesheet.
   * @param  bool    $preferred  Whether this alternative stylesheet is enabled by default
   * @param  int     $priority   Defines the order that stylesheets are output.
   * @param  array   $attrs      An array of additional attributes.
   *
   * @throws  \OutOfRangeException
   * @see     Chrome::addStylesheet()
   */
  public static function addAlternateStylesheet($href, $title, $preferred = false, $priority = 50, array $attrs = array()) {
    $attrs = array_merge(
      $attrs,
      array('rel' => $preferred ? 'stylesheet' : 'alternate stylesheet', 'title' => $title)
    );
    self::addStylesheet($href, $priority, $attrs);
  }

  /**
   * Removes a stylesheet from the page.
   *
   * @param  string  $href  The href of the file.
   */
  public static function removeStylesheet($href) {
    unset(self::$stylesheets[$href]);
  }

  /**
   * Clears all stylesheets currently added to the page.
   */
  public static function clearStylesheets() {
    self::$stylesheets = array();
  }

  /**
   * Gets the array of stylesheets for the page.  The stylesheets are returned
   * in ascending priority order.
   *
   * Files that have names targeted at specific versions of Internet Explorer
   * will be wrapped in conditional comments.  If external resource grouping
   * is enabled, then the files will be grouped to reduce excess markup.
   *
   * @see setExternalResourceGrouping()
   *
   * @param  boolean  $group  Whether to group scripts by browser specific
   *                          information provided in filename.
   *
   * @return  array  The stylesheets for the current page.
   */
  public static function getStylesheets($group = false) {
    # Check if there are any stylesheets and if not return an empty array.
    if (empty(self::$stylesheets)) return array();
    # Use either the entire stylesheet array.
    $stylesheets = self::$stylesheets;
    # Perform the sorting.
    self::sortExternalsByPriority($stylesheets);
    if ($group) self::sortExternalsByGroup($stylesheets);
    # If we are only expecting one type, return that type only.
    return $stylesheets;
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
   * Appends/prepends one or more parts to the page title.  By default we
   * prepend to the title because we want the most specific part at the
   * beginning.
   *
   * @param  mixed  $part    Part(s) to add to the title.
   * @param  bool   $append  Whether to append to the title.
   */
  public static function addToTitle($part, $append = false) {
    if (!is_array($part)) {
      $part = array($part);
    }
    if ($append) {
      self::$title = array_merge(self::$title, $part);
    } else {
      self::$title = array_merge($part, self::$title);
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
        throw new \InvalidArgumentException('No prefix supplied and no default available.');
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
   * @see  http://wiki.whatwg.org/wiki/HTML_vs._XHTML
   *
   * @return  array  A key-value pair.
   */
  protected static function getDefaultXMLNamespace() {
    $ns = array();
    $ctx = RenderContext::get();
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
    self::addHeader('Content-Language', $language);
    self::addMetadata('Content-Language', $language, true);
  }

  /**
   * Get whether to wrap the <html> tag in conditional comments.
   *
   * @return  boolean
   */
  public static function getWrapHtmlCC() {
    return self::$html_cc_wrap;
  }

  /**
   * Set whether to wrap the <html> tag in conditional comments.
   *
   * @param  boolean  Whether the <html> tag should be wrapped with conditional comments.
   */
  public static function setWrapHtmlCC($v = true) {
    self::$html_cc_wrap = $v;
  }

  /**
   * Output the HTTP response code header.
   */
  public static function outputResponseCode() {
    header(implode(' ', array($_SERVER['SERVER_PROTOCOL'], self::getResponseCode(), self::getResponseCodeText())), true, self::getResponseCode());
  }

  /**
   * Outputs HTTP headers.  If the values are arrays, we automatically output
   * multiple times and do not replace the previous header.
   */
  public static function outputHeaders() {
    # HTTP response code first
    self::outputResponseCode();

    header('Content-Type: ' . RenderContext::get()->getContentType());

    foreach (self::getHeaders() as $k => $v) {
      if (is_array($v)) {
        header("{$k}: {$v[0]}");
        if (count($v) > 1) {
          $r = array_slice($v, 1);
          foreach ($r as $i) {
            header("{$k}: {$i}", false);
          }
        }
      } else {
        header("{$k}: {$v}");
      }
    }
  }

  /**
   * Render the opening HTML and HEAD tags, with namespaces and profiles as
   * appropriate.
   *
   * HTML5 can have a default XML namespace for XHTML to aid migration.
   *
   * @see  http://wiki.whatwg.org/wiki/HTML_vs._XHTML
   */
  public static function outputOpeningTags() {
    $ctx = RenderContext::get();
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
    if ($ctx->getVersion() == 5) {
      # head[profile] is no longer allowed in (X)HTML5.
      $profiles = '';
    }
    if (self::$html_cc_wrap) {
      echo '<!--[if IE 6]><html', $languages, $namespaces, ' class="no-js ie6"><![endif]-->', PHP_EOL;
      echo '<!--[if IE 7]><html', $languages, $namespaces, ' class="no-js ie7"><![endif]-->', PHP_EOL;
      echo '<!--[if IE 8]><html', $languages, $namespaces, ' class="no-js ie8"><![endif]-->', PHP_EOL;
      echo '<!--[if IE 9]><html', $languages, $namespaces, ' class="no-js ie9"><![endif]-->', PHP_EOL;
      echo '<!--[if (gt IE 9)|!(IE)]><!--><html', $languages, $namespaces, ' class="no-js"><!--<![endif]-->', PHP_EOL;
    } else {
      echo '<html', $languages, $namespaces, '>', PHP_EOL;
    }
    echo '<head', $profiles, '>', PHP_EOL;
  }

  /**
   * Render any metadata tags, ensuring that the "Content-Type" HTTP-equivalent
   * tag is rendered first if present.
   */
  public static function outputMetaTags() {
    # If a charset is specified in the render context, set it in the
    # content-type. This is a special case - the charset should come before any
    # other data in the head.
    $ctx = RenderContext::get();
    if ($ctx->getCharset()) {
      $charset = strtolower($ctx->getCharset());
      if ($ctx->getVersion() == 5) { # only if (X)HTML5
        echo Tag::meta(Tag::META_CHAR, null, $charset), PHP_EOL;
      } else {
        echo Tag::meta(Tag::META_HTTP, 'Content-Type', "{$ctx->getContentType()};charset={$charset}"), PHP_EOL;
      }
    }
    # Render other metadata
    $metadata = self::getMetadata(null);
    ksort($metadata);
    foreach ($metadata as $type => $a) {
      foreach ($a as $name => $content) {
        echo Tag::meta($type, $name, $content), PHP_EOL;
      }
    }
    if (isset($content_type)) {
      $metadata[Tag::META_HTTP]['Content-Type'] = $content_type;
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
   * Renders any stylesheets attached to the page, taking their priorities and
   * (optionally) groupings into account.
   */
  public static function outputStylesheetTags() {
    $stylesheets = self::getStylesheets(self::getExternalResourceGrouping());
    self::outputExternalResources($stylesheets, 'style');
  }

  /**
   * Renders the script tags that reference an external file, taking their
   * priorities and (optionally) groupings into account.
   */
  public static function outputExternalScriptTags() {
    $scripts = self::getScripts(null, self::getExternalResourceGrouping());
    foreach ($scripts as $type => $items) {
      self::outputExternalResources($items, 'script');
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
    # HTTP Headers
    self::outputHeaders();
    # XML declaration and doctype (if required).
    echo RenderContext::get()->renderPreContent();
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

  ##############################################################################
  # external resource helper functions {{{

  /**
   * Sorts the given array by priority and then strips the priority information.
   *
   * @param  &array  $items  The array to sort.
   */
  protected static function sortExternalsByPriority(array &$items) {
    uasort($items, function ($a, $b) {
      return Number::intcmp($a['priority'], $b['priority']);
    });
    $items = array_map(function ($a) {
      return $a['attrs'];
    }, $items);
  }

  /**
   * Sorts the given array into groups by matching components of the filename.
   * The main purpose of this is to automatically group various styles or
   * scripts specific to Internet Explorer so that they can be wrapped in
   * conditional comments as a single block.
   *
   * Groupings are ordered to be most general first, most specific last. The
   * order of sorting is:
   *  - Scripts for all browsers
   *  - Scripts for any version of IE
   *  - Scripts for any IE <=, < VERSION (interleaved, descending)
   *  - Scripts for any IE >=, > VERSION (interleaved, ascending)
   *  - Scripts for any IE ==    VERSION (ascending)
   *
   * @param  &array  $items  The array to sort.
   */
  protected static function sortExternalsByGroup(array &$items) {
    $sorted = array(
      '**' => array(),
      'ie' => array(),
      'lt' => array(),
      'gt' => array(),
      'eq' => array(),
    );
    foreach ($items as $href => $item) {
      if (preg_match(self::$resource_iecc_regex, $href, $m)) {
        # no version
        if (empty($m[2])) {
          $sorted['ie'][$href] = $item;
        } else {
          $version = strtr($m[2], '_', '.');
          # ranged version
          if (!empty($m[1])) {
            $x = $m[1][0].'t';
            if (!isset($sorted[$x][$version])) {
              $sorted[$x][$version] = array($x.'e' => array(), $x => array());
            }
            $sorted[$x][$version][$m[1]][$href] = $item;
          # exact version
          } else {
            $sorted['eq'][$version][$href] = $item;
          }
        }
      } else {
        $sorted['**'][$href] = $item;
      }
    }
    # perform numerical sorting
    krsort($sorted['lt']);
    ksort($sorted['gt']);
    ksort($sorted['eq']);
    # return in original array
    $items = $sorted;
  }

  /**
   * A helper function to output grouped external resources wrapped in the
   * appropriate conditional comment for the group.
   *
   * @param  &array  $items  List of external resource links to be output.
   * @param  array   $expr   The expression parts for the conditional comment.
   * @param  strong  $type   Used to determine the tag function (style, script)
   */
  protected static function outputGroupedExternalResources(array &$items, array $expr, $type) {
    switch ($type) {
      case 'script':
        $function = array('\Jerity\Core\Tag', 'script');
        $parameters = array(null);
        break;
      case 'style':
        $function = array('\Jerity\Core\Tag', 'link');
        $parameters = array(Tag::getDefaultStyleContentType());
        break;
      default:
        throw new \InvalidArgumentException('Unrecognised external resource type: '.$type);
    }
    $content = array();
    foreach ($items as $href => $item) {
      $params = $parameters;
      if ($type == 'style') array_unshift($params, $href);
      $params[] = $item;
      $content[] = call_user_func_array($function, $params);
    }
    $expression = join(' ', $expr);
    $content = join(PHP_EOL, $content);
    $newline = (count($items) > 1);
    echo Tag::ieConditionalComment($expression, $content, $newline);
  }

  /**
   * Outputs tags for external resources based on the type passed in.  If we
   * have a resource that should be grouped within conditional comments, then
   * the rendering is handed off to a helper function.
   *
   * @see outputGroupedExternalResources()
   *
   * @param  &array  $items  List of external resource groups to be output.
   * @param  strong  $type   Used to determine the tag function (style, script)
   */
  protected static function outputExternalResources(&$items, $type) {
    switch ($type) {
      case 'script':
        $function = array('\Jerity\Core\Tag', 'script');
        $parameters = array(null);
        break;
      case 'style':
        $function = array('\Jerity\Core\Tag', 'link');
        $parameters = array(Tag::getDefaultStyleContentType());
        break;
      default:
        throw new \InvalidArgumentException('Unrecognised external resource type: '.$type);
    }
    if (self::getExternalResourceGrouping()) {
      foreach ($items as $key => $items1) {
        switch ($key) {
          case 'lt':
          case 'gt':
            foreach ($items1 as $version => $items2) {
              foreach ($items2 as $operator => $items3) {
                if (empty($items3)) continue;
                self::outputGroupedExternalResources($items3, array($operator, 'IE', $version), $type);
              }
            }
            break;
          case 'eq':
            foreach ($items1 as $version => $items2) {
              if (empty($items2)) continue;
              self::outputGroupedExternalResources($items2, array('IE', $version), $type);
            }
            break;
          case 'ie':
            if (empty($items1)) continue;
            self::outputGroupedExternalResources($items1, array('IE'), $type);
            break;
          case '**':
          default:
            foreach ($items1 as $href => $attrs) {
              $params = $parameters;
              if ($type == 'style') array_unshift($params, $href);
              $params[] = $attrs;
              echo call_user_func_array($function, $params), PHP_EOL;
            }
        }
      }
    } else {
      foreach ($items as $href => $attrs) {
        $params = $parameters;
        if ($type == 'style') array_unshift($params, $href);
        $params[] = $attrs;
        $content = call_user_func_array($function, $params);
        if (preg_match(self::$resource_iecc_regex, $content, $m)) {
          $expression = (!empty($m[1]) ? $m[1].' ' : '');
          $expression .= 'IE';
          $expression .= (!empty($m[2]) ? ' '.strtr($m[2], '_', '.') : '');
          $content = Tag::ieConditionalComment($expression, $content);
        } else {
          $content .= PHP_EOL;
        }
        echo $content;
      }
    }
  }

  /**
   * Whether to group linked resources that are wrapped in Internet Explorer
   * conditional comments, or to only use priority sorting on resources.
   *
   * @return  boolean  Whether to enable grouping.
   */
  public static function getExternalResourceGrouping() {
    return self::$resource_iecc_group;
  }

  /**
   * Whether to group linked resources that are wrapped in Internet Explorer
   * conditional comments, or to only use priority sorting on resources.
   *
   * @param  boolean  $b  Whether to enable grouping.
   */
  public static function setExternalResourceGrouping($b) {
    self::$resource_iecc_group = $b;
  }

  # }}} external resource helper functions
  ##############################################################################

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
