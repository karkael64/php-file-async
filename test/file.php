<?php

require_once __DIR__ . "/../vendor/amonite/async/index.php";
require_once __DIR__ . "/../index.php";

echo microtime(true) . "\n";
echo microtime(true) . "\n";
await(function () {
  $t = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. In nec molestie felis. Aliquam posuere est id semper tincidunt. Etiam efficitur elit non neque dictum rutrum. Morbi et lorem at sapien sollicitudin vestibulum sit amet id leo. Quisque ornare vehicula nunc, in finibus mi gravida vitae. Integer sed metus volutpat, sodales metus id, volutpat tellus. Cras placerat lacinia ligula, ut hendrerit ante pharetra sit amet. Mauris laoreet mattis ante. Sed accumsan nisl vestibulum, porttitor nisi nec, gravida felis. Ut eu tellus eros. Phasellus tempus ante nec odio dignissim, sit amet commodo felis posuere. Proin dui ligula, mollis ut elit a, rutrum faucibus neque. In eleifend mauris at lectus varius, sed fringilla ipsum elementum. Vestibulum a mi dignissim, eleifend mauris quis, molestie ante. Quisque pharetra risus nunc, a consequat leo scelerisque imperdiet. Sed quis dapibus arcu.

Praesent semper justo non dui dignissim vulputate. Sed tortor eros, elementum eu congue ut, pharetra quis leo. Praesent massa neque, bibendum ac gravida eget, condimentum et orci. Quisque quis tincidunt massa. Quisque sit amet ex est. Proin a dui auctor, congue justo consequat, lacinia urna. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.

Morbi in consectetur nibh, vitae rutrum purus. Sed vel ornare leo. Vivamus eget turpis eget lectus pharetra imperdiet sit amet et erat. Donec vitae faucibus felis. Sed turpis mauris, egestas vel pretium fringilla, dignissim ut quam. Curabitur facilisis, purus id vestibulum tempus, lacus justo euismod felis, sed blandit lorem diam sollicitudin mi. Sed imperdiet tellus id dolor feugiat tempus. In dictum augue ipsum, vitae vestibulum odio tincidunt nec. Donec facilisis tincidunt rutrum.

Suspendisse mattis eget tellus a euismod. Donec imperdiet mi eu nisl maximus mollis. Etiam interdum elit dui, non ultricies lacus pulvinar et. Praesent vitae quam varius, venenatis augue at, rhoncus velit. Proin vel ullamcorper nibh. Cras lacinia ultrices dolor et facilisis. Morbi mollis nulla in diam pharetra rhoncus. Ut tristique condimentum gravida.

Aenean fringilla, neque ut congue maximus, arcu nisl viverra orci, in vehicula libero ante ornare nulla. Integer nisi leo, consequat vel metus vestibulum, pharetra pharetra neque. Pellentesque aliquet vestibulum tortor sit amet pulvinar. Nunc vulputate tellus felis. Nullam varius felis ex, sed vulputate massa tristique nec. In vitae neque quis neque egestas dictum ultricies et sem. Ut id mattis elit, et convallis turpis. Etiam sit amet tellus ut libero facilisis facilisis eget congue quam. Suspendisse a sodales erat, non lacinia ligula. Nulla non neque at purus malesuada eleifend nec a diam.
";

  $f = Async\File\File::getInstance(__DIR__ . "/tmp.any");
  echo "file exists: " . json_encode($f->exists()) . "\n";
  $p1 = $f->append("Hello, World!\n");
  $p2 = $f->append($t);
  $p3 = $f->append("Welcome home!\n");
  Async\Promise::all(array($p1, $p2, $p3))->then(function () use ($f) {
    echo "file exists: " . json_encode($f->exists()) . " (" . json_encode($f->getPath()) . ")\n";
    $l = 0;
    $f->readLines(function ($line) use (&$l) {
      $l++;
      echo "#$l:\t$line\n";
    })->then(function () use ($f) {
      $f->remove();
      echo "file exists: " . json_encode($f->exists()) . "\n";
    });
  });
});
echo microtime(true) . "\n";
