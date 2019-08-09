<?php

namespace TinyTest;

class Runner {
  protected $_failed = 0;

  static function header($nResults){
    return ['TAP version 13', "1..{$nResults}"];
  }

  function result($run, $index){
    list($name, $result) = $run; $index += 1;

    if ( !$result ) return "ok {$index} {$name}";

    if ( $result instanceof TestSkipped )
      return "ok {$index} {$name} # SKIP {$result}";

    $this->_failed++;

    return join("\n", [
      "not ok {$index} {$name}",
      "\t" . join("\n\t", [
        "---",
        "message: {$result}",
        "...",
      ])
    ]);
  }

  public function results(array $results){
    echo join("\n", array_merge(
      $this->header(count($results)),
      array_map([ $this, 'result' ], $results, array_keys($results))
    )), "\n";

    exit($this->_failed);
  }

  static function instance($runner = null){
    $runner = $runner ?: get_called_class();

    if ( is_object($runner) ) return $runner;

    if ( is_string($runner) ) return new $runner;
  }
}
