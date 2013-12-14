<?php

namespace igorw\lambda;

use Symfony\Component\Debug\ErrorHandler;

require 'vendor/autoload.php';

function prompt($mode)
{
    echo "$mode> ";

    return true;
}

function format_raw($result)
{
    ob_start();
    var_dump($result);
    return trim(ob_get_clean());
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

while (prompt($mode) && false !== ($line = fgets(STDIN))) {
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

echo "\n";
