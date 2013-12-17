<?php

// lambda calculus interpreter
//
// too bad PHP has no tail recursion, meaning it will run out of stack space
// and/or memory rather quickly.
//
// loosely based on Matt Might's:
//
//   7 lines of code, 3 minutes: Implement a programming language from scratch
//
// http://matt.might.net/articles/implementing-a-programming-language/
//
// if you like this, you'll probably also like Tom Stuart's:
//
//   Programming with Nothing
//
// http://codon.com/programming-with-nothing

namespace igorw\lambda;

function first(array $list)
{
    return $list[0];
}

function second(array $list)
{
    return $list[1];
}

function evaluate($exp, array $env = [])
{
    // actual PHP numbers and callables
    // needed for hooking into the engine
    // inside of a lambda calculus program you would
    // just use church numerals
    //
    // note: only object callables are supported, which
    // includes closures
    if (is_int($exp) || is_float($exp) || is_bool($exp) || is_object($exp) && is_callable($exp)) {
        return $exp;
    }

    // exp is a symbol, lookup in env
    if (is_string($exp)) {
        return $env[$exp];
    }

    // procedure 'object'
    // encoded as a 4-tuple of [λ, arg, body, env]
    // this is what is passed to apply
    if ('λ' === first($exp)) {
        list($_, $arg, $body) = $exp;
        return ['λ', $arg, $body, $env];
    }

    // function application
    // evaluate sub-expressions, then apply
    $f = evaluate(first($exp), $env);
    $arg = evaluate(second($exp), $env);
    return apply($f, $arg);
}

function apply($f, $x)
{
    // f can be a PHP callable, but this is
    // only used for engine calls
    if (is_callable($f)) {
        return $f($x);
    }

    // evaluate the body of the function
    // by substituting the argument via
    // the environment
    //
    // this is also known as beta reduction
    list($_, $arg, $body, $env) = $f;
    return evaluate($body, array_merge($env, [$arg => $x]));
}

function call(/* $f, $args... */)
{
    $args = func_get_args();
    $f = array_shift($args);

    $call = $f;
    foreach ($args as $arg) {
        $call = [$call, $arg];
    }
    return $call;
}

function lazy($exp, $x = 'x')
{
    return ['λ', $x, [$exp, $x]];
}

function to_int($exp)
{
    $inc = function ($n) {
        return $n + 1;
    };

    return [[$exp, $inc], 0];
}

function to_bool($exp)
{
    return [[$exp, true], false];
}
