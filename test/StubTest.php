<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once 'LoggerTest.php';

class StubTest extends LoggerTest {
  function setUp(){
    $this->logger = new \TinyTest\Stub($this->loggerMethods());
  }

  function test_methods(){
    foreach ( $this->loggerMethods as $methodName ){
      assert('is_callable($this->logger->{$methodName})');
      assert('$this->logger->{$methodName}() === null');
    }
  }
}
StubTest::run();

