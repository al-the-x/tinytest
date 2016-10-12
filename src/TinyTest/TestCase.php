<?php

namespace TinyTest;

require_once __DIR__ . '/Exceptions.php';

class TestCase {
  final private function __construct(){
    $self = get_called_class();

    assert_options(ASSERT_ACTIVE, true);
    assert_options(ASSERT_WARNING, false);
    assert_options(ASSERT_CALLBACK, [ $self, 'failure' ]);
  }

  /**
   * Override to provide pre-test logic; run before every test.
   */
  public function setUp(){ }

  /**
   * Override to provide post-test logic; run after every test.
   */
  public function tearDown(){ }

  /**
   * Check missing method for `xtest_` or `ftest_` prefix and dispatch the
   * matching method instead. If you have dependent tests, you can mark the
   * parent test as skipped by prefixing it with `x` and not worry about any
   * dependent tests breaking.
   *
   * @example
   *  class SomeTest extends \TinyTest\TestCase {
   *    // Test skipped by renaming...
   *    function xtest_something(){
   *      assert(true);
   *    }
   *
   *    // This test will be skipped as well...
   *    function test_somethingElse(){
   *      // No need to update all references to skipped test...
   *      $this->test_something();
   *
   *      assert(false);
   *    }
   *  }
   *
   *  try {
   *    SomeTest::run('test_something');
   *  } catch (\TinyTest\TestSkipped $failure) {
   *  } finally {
   *    assert(isset($failure) && $failure instanceof \TinyTest\TestSkipped);
   *  }
   *
   *  try {
   *    SomeTest::run('test_somethingElse');
   *  } catch (\TinyTest\TestSkipped $failure) {
   *  } finally {
   *    assert(isset($failure) && $failure instanceof \TinyTest\TestSkipped);
   *  }
   *
   * @param string $method
   * @param array $arguments
   * @throws \TinyTest\TestSkipped if $method is a skipped test
   * @throws \TinyTest\AssertionError if $method is a failed test
   * @throws \RunTimeException if $method is not a method at all
   */
  final public function __call($method, $arguments){
    $self = get_class();

    if ( strpos($method, 'test_') ){
      if ( method_exists($self, "x{$method}") ) throw new TestSkipped($method);

      if ( method_exists($self, "f{$method}") ){
        // This could still throw \TinyTest\AssertionError or \TinyTest\TestSkipped...
        return call_user_func_array([ $self, "f{$method}" ], $arguments);
      }
    }

    throw new \RunTimeException("No method {$method} on {$self}");
  }

  /**
   * Skip the current test; best placed at the TOP of the test method.
   *
   * @param string [$reason] for skipping
   * @throws \TinyTest\TestSkipped
   */
  final protected function skip($reason = null){
    throw new TestSkipped($reason);
  }

  /**
   * Break your entire test suite! Use `assert(false)` instead of `fail()`
   *
   * @param string [$reason]
   * @throws \RuntimeException
   */
  final protected function fail($reason = null){
    throw new \RuntimeException('Use `assert(false, $message)` instead of `fail($message)`');
  }

  /**
   * Handler for `assert` failure, registered as `ASSERT_CALLBACK`
   *
   * @param string $file that `assert` failed in
   * @param integer $line in {$file} that `assert` failed on
   * @param string $code passed to and `eval`-ed by `assert` (maybe empty)
   * @param string [$message] provided to `assert`
   * @throws \TinyTest\AssertionError unconditionally
   */
  static function failure($file, $line, $code, $message = null){
    throw new AssertionError(compact('file', 'line', 'code', 'message'));
  }

  /**
   * Provide a list of the tests that _could_ be run for this TestCase, i.e.
   * any class methods that match `/[xf]?test_/`. The `run` method determines
   * whether or not those tests are executed.
   *
   * @see \TinyTest\TestCase::run
   * @return array of test methods, i.e. methods that match `^/[xf]?test_/`
   */
  static function tests(){
    return preg_grep('/^[xf]?test_/', get_class_methods(get_called_class()));
  }

  /**
   * Run a specific test returning the failure state:
   * - {null} for passed tests
   * - {\TinyTest\AssertionError} for failed tests
   * - {\TinyTest\TestSkipped} for skipped tests
   *
   * @param string $test to run
   * @return \TinyTest\AssertionError|array[\TinyTest\AssertionError]
   */
  static function runOne($test){
    $self = get_called_class();

    // Run the provided $test...
    try {
      $testcase = new $self;
      $testcase->setUp();
      $testcase->$test();
    } catch (AssertionError $failure){
      return $failure;
    } catch (TestSkipped $skipped){
      return $skipped;
    } finally {
      $testcase->tearDown();
    }
  }

  /**
   * 2-tuple of {string} test name and test result
   *
   * @name ResultList
   * @typedef array[ array[ string, null|Exception ] ] ResultList
   */

  /**
   * Run all the tests for this TestCase, collecting the responses into a map
   * of test names (`class::method`) associated with the response.
   *
   * @return ResultList
   */
  static function runAll(){
    $self = get_called_class();

    $all = $self::tests();

    $focused = preg_grep('/^ftest_/', $all);

    return array_reduce($focused ?: $all, function($results, $test) use ($self){
      // Collect 2-tupled containing test name and result...
      $results[] = [ "{$self}::{$test}", ( strpos($test, 'xtest_') === 0 ?
        new TestSkipped("Test {$test} skipped by convention") : $self::runOne($test)
      ) ];

      return $results;
    }, [ ]);
  }

  /**
   * Invoke this function at the bottom of your test case to run all the tests
   * defined in that file when executed directly.
   *
   * @example
   *  // in `SomeTest.php`:
   *  class SomeTest extends \TinyTest\TestCase {
   *    function test_should_fail(){
   *      assert(false, 'This test should fail to prove it ran');
   *    }
   *  }
   *
   *  SomeTest::run();
   *
   *  // in CLI:
   *  $> php SomeTest.php
   *  1..1
   *  not ok 1
   *
   * @param string|\TinyTest\Runner $runner to pass results to
   * @return null
   */
  static function run(){
    $self = get_called_class();

    $here = realpath((new \ReflectionClass($self))->getFileName());

    if ( $here === realpath($_SERVER['PHP_SELF']) )
      Runner::instance()->results($self::runAll());
  }
} // END TestCase
