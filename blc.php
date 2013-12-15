<?php

/*
Binary Lambda Calculus

e : 00 e   (Lam)
  | 01 e e (App)
  | [1] 0  (Var)

Var encodes DeBruijn indices with

0 = 10
1 = 110
2 = 1110
3 = 11110
...

Closure : TE Term Env
        | IDX Int

Env : [Closure]

[[e]] = Return Closure | Apply Term Term Env

*/

// based on the python implementation:
// https://github.com/sdiehl/bnlc
//
// other references:
//  * http://stackoverflow.com/q/18034246

#------------------------------------------------------------------------
# Structures
#------------------------------------------------------------------------

class App
{
    function __construct($e1, $e2)
    {
        $this->e1 = $e1;
        $this->e2 = $e2;
    }

    function __toString()
    {
        return sprintf('%s(%s)', $this->e1, $this->e2);
    }
}

class Lam
{
    function __construct($e)
    {
        $this->e = $e;
    }

    function __toString()
    {
        return sprintf('\%s', $this->e);
    }
}

class BVar
{
    function __construct($i)
    {
        $this->i = $i;
    }

    function __toString()
    {
        return (string) $this->i;
    }
}

class BReturn
{
    function __construct($cls)
    {
        $this->cls = $cls;
    }
}

class Apply
{
    function __construct($e1, $e2, $env)
    {
        $this->e1 = $e1;
        $this->e2 = $e2;
        $this->env = $env;
    }
}

class Idx
{
    function __construct($i)
    {
        $this->i = $i;
    }
}

class TE
{
    function __construct($term, $env)
    {
        $this->term = $term;
        $this->env = $env;
    }
}

#------------------------------------------------------------------------
# Normalization
#------------------------------------------------------------------------

function span($p, $xs)
{
    for ($i = 0; $i < strlen($xs); $i++) {
        $x = $xs[$i];

        if (!$p($x)) {
            return [substr($xs, 0, $i), substr($xs, $i)];
        }
    }
    return [[], $xs];
}

function parse($xs)
{
    if ($xs[0] == '0' && $xs[1] == '0') {
        list($t, $xs) = parse(substr($xs, 2));
        return [new Lam($t), $xs];
    } elseif ($xs[0] == '0' && $xs[1] == '1') {
        list($l, $xs) = parse(substr($xs, 2));
        list($r, $xss) = parse($xs);
        return [new App($l, $r), $xss];
    } elseif ($xs[0] == '1') {
        list($os, $xs) = span(function ($x) { return $x == '1'; }, $xs);
        return [new BVar(strlen($os)), '0'.$xs];
    } else {
        throw new \Exception("Invalid expression");
    }
}

function whnf($e, $env)
{
    if ($e instanceof BVar) {
        $term = $env[$e->i-1];
        if ($term instanceof Idx) {
            return new BReturn($term);
        }
        elseif ($term instanceof TE) {
            return whnf($term->term, $term->env);
        }
    } elseif ($e instanceof Lam) {
        return new BReturn(new TE($e, $env));
    } elseif ($e instanceof App) {
        $l = $e->e1;
        $r = $e->e2;

        $wl = whnf($l, $env);
        if ($wl instanceof BReturn &&
            $wl->cls instanceof TE &&
            $wl->cls->term instanceof Lam) {

            $le = $wl->cls->term->e;
            $env_ = $wl->cls->env;
            return whnf($le, array_merge([new TE($r, $env)], $env_));
        } else {
            return new Apply($wl, $r, $env);
        }
    } else {
        assert(0);
    }
}

function _nf($d, $t)
{
    if ($t instanceof Apply) {
        return new App(_nf($d, $t->e1), nf($d, $t->e2, $t->env));
    } elseif ($t instanceof BReturn) {
        if ($t->cls instanceof TE && $t->cls->term instanceof Lam) {
            return new Lam(nf(($d+1), $t->cls->term->e, array_merge([new Idx($d)], $t->cls->env)));
        } elseif ($t->cls instanceof TE) {
            return $t->cls->term;
        } elseif ($t->cls instanceof Idx) {
            return new BVar($d - $t->cls->i - 1);
        } else {
            assert(0);
        }
    } else {
        assert(0);
    }
}

function nf($d, $t, $env)
{
    return _nf($d, whnf($t, $env));
}

$prg1 = '0010';
$prg2 = '0000000101101110110';
$prg3 = '00010001100110010100011010000000010110000010010001010111110111101001000110100001110011010000000000101101110011100111111101111000000001111100110111000000101100000110110';
$eval = '0101000110100000000101011000000000011110000101111110011110000101110011110000001111000010110110111001111100001111100001011110100111010010110011100001101100001011111000011111000011100110111101111100111101110110000110010001101000011010';

$p1 = parse($prg1)[0];
$p2 = parse($prg2)[0];
$p3 = parse($prg3)[0];
$pe = parse($eval)[0];

var_dump($p1);

echo (string) $p2."\n";
echo (string) nf(0, $p2, [])."\n";
