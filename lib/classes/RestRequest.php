<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

class RestRequest {
  protected $url;
  protected $clean_url;
  protected $verb;
  protected $headers;
  protected $get_args;
  protected $body;
  protected $response_format;
  protected $forced_response_format = null;
  protected $matches = null;

  /**
   * Construct a REST request.
   */
  public function __construct($url, $verb, array $get_args, $body, array $headers, $response_format = null) {
    // remove query string
    $url = preg_replace('/\?.*$/', '', $url);
    $this->url = $url;
    $this->clean_url = preg_replace('/\.(json|xml)$/', '', $url);
    $this->verb = $verb;
    $this->get_args = $get_args;
    $this->body = $body;
    $this->headers = $headers;
    if ($response_format !== null) {
      $this->response_format = $response_format;
    } else {
      $this->setResponseFormatFromRequest();
    }
  }

  public function getMatches() {
    return $this->matches;
  }

  public function setMatches(array $matches=null) {
    $this->matches = $matches;
    return $this;
  }

  public function getUrl($remove_extension=true) {
    if ($remove_extension) {
      return $this->clean_url;
    } else {
      return $this->url;
    }
  }

  public function getVerb() {
    return $this->verb;
  }

  public function getArgs() {
    return $this->get_args;
  }

  public function getRawBody() {
    return $this->body;
  }

  public function getJsonBody() {
    return json_decode($this->body, true);
  }

  public function getXmlBody() {
    $doc = new DOMDocument();
    $doc->loadXML($this->body);
    return $doc;
  }

  public function getXmlBodyArray() {
    // TODO: implement
  }

  public function getFormBody() {
    $array = array();
    parse_str(trim(strtolower($this->body)), $array);
    if ($array && isset($array['id'])) {
      $update_id = round(0+$array['id']);
    }
    unset($array);
  }

  public function getBody() {
    switch ($this->getBodyContentType()) {
      case 'application/x-www-form-urlencoded':
        return $this->getFormBody();

      case 'application/json':
        return $this->getJsonBody();

      case 'application/xml':
      case 'text/xml':
        return $this->getXmlBody();

      case 'text/plain':
        // hack, undocumented
        return $this->getRawBody();
    }

    return null;
  }

  public function getBodyContentType() {
    $content_type = null;

    if (!isset($headers['Content-Type'])) {
      // try to autodetect based on actual content
      if (preg_match('/^{\s*"[^"]+"\s*:/', $this->body)) {
        $content_type = 'application/json';
      } elseif (preg_match('/^(?:<\?xml[^?>]+\?>)\s*<[^>]+>/i', $this->body)) {
        $content_type = 'application/xml';
      } elseif (preg_match('/^[a-zA-Z0-9_.~-]+=[^&]*&/', $this->body)) {
        $content_type = 'application/x-www-form-urlencoded';
      }
    } else {
      $content_type = $headers['Content-Type'];
    }

    return $content_type;
  }

  public function getHeaders() {
    return $this->headers;
  }

  public function getResponseFormat() {
    if ($this->forced_response_format !== null) {
      return $this->forced_response_format;
    }
    return $this->response_format;
  }

  public function setResponseFormat($format) {
    $this->forced_response_format = $format;
    return $this;
  }

  public function setResponseFormatFromRequest() {
    $this->setResponseFormatFromUrl();
    if ($this->response_format === null) {
      $this->setResponseFormatFromHeaders();
    }
    return $this;
  }

  public function setResponseFormatFromUrl() {
    if ($this->clean_url != $this->url) {
      $this->response_format = substr($this->url, strrpos($this->url, '.')+1);
    }
    return $this;
  }

  public function setResponseFormatFromHeaders() {
    if (isset($this->headers['Accept'])) {
      $format = null;
      // examine Accept: header
      $accept = array_map('trim', explode(',', $this->headers['Accept']));
      foreach ($accept as $type) {
        $type = explode(';', $type);
        $type = trim($type[0]);
        if (in_array($type, array('application/json', 'application/xml', 'text/xml'))) {
          $format = explode('/', $type);
          $format = $format[1];
          break;
        }
      }
      if ($format !== null) {
        $this->response_format = $format;
      }
    }
    return $this;
  }

  public static function createFromCurrent() {
    // XXX: The spec says that the verb is case-sensitive, but user-agents
    // don't really honour that
    return new RestRequest(
      RestManager::getCurrentUrl(),
      strtoupper($_SERVER['REQUEST_METHOD']),
      $_GET,
      file_get_contents('php://input'),
      RestManager::getRequestHeaders()
    );
  }

}
