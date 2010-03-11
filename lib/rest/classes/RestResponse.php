<?php

class RestResponse {
  const OK = 200;
  const CREATED = 201;
  const NO_CONTENT = 204;
  const RESET_CONTENT = 205;
  const PARTIAL_CONTENT = 206;
  const MOVED_PERMANENTLY = 301;
  const FOUND = 302;
  const SEE_OTHER = 303;
  const NOT_MODIFIED = 304;
  const TEMPORARY_REDIRECT = 307;
  const BAD_REQUEST = 400;
  const UNAUTHORIZED = 401;
  const FORBIDDEN = 403;
  const NOT_FOUND = 404;
  const METHOD_NOT_ALLOWED = 405;
  const NOT_ACCEPTABLE = 406;
  const CONFLICT = 409;
  const GONE = 410;
  const LENGTH_REQUIRED = 411;
  const PRECONDITION_FAILED = 412;
  const REQUEST_ENTITY_TOO_LARGE = 413;
  const REQUEST_URI_TOO_LONG = 414;
  const UNSUPPORTED_MEDIA_TYPE = 415;
  const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
  const EXPECTATION_FAILED = 417;
  const IM_A_TEAPOT = 418;
  const INTERNAL_ERROR = 500;
  const NOT_IMPLEMENTED = 501;
  const BAD_GATEWAY = 502;
  const SERVICE_UNAVAILABLE = 503;
  const GATEWAY_TIMEOUT = 504;
  const HTTP_VERSION_NOT_SUPPORTED = 505;

  protected $code = 200;
  protected $content = null;
  protected $format = 'json';
  protected $location = null;
  protected $force_envelope = false;

  public function __construct($code, $content, RestRequest $request) {
    $this->code = $code;
    $this->content = $content;
    $this->setFormat($request->getResponseFormat());
  }

  public function setFormat($format) {
    $this->format = $format;
  }

  public function setLocation($location) {
    $this->location = $location;
  }

  public function setForceEnvelope($force=true) {
    $this->force_envelope = $force;
  }

  protected function statusFromCode($code) {
    $statuses = array(
      self::OK                              => 'OK',
      self::CREATED                         => 'Created',
      202                                   => 'Accepted',
      203                                   => 'Non-Authoritative Information',
      self::NO_CONTENT                      => 'No Content',
      self::RESET_CONTENT                   => 'Reset Content',
      self::PARTIAL_CONTENT                 => 'Partial Content',
      300                                   => 'Multiple Choices',
      self::MOVED_PERMANENTLY               => 'Moved Permanently',
      self::FOUND                           => 'Found',
      self::SEE_OTHER                       => 'See Other',
      self::NOT_MODIFIED                    => 'Not Modified',
      self::TEMPORARY_REDIRECT              => 'Temporary Redirect',
      self::BAD_REQUEST                     => 'Bad Request',
      self::UNAUTHORIZED                    => 'Unauthorized',
      402                                   => 'Payment Required',
      self::FORBIDDEN                       => 'Forbidden',
      self::NOT_FOUND                       => 'Not Found',
      self::METHOD_NOT_ALLOWED              => 'Method Not Allowed',
      self::NOT_ACCEPTABLE                  => 'Not Acceptable',
      407                                   => 'Proxy Authentication Required',
      408                                   => 'Request Timeout',
      self::CONFLICT                        => 'Conflict',
      self::GONE                            => 'Gone',
      self::LENGTH_REQUIRED                 => 'Length Required',
      self::PRECONDITION_FAILED             => 'Precondition Failed',
      self::REQUEST_ENTITY_TOO_LARGE        => 'Request Entity Too Large',
      self::REQUEST_URI_TOO_LONG            => 'Request-URI Too Long',
      self::UNSUPPORTED_MEDIA_TYPE          => 'Unsupported Media Type',
      self::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
      self::EXPECTATION_FAILED              => 'Expectation Failed',
      self::IM_A_TEAPOT                     => 'I\'m a Teapot',
      self::INTERNAL_ERROR                  => 'Internal Server Error',
      self::NOT_IMPLEMENTED                 => 'Not Implemented',
      self::BAD_GATEWAY                     => 'Bad Gateway',
      self::SERVICE_UNAVAILABLE             => 'Service Unavailable',
      self::GATEWAY_TIMEOUT                 => 'Gateway Timeout',
      self::HTTP_VERSION_NOT_SUPPORTED      => 'HTTP Version Not Supported',
      506                                   => 'Variant Also Negotiates',
    );
    return isset($statuses[$code]) ? $statuses[$code] : 'Unknown Response';
  }

