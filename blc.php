<?php

// ;;;; Krivine's Machine in Scheme ;;;;
// ;;; 2012 Minori Yamashita <ympbyc@gmail.com> ;;add your name here
// ;;;
// ;;; reference:
// ;;;   http://pauillac.inria.fr/~xleroy/talks/zam-kazam05.pdf
// ;;;   http://pop-art.inrialpes.fr/~fradet/PDFs/HOSC07.pdf
//
// ;;; Notes ;;;
// ;; CLOSURE creates thunks that packs the continuation and environment together.
// ;; To create closures(function objects), CLOSURE the GRAB and expression followed by CONTINUE.
// ;;

// based on:
//  https://github.com/ympbyc/Carrot/blob/master/old/Krivine.scm

// ;;get the value associated with the key symbol
// (define (assoc-ref env key)
//   (let ((binding (assq key env)))
//     (if binding (cdr binding) 'lookup-fail)))

// ;;; Krivine's Machine ;;;
function krivine($code, $env, $stack)
{
    // ;(print (format "code : ~S" code))
    // ;(print (format "env  : ~S" env))
    // ;(print (format "stack: ~S" stack))
    // ;(print (format "g-env: ~S"))
    // ;(newline)

    $inst = first(first($code));
    $inst_arg = rest(first($code));
    $code_rest = rest($code);

    return $inst($inst_arg, $inst_arg, $code_rest, $env, $stack);
}

// ;; refer a value associated with the character from either local-env or global-env
function ACCESS($args, $code, $env, $stack)
{
    $val = assoc_ref($env, first($args));
    array_unshift($stack, $val);

    return krivine($code, $env, $stack);
}

// ;; retrieves a thunk from the stack and replace the state with its.
// ;; thunks carry all the continuation therefore no need to worry about the "frame" or "return"
function _CONTINUE($args, $code, $env, $stack)
{
    $closure    = first($stack);
    $c_code     = assoc_ref($closure, 'code');
    $c_env      = assoc_ref($closure, 'env');

    return krivine($c_code, $c_env, rest($stack));
}

// ;; associate a stack-top value with the character and cons the pair onto the local-env
function GRAB($args, $code, $env, $stack)
{
    // (cons `(,(car args) . ,(car stack)) env)
    array_unshift($env, [first($args), first($stack)]);

    return krivine($code, $env, rest($stack));
}

// ;; creates a thunk that is a data carrying continuation + environment
function CLOSURE($args, $code, $env, $stack)
{
    // (cons `((code . ,(car args)) (env . ,env)) stack))
    array_unshift($stack, [[$code, first($args)], [$env, $env]]);

    return krivine($code, $env, $stack);
}

__halt_compiler();

(define (PRIMITIVE args code env stack)
  (define (get-constant code) ;dirty part
    (receive (result _)
      (guard (exc
        (else (values 'closure '())))
        (Krivine- code env '())) result))
  (let ([subr (car args)]
    [p-args (cdr args)]
    [true  `((,ACCESS true)  (,CONTINUE))]
    [false `((,ACCESS false) (,CONTINUE))])
  (cond
    [(eq? subr 'equal)
     (Krivine-
      (append (if (equal? (get-constant (car p-args)) (get-constant (cadr p-args))) true false) code)
      env stack)]
    [(eq? subr '<)
     (Krivine-
      (append (if (< (get-constant (car p-args)) (get-constant (cadr p-args))) true false) code)
      env stack)]
    [(eq? subr '<=)
     (Krivine-
      (append (if (<= (get-constant (car p-args)) (get-constant (cadr p-args))) true false) code)
      env stack)]
    [(eq? subr '+)
     (Krivine-
      code env
      (cons (+ (get-constant (car p-args)) (get-constant (cadr p-args))) stack))]
    [(eq? subr '-)
     (Krivine-
      code env
      (cons (- (get-constant (car p-args)) (get-constant (cadr p-args))) stack))]
    [(eq? subr '*)
     (Krivine-
      code env
      (cons (* (get-constant (car p-args)) (get-constant (cadr p-args))) stack))]
    [(eq? subr '/)
     (Krivine-
      code env
      (cons (/ (get-constant (car p-args)) (get-constant (cadr p-args))) stack))]
    [(eq? subr '%)
     (Krivine-
      code env
      (cons (mod (get-constant (car p-args)) (get-constant (cadr p-args))) stack))]
    [(eq? subr '++)
     (Krivine-
      code env
      (cons (string-append (get-constant (car p-args)) (get-constant (cadr p-args))) stack))]
    [(eq? subr 'num->str)
     (Krivine-
      code env
      (cons (number->string (get-constant (car p-args))) stack))]
    [(eq? subr 'string?)
     (Krivine-
      (append (if (string? (get-constant (car p-args))) true false) code) env stack)]
    [(eq? subr 'number?)
     (Krivine-
      (append (if (number? (get-constant (car p-args))) true false) code)
      env stack)]
    [(eq? subr 'print)
     (print (get-constant (car p-args)))
     (Krivine-
      code env stack)]
    [(eq? subr 'time)
     (time (get-constant (car p-args)))
     (Krivine-
      code env stack)])))
