<?php
if (!isset($count)) $count=count($this->getContent());
for ($i=0; $i<$count; $i++) {
  $c = $this->getNextContent();
  if (!isset($compact) || $compact) {
    print $c.'|';
  } else {
    if (is_null($c)) {
      printf("%s Content %2d %s\n", str_repeat('#', 5), $i, str_repeat('#', 5));
    } else {
      printf("%s Content %2d %s\n", str_repeat('=', 5), $i, str_repeat('=', 5));
      echo $c;
    }
    echo "\n".str_repeat(22, '=')."\n";
  }
}
