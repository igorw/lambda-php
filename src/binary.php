<?php

namespace igorw\lambda\binary;

// parse, return pair of [parsed, offset]
// parsed uses de-bruijn indices
function parse_term($bits)
{
    if ('00' === substr($bits, 0, 2)) {
        list($body, $rest) = parse_term(substr($bits, 2));

        return [
            ['λ', $body],
            $rest,
        ];
    }

    if ('01' === substr($bits, 0, 2)) {
        $rest = substr($bits, 2);
        list($l, $rest) = parse_term($rest);
        list($r, $rest) = parse_term($rest);

        return [
            [$l, $r],
            $rest,
        ];
    }

    if ('1' === $bits[0]) {
        $i = 0;
        while ($bits[++$i] !== '0');

        return [
            ['offset', $i],
            substr($bits, $i + 1),
        ];
    }

    throw new \InvalidArgumentException("Invalid binary term '$bits'");
}

function parse($bits)
{
    $bits = preg_replace('/\s/s', '', $bits);
    list($parsed, $rest) = parse_term($bits);

    if (strlen($rest) > 0) {
        throw new \InvalidArgumentException("Non-closed binary term '$bits' had remaining part '$rest'");
    }

    return $parsed;
}

// identity
// ['λ', ['offset', 1]]
// var_dump(parse('0010'));

// false
// ['λ', ['λ', ['offset', 1]]]
// var_dump(parse('000010'));

// true
// ['λ', ['λ', ['offset', 2]]]
// var_dump(parse('0000110'));

// λx.x x
// ['λ', [['offset', 1], ['offset', 1]]]
// var_dump(parse('00 01 10 10'));

// λx.false
// ['λ', ['λ', ['λ', ['offset', 1]]]]
// var_dump(parse('00000010'));
