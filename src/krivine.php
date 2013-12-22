<?php

namespace igorw\lambda\krivine;

// (M N, S, E) → (M, (S,(N,E)), E)
// (λM, (S,N), E) → (M, S, (E,N))
// (i+1, S, (E,N)) → (i, S, E)
// (0, S, (E1,(M,E2))) → (M, S, E2)

function first(array $list)
{
    return isset($list[0]) ? $list[0] : null;
}

function second(array $list)
{
    return isset($list[1]) ? $list[1] : null;
}

function rest(array $list)
{
    array_shift($list);
    return $list;
}

function inc($value)
{
    return $value + 1;
}

function inc_indices($indices)
{
    return array_map('igorw\lambda\krivine\inc', $indices);
}

function assoc(array $map, $key, $value)
{
    $map[$key] = $value;
    return $map;
}

function dissoc(array $map, $key)
{
    unset($map[$key]);
    return $map;
}

function de_bruijn($exp, $indices = [])
{
    if (is_int($exp) || is_float($exp) || is_bool($exp) || is_object($exp) && is_callable($exp)) {
        return $exp;
    }

    if (is_string($exp)) {
        return ['offset', $indices[$exp]];
    }

    if ('λ' === first($exp)) {
        list($_, $arg, $body) = $exp;

        $indices = inc_indices($indices);
        $indices = assoc($indices, $arg, 1);

        return ['λ', de_bruijn($body, $indices)];
    }

    $f = de_bruijn(first($exp), $indices);
    $arg = de_bruijn(second($exp), $indices);
    return [$f, $arg];
}

function compile($exp)
{
    if (is_int($exp) || is_float($exp) || is_bool($exp)) {
        return [['constant', $exp]];
    }

    if (is_object($exp) && is_callable($exp)) {
        return [['call', $exp]];
    }

    if ('λ' === first($exp)) {
        list($_, $body) = $exp;
        return array_merge([['grab']], compile($body));
    }

    if ('offset' === first($exp)) {
        list($_, $offset) = $exp;
        return [['access', $offset]];
    }

    $f = first($exp);
    $arg = second($exp);

    return array_merge([['push', compile($arg)]], compile($f));
}

class Machine
{
    public $code, $env, $stack;

    function __construct($code, $env, $stack)
    {
        $this->code = $code;
        $this->env = $env;
        $this->stack = $stack;
    }

    function execute()
    {
        $machine = $this;

        while ($machine->code) {
            $machine = $machine->step();
        }

        return first($machine->stack);
    }

    function step()
    {
        // echo 'code: '.json_encode($this->code)."\n";
        // echo 'env: '.json_encode($this->env)."\n";
        // echo 'stack: '.json_encode($this->stack)."\n";
        // echo "---\n";

        $inst = first(first($this->code));
        $inst_arg = rest(first($this->code));

        $fn = [$this, $inst];

        if (!is_callable($fn)) {
            throw new \RuntimeException("Invalid instruction '$inst'");
        }

        return $fn($inst_arg, rest($this->code), $this->env, $this->stack);
    }

    function access($args, $code, $env, $stack)
    {
        $i = first($args) - 1;
        list($c_code, $c_env) = $env[$i];

        return new Machine($c_code, $c_env, $stack);
    }

    function push($args, $code, $env, $stack)
    {
        $closure_code = first($args);
        array_unshift($stack, [$closure_code, $env]);

        return new Machine($code, $env, $stack);
    }

    function grab($args, $code, $env, $stack)
    {
        list($code0, $env0) = first($stack);
        $code0 = $code0 ?: [['unclosed_term']];
        $env0 = $env0 ?: [];

        array_unshift($env, [$code0, $env0]);

        return new Machine($code, $env, rest($stack));
    }

    function constant($args, $code, $env, $stack)
    {
        $value = first($args);
        array_unshift($stack, $value);

        return new Machine($code, $env, $stack);
    }

    // function call($args, $code, $env, $stack)
    // {
    //     $f = first($args);
    //     return $f($code, $env, $stack);
    // }
}

function evaluate(array $ops)
{
    $vm = new Machine($ops, [], []);
    return $vm->execute();
}
