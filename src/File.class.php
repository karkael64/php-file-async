<?php

namespace \Async\File;
use \Async\Promise;

class File {

  private static $instances = array();
  private $filepath, $locked = false;
  const FILE_CHUNK = 0xffff;

  function __construct (string $filepath) {
    if (isset(self::$instances[$filepath])) throw new Error("A File instance already exists, please load it with File::getInstance");
    else {
      $this->$filepath = $filepath;
      self::$instances[$filepath] = $this;
    }
  }

  static function getInstance (string $filepath) {
    if (isset(self::$instances[$filepath])) {
      return self::$instances[$filepath];
    } else {
      return new self($filepath);
    }
  }

  function exists () {
    return \file_exists($this->filepath);
  }

  function getPath () {
    return $this->filepath;
  }

  function remove () {
    return \unlink($this->filepath);
  }

  function rename ($as) {
    if (\rename($this->filepath, $as)) {
      $this->filepath = $as;
      return true;
    } else {
      return false;
    }
  }


  /**
   * @method readChunks Read a file chunk by chunks, about to manage computer resources.
   * @param $each {Closure.<$chunk {string}>} function called at each chunk read.
   * @param $chunksize {int} define the chunk size (default=`65535`).
   * @return {Async\Promise}
   */

  function readChunks (\Closure $each, int $chunksize = self::FILE_CHUNK) {
    $handler = fopen($self->filepath, "r");
    return new Promise(function (\Closure $resolve) use ($each, $chunksize, $handler) {
      async(function () use ($resolve, $each, $chunksize, $handler) {
        if (\foef($handler)) {
          return ($len = \ftell($handler) ? $len : true);
        }
        $chunk = fread($handler, $chunksize);
        $each($chunk);
      }, function ($err, $len) use ($handler, $resolve, $reject) {
        \fclose($handler);
        if (\is_null($err)) $resolve(\$len);
        else $reject($err);
      });
    });
  }


  /**
   * @method readLines Read a file line by lines, about to manage computer resources.
   * @param $each {Closure.<$chunk {string}>} function called at each chunk read.
   * @return {Async\Promise}
   */

  function readLines (\Closure $each) {
    return new Promise(function (\Closure $resolve, \Closure $reject) use ($each) {
      $raw = "";
      $count = 0;
      $this->readChunks(function (string $chunk) use (&$raw, &$count, $each) {
        $raw .= $chunk;
        $lines = \explode("\n", $raw);
        $raw = \array_pop($lines);
        foreach ($lines as $line) {
          $count++;
          $each($line);
        }
      })->then(function () use ($raw, $count, $each, $resolve) {
        $count++;
        $each($raw);
        $resolve($count);
      })->catch($reject);
    });
  }


  /**
   * @method append Add text at the end of a file.
   * @param $text {string} text to add.
   * @param $chunksize {int} manage computer resources.
   * @return {Async\Promise}
   */

  function append (string $text, int $chunksize = self::FILE_CHUNK) {
    $handler = \fopen($self->filepath, "a");
    return new Promise(function (\Closure $resolve) use (&$text, $chunksize, $handler) {
      async(function () use (&$text, $chunksize, $handler) {
        $chunk = \substr($text, 0, $chunksize);
        $text = \substr($text, $chunksize+1);
        if (!\strlen($chunk)) {
          return ($len = \ftell($handler) ? $len : true);
        }
        \fwrite($handler, $chunk);
      }, function ($err, $len) use ($handler, $resolve, $reject) {
        \fclose($handler);
        if (\is_null($err)) $resolve(\$len);
        else $reject($err);
      });
    });
  }
}
