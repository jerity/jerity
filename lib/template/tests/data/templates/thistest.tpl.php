<?php
if (is_object($this) && $this instanceof Template) { echo 'PASS'; return; }
var_dump($this);
