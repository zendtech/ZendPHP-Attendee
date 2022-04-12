<?php
try {
    $fn  = 'lookup.html';
    $dsn = 'mysql:host=10.10.10.30:3306;dbname=geonames';
    $usr = 'geonames';
    $pwd = 'password';
    $pdo = new PDO($dsn, $usr, $pwd);
    $msg = '';
    $results = [];
    foreach ($results as $row)
        $msg .= implode(' | ', $row) . PHP_EOL;
} catch (Throwable $t) {
    $fn  = 'error.html';
    $msg = $t->getMessage() . ':' . $t->getTraceAsString();
    error_log($msg);
}
$contents = file_get_contents(__DIR__ . '/' . $fn);
echo str_replace('<!-- REPLACE -->',
                 '<pre>' . $msg . '</pre>',
                 $contents);
