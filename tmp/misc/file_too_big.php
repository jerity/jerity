<?php

public static function checkForLargeFileUpload() {
  static $result = null;
  if (!is_null($result)) return $result;
  if (empty($_SERVER['CONTENT_LENGTH'])) return false;
  # Check if the user is trying to upload a file that is too large.
  # ($_POST & $_FILES are both empty in this case)
  $result = false;
  # Check if file is larger than [ini:post_max_size]
  $mul = substr(ini_get('post_max_size'), -1);
  $mul = ($mul == 'K' ? 1024 : ($mul == 'M' ? 1048576 : ($mul == 'G' ? 1073741824 : 1)));
  if ($_SERVER['CONTENT_LENGTH'] > $mul * (int) ini_get('post_max_size')) {
    if (!empty($_FILES)) {
      // sanity check: allegedly too big, but we have files array??
      // future check: can we have a half-populated $_FILES but then error..?
      // Assumption: this shouldn't be possible, so we shouldn't see this error.
      trigger_error('Shouldnt be here. $_FILES[] is not empty but content length was larger than post_max_size?' . implode(',', array(count($_FILES), $_SERVER['CONTENT_LENGTH'], ini_get('post_max_size'))));
      // assume all ok for now, need to check if this ever happens..
    } else {
      $result = true;
    }
  } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) {
    # Should have $_POST but it is empty -- file was too large.
    $result = true;
  }
  // XXX: if ($result) StatisticsPeer::record(Statistics::FILE_UPLOAD_ERROR_SIZE);
  return $result;
}
