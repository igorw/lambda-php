<?php

namespace igorw\lambda;

require 'vendor/autoload.php';

// identity: returns its argument
// λx.x
$identity = ['λ', 'x', 'x'];
$identity_a = ['λ', 'a', 'a'];

// omega: loops forever
// (λf.f f) (λf.f f)
$omega = [['λ', 'f', ['f', 'f']], ['λ', 'f', ['f', 'f']]];

// church numerals, represent numbers as functions
// λf.λx.x
// λf.λx.f x
// λf.λx.f (f x)
$zero = ['λ', 'f', ['λ', 'x', 'x']];
$one  = ['λ', 'f', ['λ', 'x', ['f', 'x']]];
$two  = ['λ', 'f', ['λ', 'x', ['f', ['f', 'x']]]];

// increment a church numeral by one
// λn.λf.λx.f (n f x)
$succ = ['λ', 'n', ['λ', 'f', ['λ', 'x', ['f', [['n', 'f'], 'x']]]]];

// addition
// λm.λn.m SUCC n
$plus = ['λ', 'm', ['λ', 'n', [['m', $succ], 'n']]];

// decrement
// λn.λf.λx.n (λg.λh.h (g f)) (λu.x) (λu.u)
$pred = ['λ', 'n', ['λ', 'f', ['λ', 'x',
            [[['n', ['λ', 'g', ['λ', 'h', ['h', ['g', 'f']]]]],
                ['λ', 'u', 'x']],
                ['λ', 'u', 'u']]]]];

// subtraction
// λm.λn.n PRED m
$sub = ['λ', 'm', ['λ', 'n', [['n', $pred], 'm']]];

// multiplication
// λm.λn.λf.m (n f)
$mult = ['λ', 'm', ['λ', 'n', ['λ', 'f', ['m', ['n', 'f']]]]];

// exp
// λm.λn.n m
$exp = ['λ', 'm', ['λ', 'n', ['n', 'm']]];

// Y combinator, recursion
// does not work due to call-by-value, it loops forever
// λf.(λx.f (x x)) (λx.f (x x))
$Y = ['λ', 'f', [['λ', 'x', ['f', ['x', 'x']]],
                 ['λ', 'x', ['f', ['x', 'x']]]]];

// Z combinator
// applicative order Y combinator, no longer loops
// λf.(λx.f (λv.((x x) v))) (λx.f (λv.((x x) v)))
$Z = ['λ', 'f', [['λ', 'x', ['f', ['λ', 'v', [['x', 'x'], 'v']]]],
                 ['λ', 'x', ['f', ['λ', 'v', [['x', 'x'], 'v']]]]]];

// true
// λx.λy.x
$true = ['λ', 'x', ['λ', 'y', 'x']];

// false
// λx.λy.y
$false = ['λ', 'x', ['λ', 'y', 'y']];

// logical and
// λp.λq.p q p
$and = ['λ', 'p', ['λ', 'q', [['p', 'q'], 'p']]];

// logical or
// λp.λq.p p q
$or = ['λ', 'p', ['λ', 'q', [['p', 'p'], 'q']]];

// logical not
// λp.λa.λb.p b a
$not = ['λ', 'p', ['λ', 'a', ['λ', 'b', [['p', 'b'], 'a']]]];

// if
// λp.λa.λb.p a b
$if = ['λ', 'p', ['λ', 'a', ['λ', 'b', [['p', 'a'], 'b']]]];

// more church numerals
$three = [$succ, $two];
$four  = [[$mult, $two], $two];
$five  = [[$plus, $four], $one];
$ten   = [[$mult, $five], $two];
$forty = [[$mult, $ten], $four];
$fortytwo = [[$plus, $forty], $two];

// is zero
// λn.n (λx.FALSE) TRUE
$is_zero = ['λ', 'n', [['n', ['λ', 'x', $false]], $true]];

// less than or equal
// λm.λn.ISZERO (SUB m n),
$lte = ['λ', 'm', ['λ', 'n', [$is_zero, [[$sub, 'm'], 'n']]]];

$fact = [$Z, ['λ', 'fact',
            ['λ', 'n',
                call($if, [$is_zero, 'n'],
                    $one,
                    lazy([[$mult, 'n'], ['fact', [$pred, 'n']]]))]]];

$fib = [$Z, ['λ', 'fib',
        ['λ', 'n',
            call($if, call($lte, 'n', $one),
                'n',
                lazy(call($plus, ['fib', call($sub, 'n', $two)],
                                 ['fib', call($sub, 'n', $one)])))]]];

// pair
// λx.λy.λf.f x y
$pair = ['λ', 'x', ['λ', 'y', ['λ', 'f', call('f', 'x', 'y')]]];

// FIRST := λp.p TRUE
// SECOND := λp.p FALSE
// NIL := λx.TRUE
// NULL := λp.p (λx.λy.FALSE)
$first = ['λ', 'p', ['p', $true]];
$second = ['λ', 'p', ['p', $false]];
$nil = ['λ', 'x', $true];
$null = ['λ', 'p', ['p', ['λ', 'x', ['λ', 'y', $false]]]];

// var_dump(evaluate($identity));
// var_dump(evaluate([$identity, $identity_a]));
// var_dump(evaluate([[[$identity, $identity], $identity], $identity_a]));
// var_dump(evaluate($omega));
// var_dump(evaluate(to_int($zero)));
// var_dump(evaluate(to_int($one)));
// var_dump(evaluate(to_int($two)));
// var_dump(evaluate(to_int([$succ, [$succ, $zero]])));
// var_dump(evaluate(to_int([[$plus, $zero], $one])));
// var_dump(evaluate(to_int([$pred, $two])));
// var_dump(evaluate(to_int([[$sub, $fortytwo], $two])));
// var_dump(evaluate(to_int([[$mult, $two], $two])));
// var_dump(evaluate(to_bool([[$and, $true], $true])));
// var_dump(evaluate(to_bool([[$and, $true], $false])));
// var_dump(evaluate(to_bool([$not, $true])));
// var_dump(evaluate(to_int([[[$if, $true], $fortytwo], 0])));
// var_dump(evaluate(to_bool([$is_zero, $zero])));
// var_dump(evaluate(to_bool([$is_zero, $one])));
// var_dump(evaluate(to_bool([[$lte, $one], $one])));
// var_dump(evaluate(to_bool([[$lte, $one], $two])));
// var_dump(evaluate(to_bool([[$lte, $two], $one])));
// var_dump(evaluate(to_int([$fact, $five])));
// var_dump(evaluate(to_int([$fib, call($plus, $five, $two)])));
// var_dump(evaluate(parse('(λf.f f) (λf.f f)')));
// var_dump(evaluate(to_int(call($exp, $two, $three))));
// var_dump(evaluate(to_int(
//     call($sub,
//          call($exp,
//               $two,
//               call($plus,
//                    call($mult, $two, $five),
//                    $one)),
//          call($plus,
//               call($exp, $two, $five),
//               $two)))));
