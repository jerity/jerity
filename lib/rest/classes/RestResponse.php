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

  public function __construct($code, $content) {
    $this->code = $code;
    $this->content = $content;
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

  protected static function arrayIsNumeric(array $array) {
    foreach (array_keys($array) as $key) {
      if (!is_numeric($key)) {
        return false;
      }
    }
    return true;
  }

  protected function __initSingularRules() {
    $singularRules = array(
      '/(s)tatuses$/i' => '\1\2tatus',
      '/^(.*)(menu)s$/i' => '\1\2',
      '/(quiz)zes$/i' => '\\1',
      '/(matr)ices$/i' => '\1ix',
      '/(vert|ind)ices$/i' => '\1ex',
      '/^(ox)en/i' => '\1',
      '/(alias)(es)*$/i' => '\1',
      '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
      '/([ftw]ax)es/' => '\1',
      '/(cris|ax|test)es$/i' => '\1is',
      '/(shoe)s$/i' => '\1',
      '/(o)es$/i' => '\1',
      '/ouses$/' => 'ouse',
      '/uses$/' => 'us',
      '/([m|l])ice$/i' => '\1ouse',
      '/(x|ch|ss|sh)es$/i' => '\1',
      '/(m)ovies$/i' => '\1\2ovie',
      '/(s)eries$/i' => '\1\2eries',
      '/([^aeiouy]|qu)ies$/i' => '\1y',
      '/([lr])ves$/i' => '\1f',
      '/(tive)s$/i' => '\1',
      '/(hive)s$/i' => '\1',
      '/(drive)s$/i' => '\1',
      '/([^fo])ves$/i' => '\1fe',
      '/(^analy)ses$/i' => '\1sis',
      '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
      '/([ti])a$/i' => '\1um',
      '/(p)eople$/i' => '\1\2erson',
      '/(m)en$/i' => '\1an',
      '/(c)hildren$/i' => '\1\2hild',
      '/(n)ews$/i' => '\1\2ews',
      '/^(.*us)$/' => '\\1',
      '/s$/i' => '');

    $uninflected = array(
      '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss', 'Amoyese',
      'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
      'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
      'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
      'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
      'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
      'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
      'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
      'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
      'whiting', 'wildebeest', 'Yengeese'
    );

    $irregular = array(
      'atlases' => 'atlas',
      'beefs' => 'beef',
      'brothers' => 'brother',
      'children' => 'child',
      'corpuses' => 'corpus',
      'cows' => 'cow',
      'ganglions' => 'ganglion',
      'genies' => 'genie',
      'genera' => 'genus',
      'graffiti' => 'graffito',
      'hoofs' => 'hoof',
      'loaves' => 'loaf',
      'men' => 'man',
      'monies' => 'money',
      'mongooses' => 'mongoose',
      'moves' => 'move',
      'mythoi' => 'mythos',
      'numina' => 'numen',
      'occiputs' => 'occiput',
      'octopuses' => 'octopus',
      'opuses' => 'opus',
      'oxen' => 'ox',
      'penises' => 'penis',
      'people' => 'person',
      'sexes' => 'sex',
      'soliloquies' => 'soliloquy',
      'testes' => 'testis',
      'trilbys' => 'trilby',
      'turfs' => 'turf',
      'waves' => 'wave'
    );

    $this->singularRules = array('singularRules' => $singularRules, 'uninflected' => $uninflected, 'irregular' => $irregular);
    $this->singularized = array();
  }

  protected function singularise($word) {
    if (!isset($this->singularRules) || empty($this->singularRules)) {
      $this->__initSingularRules();
    }

    if (isset($this->singularized[$word])) {
      return $this->singularized[$word];
    }
    extract($this->singularRules);

    if (!isset($regexUninflected) || !isset($regexIrregular)) {
      $regexUninflected = '(?:'.join( '|', $uninflected).')';
      $regexIrregular = '(?:'.join( '|', array_keys($irregular)).')';
      $this->singularRules['regexUninflected'] = $regexUninflected;
      $this->singularRules['regexIrregular'] = $regexIrregular;
    }

    if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
      $this->singularized[$word] = $word;
      return $word;
    }

    if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
      $this->singularized[$word] = $regs[1] . substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
      return $this->singularized[$word];
    }

    foreach ($singularRules as $rule => $replacement) {
      if (preg_match($rule, $word)) {
        $this->singularized[$word] = preg_replace($rule, $replacement, $word);
        return $this->singularized[$word];
      }
    }
    $this->singularized[$word] = $word;
    return $word;
  }

  protected function encodeArrayToXML($data, $parent) {
    $content = '';
    $parent_sing = $this->singularise($parent);
    $numeric_keys = self::arrayIsNumeric($data);
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
    $content = '<'.'?xml version="1.0" standalone="yes" encoding="utf-8" ?'.">\n";
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
    if (version_compare(PHP_VERSION, 5.3, '>=')) {
      echo json_encode($this->content, count($this->content) ? 0 : JSON_FORCE_OBJECT);
    } else {
      echo json_encode($this->content);
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
  public function __construct($code, $errors) {
    if ($code < 400 || $code > 599) {
      throw new InvalidArgumentException('Invalid status code '.$code.'; only error codes accepted');
    }
    if (is_array($errors)) {
      $content = array('errors' => $errors);
    } else {
      $content = func_get_args();
      array_shift($content);
      $content = array('errors' => $content);
    }
    parent::__construct($code, $content);
  }
}

class RestResponseBadRequest extends RestResponseError {
  public function __construct($errors) {
    if (!is_array($errors)) $errors = func_get_args();
    parent::__construct(self::BAD_REQUEST, $errors);
  }
}

class RestResponseNotFound extends RestResponseError {
  public function __construct($errors) {
    if (!is_array($errors)) $errors = func_get_args();
    parent::__construct(self::NOT_FOUND, $errors);
  }
}

class RestResponseMethodNotAllowed extends RestResponseError {
  public function __construct($errors) {
    if (!is_array($errors)) $errors = func_get_args();
    parent::__construct(self::METHOD_NOT_ALLOWED, $errors);
  }
}

class RestResponseNotAcceptable extends RestResponseError {
  public function __construct($errors) {
    if (!is_array($errors)) $errors = func_get_args();
    parent::__construct(self::NOT_ACCEPTABLE, $errors);
  }
}

class RestResponseOk extends RestResponse {
  public function __construct($content) {
    parent::__construct(self::OK, $content);
  }
}
