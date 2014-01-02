<?php

namespace igorw\lambda;

function dump($exp)
{
    if (is_string($exp)) {
        return $exp;
    }

    if ('λ' === first($exp)) {
        list($_, $arg, $body) = $exp;
        return 'λ'.dump($arg).'.'.dump($body);
    }

    list($f, $arg) = $exp;
    $f = is_abs($f) ? '('.dump($f).')' : dump($f);
    $arg = is_app($arg) || is_abs($arg) ? '('.dump($arg).')' : dump($arg);
    return $f.' '.$arg;
}

function is_abs($exp)
{
    return is_array($exp)
        && 'λ' === first($exp);
}

function is_app($exp)
{
    return is_array($exp)
        && 'λ' !== first($exp)
        && 2 === count($exp);
}
