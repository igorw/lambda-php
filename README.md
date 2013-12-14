# lambda-php

Lambda calculus interpreter in PHP.

## Lambda calculus

Lambda calculus is a very minimal programming language that was invented in
1936 by Alonzo Church. It is the functional equivalent of the Turing Machine.

Lambda calculus has only three concepts: Function definitions, lexically
scoped variables, function application.

An example term would be the identity function:

    λx.x

The first part `λx` defines a function that takes an `x`, the `.` signifies
that the part that follows is the function body. The body just returns `x`.

In PHP, you would write the same thing as follows:

    function ($x) {
        return $x;
    }

You can nest function definitions. Here is a function returning a function:

    λx.λy.x

And you can also *apply* a function to an argument, which just means calling
the function.

    λf.λg.f g

Which is the short hand (left-associative) form of writing

    λf.λg.(f g)

Nested calls like:

    λf.λg.λh.f g h

Are interpreted as:

    λf.λg.λh.((f g) h)

If you want to change the grouping to be right-associative, you need to
explicitly group them in parentheses:

    λf.λg.λh.(f (g h))

Interestingly, lambda calculus is turing complete. Using just these three
concepts you can represent *any* computation.

Check out the links at the bottom for more details on how to do stuff in
lambda calculus.

## Interpreter

This project consists of a lambda calculus expression parser using
[dissect](https://github.com/jakubledl/dissect), and an *eval-apply*
interpreter based on [Matt Might's implementation in
scheme](http://matt.might.net/articles/implementing-a-programming-language/).

For examples of how to do numbers (church encoding), booleans, arithmetic,
boolean logic, looping (recursion), etc. look at `example.php`.

## REPL

This project ships with a read-eval-print-loop that you can use to evaluate
lambda calculus expressions:

    $ php repl.php

By default, it is in *int-mode*, expecting the result of the expression to be
a church-encoded number. Example:

    $ php repl.php
    i> λf.λx.f (f (f x))
    3

You can switch to *bool-mode* by sending the `b` command:

    $ php repl.php
    i> b
    b> λx.λy.x
    true

## Further reading

* [Lambda Calculus - Wikipedia](http://en.wikipedia.org/wiki/Lambda_calculus)
* [Matt Might: 7 lines of code, 3 minutes](http://matt.might.net/articles/implementing-a-programming-language/)
* [Tom Stuart: Programming with Nothing](http://codon.com/programming-with-nothing)
* [Erkki Lindpere: Parsing Lambda Calculus in Scala](http://zeroturnaround.com/rebellabs/parsing-lambda-calculus-in-scala/)
