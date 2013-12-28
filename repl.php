<?php

namespace igorw\lambda;

use Symfony\Component\Debug\ErrorHandler;

require 'vendor/autoload.php';

function prompt($mode, $interactive)
{
    if ($interactive) {
        echo "$mode> ";
    }

    return true;
}

function format_raw($result)
{
    return dump($result);
}

function identity($x)
{
    return $x;
}

function format($result)
{
    if (is_bool($result)) {
        return json_encode($result);
    }

    return $result;
}

$mode = 'i';

foreach (['b', 'r'] as $flag) {
    if (in_array("-$flag", $argv, true)) {
        $mode = $flag;
        break;
    }
}

$interactive = true;

if (in_array('-n', $argv, true)) {
    $interactive = false;
}

while (prompt($mode, $interactive) && false !== ($line = fgets(STDIN))) {
    $exp = trim($line);

    $factories = [
        'i' => 'igorw\lambda\to_int',
        'b' => 'igorw\lambda\to_bool',
        'r' => 'igorw\lambda\identity',
    ];

    if (in_array($exp, array_keys($factories), true)) {
        $mode = $exp;
        continue;
    }

    try {
        $factory = $factories[$mode];
        $format = __NAMESPACE__.'\\'.('r' === $mode ? 'format_raw' : 'format');
        echo $format(evaluate($factory(parse($exp))))."\n";
    } catch (\Exception $e) {
        echo $e->getMessage()."\n";
    }
}

if ($interactive) {
    echo "\n";
}