  protected function encodeArrayToXML($data, $parent) {
    $content = '';
    $parent_sing = Inflector::singularize($parent);
    $numeric_keys = ArrayUtil::isNumericallyKeyed($data);
    foreach ($data as $key => $value) {
      $key = $numeric_keys ? $parent_sing : $key;
      $content .= '<'.htmlspecialchars($key).'>';
      switch (true) {
        case is_array($value):
        case is_object($value):
          $content .= $this->encodeArrayToXML($value, $key);
          break;
        case is_bool($value):
          $content .= $value ? 'true' : 'false';
          break;
        case is_string($value):
        case is_numeric($value):
        default:
          $content .= htmlspecialchars($value);
          break;
      }
      $content .= '</'.htmlspecialchars($key).'>';
    }
    return $content;
  }

  protected function encodeToXML(array $data) {
    $content = '<'.'?xml version="1.0" encoding="utf-8" standalone="yes" ?'.">\n";
    if (count($data)!=1 || $this->force_envelope) {
      $content .= '<response>';
      $content .= $this->encodeArrayToXML($data, 'responses');
      $content .= '</response>';
    } else {
      $content .= $this->encodeArrayToXML($data, 'responses');
    }
    return $content;
  }

  protected function renderContentAsXML() {
    header('Content-Type: application/xml');
    echo $this->encodeToXML($this->content);
  }

  protected function renderContentAsJSON() {
    header('Content-Type: application/json');
    if (count($this->content)) {
      echo json_encode($this->content);
    } else {
      echo '{}';
    }
  }

  protected function renderStatusHeader() {
    header($_SERVER['SERVER_PROTOCOL'].' '.$this->code.' '.$this->statusFromCode($this->code), true, $this->code);
    if ($this->location !== null) {
      header('Location: '.$this->location, true, $this->code);
    }
  }

  protected function renderContent() {
    switch ($this->format) {
      case 'xml':
        $this->renderContentAsXML();
        break;
      case 'json':
      default:
        $this->renderContentAsJSON();
        break;
    }
  }

  public function render() {
    $this->renderStatusHeader();
    if (!in_array($this->code, array(self::CREATED, self::NO_CONTENT, self::RESET_CONTENT, self::MOVED_PERMANENTLY, self::FOUND, self::SEE_OTHER, self::NOT_MODIFIED, self::TEMPORARY_REDIRECT))) {
      $this->renderContent();
    }
  }
}

class RestResponseError extends RestResponse {
  public function __construct($code, $errors, RestRequest $request) {
    if ($code < 400 || $code > 599) {
      throw new InvalidArgumentException('Invalid status code '.$code.'; only error codes accepted');
    }
    if (is_array($errors)) {
      $content = array('errors' => $errors);
    } else {
      $content = array('errors' => array($errors));
    }
    parent::__construct($code, $content, $request);
  }
}

class RestResponseBadRequest extends RestResponseError {
  public function __construct($errors, RestRequest $request) {
    if (!is_array($errors)) $errors = array($errors);
    parent::__construct(self::BAD_REQUEST, $errors, $request);
  }
}

class RestResponseNotFound extends RestResponseError {
  public function __construct($errors, RestRequest $request) {
    if (!is_array($errors)) $errors = array($errors);
    parent::__construct(self::NOT_FOUND, $errors, $request);
  }
}

class RestResponseMethodNotAllowed extends RestResponseError {
  public function __construct($errors, RestRequest $request) {
    if (!is_array($errors)) $errors = func_get_args();
    parent::__construct(self::METHOD_NOT_ALLOWED, $errors, $request);
  }
}

class RestResponseNotAcceptable extends RestResponseError {
  public function __construct($errors, RestRequest $request) {
    if (!is_array($errors)) $errors = func_get_args();
    parent::__construct(self::NOT_ACCEPTABLE, $errors, $request);
  }
}

class RestResponseOk extends RestResponse {
  public function __construct($content, RestRequest $request) {
    parent::__construct(self::OK, $content, $request);
  }
}
