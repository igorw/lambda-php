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
    return dump($f).' '.(is_application($arg) ? '('.dump($arg).')' : dump($arg));
}

function is_application($exp)
{
    return is_array($exp)
        && 'λ' !== first($exp)
        && 2 === count($exp);
}
