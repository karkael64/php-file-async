# File Async (PHP)

## Asynchronous file manager for PHP!

With package [amonite/async](https://packagist.org/packages/amonite/async) and files [karkael64/php-async](https://github.com/karkael64/php-async), it's now possible to asynchronously use files in your filesystem. Now you can let your script continue without waiting for read or write a file, and chunk your data to improve performances.

## Installation

Execute `composer require file-async` or copy `async-file.phar` in your project folder.

## Context

The filesystem can be a loss of time, where a server with many waiting users can be stuck in a queue. This library could help write/read files and return a Promise object when it resolves a result or reject an error during the filesystem process.

## Non-blocking events

In PHP, by default many functions stop script until the end. That's helpful with common arithmetic functions, but you wan also observe it when you load a file from your computer and block the script (and stuck next users in a queue).

The functions to handle a resource of a filesystem (`fopen`, `fclose`, `fwrite`, `fread`, `fclose`, `unlink`) are all blocking your script. It means handling a big file (>10GiB) could block your script for a minute while many other commands could be executed.

# Usage

## Load a single instance

In order to prevent multiple handling on a single file, please use a single instance of this File. By the way, be carefull of [mutual exclusions](https://en.wikipedia.org/wiki/Mutual_exclusion), unlock a file before to lock another file. It's recommanded to use the syntax `getInstance` as following :

``` php
<?php
use Async\File\File;
$file = File::getInstance(__DIR__ . "/path/to/file.ext");
```

## Request promised

For every requests you make, there is a Promise that wait to resolve. It means that you got response asynchronously in `then` functions or error in `catch` functions. Remember, you whould be in an `await` context.

``` php
<?php

use Async\Promise;
use Async\File\File;
$file = File::getInstance(__DIR__ . "/path/to/file.ext");

$prom = $file->append("Hello World!\n");
if ($prom instanceof Promise) {
  $prom->then(function ($len) {
    echo "file is now $len length";
  });
}
```

## Chain requests

In your script, you may send many requests. If you want to be sure your data is correctly written, read, changed file name, data append etc., it is recommanded to chain requests when previous resolves. Like this, you are sure files are unlock at time, and your request starts after the others.

``` php
<?php

$file->append("Hello World!\n")->then(function () use ($file) {
  $file->append("How are you?\n")->then(function () use ($file) {
    $num = 0;
    $file->readLines(function ($line) use (&$num) {
      echo "#$num:\t$line";
      $num++;
    })->then(function () use ($file) {
      $file->remove()->then(function ($done) {
        if ($done) {
          echo "file removed\n";
        } else {
          echo "error!";
        }
      });
    });
  });
});
```

When order does not matter (here between the 3 `append`), you can chain requests without waiting previous requests to resolve. Here, when you `readLines`, you don't know if file exists yet, if there is one, two or three lines, and in which order you get it.

``` php
<?php

$file->append("Hello World!\n");
$file->append("How are you?\n");
$file->append("How old are you?\n");
$num = 0;
$file->readLines(function ($line) use (&$num) {
  echo "#$num:\t$line";
  $num++;
});  
```

## File lock

You can check if the file is currently handled with `$file->isLocked()` or wait for unlock event with :

``` php
<?php

echo $file->isLocked() ? "true" : "false"; // true or false, depends previous usage
$file->append("Hello World!\n");
echo $file->isLocked() ? "true" : "false"; //true

$file->unlock()->then(function () {
  echo $file->isLocked() ? "true" : "false"; // false
});
```

# Documentation

## Class `Async\File\File`

### Method `__construct`

### Static `getInstance`

### Method `getPath`

### Method `isLocked`

### Method `unlock`

### Method `exists`

### Method `rename`

### Method `remove`

### Method `readChunks`

### Method `readLines`

### Method `append`
