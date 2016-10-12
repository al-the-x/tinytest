<?php

namespace TinyTest;

// `noop` intentionally does nothing
function noop(){ }

// `identity` returns whatever is passed as the first argument
function identity($arg){
  return $arg;
}

/**
 * `Dummy` represents a category of stand-in objects that can be dynamically
 * configured with data and methods, which might be trivial or return canned
 * data. A `Stub` is a configured `Dummy`; a `Spy` is a `Dummy` that "remembers"
 * the methods that were called.
 */
abstract class Dummy {
  protected $_members = [ ];

  function __construct($members){
    $this->_members = $members;
  }

  function __get($name){
    return @$this->_members[$name];
  }

  function __set($name, $value){
    $this->_members[$name] = $value;
  }

  function __call($method, $arguments){
    if ( is_callable($this->$method) )
      // FIXME: Use `$this->$method($arguments[0])` etc for speed...
      return call_user_func_array($this->$method, $arguments);
  }
}

/**
 * A `Stub` is a `Dummy` configured with data and method calls. Basically a
 * concrete implementation of `Dummy`
 */
class Stub extends Dummy { }

/**
 * A `Spy` is a `Dummy` that "remembers" the methods that were called on it
 * and may respond with canned data or a call through to a real fixture.
 *
 * @method {integer} got -- return the number of times a method was called
 * @method {mixed} __call -- record method call and call-through to dummy method
 */
class Spy extends Dummy {
  protected $_calls = [ ];

  /**
   * Proxy to {Dummy::__call} while recording the {$method} call...
   *
   * @param {string} $method name to call
   * @param {array} $arguments to pass to {$method}
   * @return {mixed} return value from {$method} with {$arguments}
   */
  function __call($method, $arguments){
    $this->_calls[$method] = @$this->_calls[$method] ?: [ ];
    $this->_calls[$method][] = $arguments;

    return parent::__call($method, $arguments);
  }

  /**
   * Return number of calls recorded for {$method} so far
   *
   * @param {String} $method
   * @return {integer}
   */
  function got($method){
    return count(@$this->_calls[$method]);
  }
}

// TODO: class Mock { }

