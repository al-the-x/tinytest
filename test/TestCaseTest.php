<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

class TestCaseTest extends \TinyTest\TestCase {
  function test_nothing(){
    // assert nothing...
  }

  function test_pass(){
    assert(true, 'This test should pass');
  }

  function test_fail(){
    assert(false, 'This test should fail');
  }

  function test_skip(){
    $this->skip('This test should be skipped');

    assert(false, 'And thus never fail...');
  }

  function xtest_skip_by_convention(){
    assert(false, 'This should be skipped by convention');
  }
}

TestCaseTest::run();

