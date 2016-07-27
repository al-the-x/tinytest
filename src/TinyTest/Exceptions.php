<?php

trait FailureHandler {
  public function __construct(array $facts){
    $facts += [
      'file' => null,
      'line' => null,
    ];

    parent::__construct("Assertion Error in {$facts['file']}:{$facts['line']}");
  }
}

trait VerboseException {
  public function toString($verbose = false){
    if ( $verbose ) return parent::__toString();

    return $this->getMessage();
  }

  public function __toString(){
    return $this->toString(false);
  }
}

// PHP7 provides `\AssertionError` thrown by `assert`...
if ( class_exists('\AssertionError') ){
  class AssertionError extends \AssertionError {
    use FailureHandler;
  }

  class TestSkipped extends \AssertionError {
    use VerboseException;
  }
} else {
  class AssertionError extends \RuntimeException {
    use FailureHandler;
  }

  class TestSkipped extends \RuntimeException {
    use VerboseException;
  }
}
