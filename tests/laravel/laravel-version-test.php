<?php

const TEST_PATH = '/tmp/laravel/app/';

/** @var array $composerConfig */
$composerConfig = json_decode(file_get_contents('/app/composer.json'), $assoc = true);

$laravelVersions = array_map(function ($version) {
    return trim($version, '^<>.0');
}, explode('|', $composerConfig['require']['illuminate/database']));

foreach ($laravelVersions as $laravelVersion) {
    echo "Testing for Laravel {$laravelVersion}\n";

    passthru(
        'rsync --delete -a --exclude=vendor/ --exclude=.*/ /app ' .
        dirname(TEST_PATH)
    );

    chdir(TEST_PATH);

    $composerConfig['require']['illuminate/database'] = "^{$laravelVersion}.0";
    $testBench = intval($laravelVersion) - 2;
    $composerConfig['require-dev']['orchestra/testbench'] = "^{$testBench}.0";

    file_put_contents(TEST_PATH . 'composer.json', json_encode($composerConfig, JSON_PRETTY_PRINT));

    exec('composer update -q');
    passthru('php ./vendor/bin/phpunit --colors=always', $resultCode);

    if ($resultCode !== 0) {
        echo "\n\nTests failed for Laravel {$laravelVersion}\n";
        exit(1);
    }
}
