<?php
function gen(int $max)
{
    $output = '';
    $output .= "Prime numbers up to $max\n";
    for ($x = 5; $x < $max; $x++) {
        // This if evaluation checks to see if number is odd or even
        $test = TRUE;
        for($i = 3; $i < $x; $i++) {
            if(($x % $i) === 0) {
                $test = FALSE;
                break;
            }
        }
        if ($test) $output .= $x . ',';
    }
    return ($output[-1] === ',') ? substr($output, 0, -1) : $output;
}

$server = new Swoole\HTTP\Server('0.0.0.0', 9501);

$server->on('start', function (Swoole\Http\Server $server) {
    echo "Swoole http server is started at http://127.0.0.1:9501\n";
});

$server->on("request", function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    if (!empty($request->get['max'])) $output = gen((int) $request->get['max']);
    $response->header("Content-Type", "text/plain");
    $response->end("$output\n");
});

$server->start();
