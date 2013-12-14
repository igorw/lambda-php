<?php

// lambda calculus parser
//
// based on the grammar from:
// http://zeroturnaround.com/rebellabs/parsing-lambda-calculus-in-scala/

namespace igorw\lambda;

use Dissect\Lexer\SimpleLexer;
use Dissect\Parser\Grammar;
use Dissect\Parser\LALR1\Parser;

class LambdaLexer extends SimpleLexer
{
    public function __construct()
    {
        $this->token('λ');
        $this->regex('identifier', '/[a-z]+/');
        $this->token('.');
        $this->token(' ');
        $this->token('(');
        $this->token(')');
    }
}

class LambdaGrammar extends Grammar
{
    public function __construct()
    {
        $this('expr')
            ->is('lambda')
            ->is('application')
            ->is('variable')
            ->is('parens');

        $this('lambda')
            ->is('λ', 'variable', '.', 'expr')
            ->call(function ($lambda, $var, $dot, $expr) {
                return ['λ', $var, $expr];
            });

        // @todo support application without explicit
        // space, while preserving left-associativity
        $this('application')
            ->is('expr', ' ', 'expr')
            ->call(function ($f, $_, $x) {
                return [$f, $x];
            });

        $this('variable')
            ->is('identifier')
            ->call(function ($var) {
                return $var->getValue();
            });

        $this('parens')
            ->is('(', 'expr', ')')
            ->call(function ($lp, $expr, $rp) {
                return $expr;
            });

        $this->operators(' ')->left();

        $this->start('expr');
    }
}

function parse($input)
{
    $lexer = new LambdaLexer();
    $parser = new Parser(new LambdaGrammar());

    $stream = $lexer->lex($input);
    return $parser->parse($stream);
}
