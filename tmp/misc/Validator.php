<?php

  class Validator {

    public static ALPHA       = '/[a-z]+/i';
    public static ALPHA_LOWER = '/[a-z]+/';
    public static ALPHA_UPPER = '/[a-z]+/';
    public static BINARY      = '/^[01]+$/';
    public static OCTAL       = '/^0?[0-8]+$/';
    public static DECIMAL     = '/^[0-9]+$/';
    public static HEXADECIMAL = '/^(0x)?[0-9a-f]+h?$/i';
    public static NOTHING     = '/^$/';

    public static check($rules) {
      foreach ($_REQUEST as $k => $v) {
        if (!array_key_exists($k, $rules) {
          unset($_REQUEST[$k]);
          continue;
        }
        if (!preg_match($v/*.'D'*/, $_REQUEST[$k]) {
          unset($_REQUEST[$k]);
          continue;
        }
        // TODO: Array values...
      }
    }

  }

?>
