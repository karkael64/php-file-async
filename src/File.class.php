<?php

namespace Async\File;
use \Async\Promise;

class File {

  private static $instances = array();
  protected $filepath, $locked = false;
  const FILE_CHUNK = 0xffff;

  function __construct (string $filepath) {
    if (isset(self::$instances[$filepath])) throw new Error("A File instance already exists, please load it with File::getInstance");
    else {
      $this->filepath = $filepath;
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


  /**
   * @method getPath Return filepath string.
   * @return {string}
   */

  function getPath () {
    return $this->filepath;
  }


  /**
   * @method isLocked Tell if a request is currently running
   * @return {boolean} `true` os a request is running
   */

  function isLocked () {
    return $this->locked;
  }


  /**
   * @method unlock Await the file is unlocked before triggering a resolve.
   * @return {Async\Promise.<>.<$err {Throwable}>}
   */

  function unlock () {
    $self = $this;
    return new Promise(function (\Closure $resolve, \Closure $reject) use ($self) {
      async(function () use ($self) {
        return !$self->isLocked();
      }, function ($err) use ($resolve, $reject) {
        if ($err) $reject($err);
        else $resolve();
      });
    });
  }


  /**
   * @method exists Tell if file exists in this filesystem.
   * @return {Async\Promise.<$bo {boolean}>.<$err {Throwable}>}
   */

  function exists () {
    $self = $this;
    $filepath = $this->filepath;
    return new Promise(function (\Closure $resolve, \Closure $reject) use ($self, $filepath) {
      $self->unlock()->then(function () use ($resolve, $filepath) {
        \file_exists($filepath) ? $resolve(true) : $resolve(false);
      })->catch($reject);
    });
  }


  /**
   * @method remove Unlink the file from this filesystem. Even if it is
   *    promised, please beware to use this function in unlocked callback.
   * @return {Async\Promise.<$bo {boolean}>.<$err {Throwable}>}
   */

  function remove () {
    $self = $this;
    $filepath = $this->filepath;
    return new Promise(function (\Closure $resolve, \Closure $reject) use ($self, $filepath) {
      $self->unlock()->then(function () use ($resolve, $filepath) {
        \unlink($filepath) ? $resolve(true) : $resolve(false);
      })->catch($reject);
    });
  }


  /**
   * @method rename Change the file path in this filesystem. Even if it is
   *    promised, please beware to use this function in unlocked callback.
   * @return {Async\Promise.<$bo {boolean}>.<$err {Throwable}>}
   */

  function rename ($as) {
    $self = $this;
    $filepath = $this->filepath;
    return new Promise(function (\Closure $resolve, \Closure $reject) use ($self, $filepath) {
      $self->unlock()->then((function () use ($resolve, $filepath) {
        if (\rename($this->filepath, $as)) {
          $this->filepath = $as;
          $resolve(true);
        } else {
          $resolve(false);
        }
      })->bindTo($self))->catch($reject);
    });
  }


  /**
   * @method readChunks Read a file chunk by chunks, about to manage computer resources.
   * @param $each {Closure.<$chunk {string}>} function called at each chunk read.
   * @param $chunksize {int} define the chunk size (default=`65535`).
   * @return {Async\Promise.<$len {int}>.<$err {Throwable}>}
   */

  function readChunks (\Closure $each, int $chunksize = self::FILE_CHUNK) {
    $self = $this;
    return new Promise(function (\Closure $resolve, \Closure $reject) use ($each, $chunksize, $self) {
      $shouldUnlock = !$self->isLocked();
      $self->unlock()->then((function () use ($resolve, $reject, $each, $chunksize, $shouldUnlock) {
        if ($shouldUnlock) $this->locked = true;
        try {
          $handler = \fopen($this->filepath, "r");
          if ($handler === false) throw new \Error("Can't read file ".json_encode($this->filepath));
        } catch (\Throwable $err) {
          return $reject($err);
        }
        async(function () use ($each, $chunksize, $handler) {
          if (\feof($handler)) {
            return ($len = \ftell($handler)) ? $len : true;
          }
          $chunk = \fread($handler, $chunksize);
          $each($chunk);
        }, (function ($err, $len) use ($handler, $resolve, $reject, $shouldUnlock) {
          \fclose($handler);
          if ($shouldUnlock) $this->locked = false;
          if (\is_null($err)) $resolve($len);
          else $reject($err);
        })->bindTo($this));
      })->bindTo($self))->catch($reject);
    });
  }


  /**
   * @method readLines Read a file line by lines, about to manage computer resources.
   * @param $each {Closure.<$chunk {string}>} function called at each chunk read.
   * @return {Async\Promise.<$countLines {int}>.<$err {Throwable}>}
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
   * @return {Async\Promise.<$len (int)>.<$err {Throwable}>}
   */

  function append (string $text, int $chunksize = self::FILE_CHUNK) {
    $self = $this;
    return new Promise(function (\Closure $resolve, \Closure $reject) use (&$text, $chunksize, $self) {
      $shouldUnlock = !$self->isLocked();
      $self->unlock()->then((function () use ($resolve, $reject, &$text, $chunksize, $shouldUnlock) {
        if ($shouldUnlock) $this->locked = true;
        try {
          $handler = fopen($this->filepath, "a");
          if ($handler === false) throw new Error("Can't append file ".json_encode($this->filepath));
        } catch (\Throwable $err) {
          return $reject($err);
        }
        async(function () use (&$text, $chunksize, $handler) {
          $chunk = \substr($text, 0, $chunksize);
          $text = \substr($text, $chunksize+1);
          if (!\strlen($chunk)) {
            return ($len = \ftell($handler)) ? $len : true;
          }
          \fwrite($handler, $chunk);
        }, (function ($err, $len) use ($handler, $resolve, $reject, $shouldUnlock) {
          \fclose($handler);
          if ($shouldUnlock) $this->locked = false;
          if (\is_null($err)) $resolve($len);
          else $reject($err);
        })->bindTo($this));
      })->bindTo($self))->catch($reject);
    });
  }
}
