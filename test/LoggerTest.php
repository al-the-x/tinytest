<?php

class LoggerTest extends \TinyTest\TestCase {
  public $loggerMethods = [
    'emergency',
    'alert',
    'critical',
    'error',
    'warning',
    'notice',
    'info',
    'debug',
    'log',
  ];

  function loggerMethods(){
    return array_map(function(){
      return function(){ };
    }, $this->loggerMethods);
  }
}

