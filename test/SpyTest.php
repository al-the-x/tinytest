<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once 'LoggerTest.php';

class SpyTest extends LoggerTest {
  function setUp(){
    $this->logger = new \TinyTest\Spy($this->loggerMethods());
  }

  function test_methods(){
    foreach ( $this->loggerMethods as $methodName ){
      assert('is_callable([ $this->logger, $methodName ])',
        "$methodName should be callable...");
      assert('$this->logger->{$methodName}() === null',
        "\$logger::{$methodName} should return NULL");
      assert('$this->logger->got($methodName) === 1',
        "\$logger->got({$methodName}) should return 1");
      // TODO: Check that $methodName was called with certain arguments...
    }
  }
}
SpyTest::run();

