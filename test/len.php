<?php

require_once __DIR__ . "/../vendor/amonite/async/index.php";
require_once __DIR__ . "/../index.php";

use Async\Promise;
use Async\File\File;
await(function () {
  $file = File::getInstance(__DIR__ . "/sample.ext");

  $prom = $file->append("Hello World!\n");
  if ($prom instanceof Promise) {
    $prom->then(function ($len) {
      echo "file is now $len length";
    });
  }
});
