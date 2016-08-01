<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

class TestFocusedTestCase extends \TinyTest\TestCase {
  function ftest_passing(){
    assert(true, 'This test should never fail but run anyway');
  }

  function text_failure(){
    assert(false, 'This test should never run because of the focused test above');
  }
}

TestFocusedTestCase::run();
