<?php

namespace igorw\lambda;

use Symfony\Component\Debug\ErrorHandler;

require 'vendor/autoload.php';

function prompt($mode) {
    echo "$mode> ";

    return true;
}

function format($result) {
    if (is_bool($result)) {
        return json_encode($result);
    }

    return $result;
}

$mode = 'i';

while (prompt($mode) && false !== ($line = fgets(STDIN))) {
    $exp = trim($line);

    if (in_array($exp, ['i', 'b'], true)) {
        $mode = $exp;
        continue;
    }

    $factories = [
        'i' => 'igorw\lambda\to_int',
        'b' => 'igorw\lambda\to_bool',
    ];

    try {
        $factory = $factories[$mode];
        echo format(evaluate($factory(parse($exp))))."\n";
    } catch (\Exception $e) {
        echo $e->getMessage()."\n";
    }
}

echo "\n";
