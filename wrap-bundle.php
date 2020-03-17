<?php
/**
 * Wrap a bundle in a Symfony Application
 * This script helps you to automate a Symfony application installation around a
 * bundle.
 *
 * @author    Sébastien Morel (Plopix) <morel.seb@gmail.com>
 * @copyright 2020 Sébastien Morel (Plopix)
 * @license   https://github.com/Plopix/symfony-bundle-app-wrapper/blob/master/LICENSE
 */

$appRootDir = $_SERVER['WRAP_APP_DIR'] ?? null;
$bundleDir = $_SERVER['WRAP_BUNDLE_DIR'] ?? getenv('PLATFORM_DIR') ?? '~';

if (null === $appRootDir) {
    echo "WRAP_APP_DIR must be defined.";
}

if (file_exists("{$appRootDir}/app/AppKernel.php")) {
    // this is Symfony 4
    handleSymfony4($appRootDir);
}

if (file_exists("{$appRootDir}/src/Kernel.php")) {
    // this is Symfony 5
    handleSymfony5($appRootDir);
}

trickComposer($appRootDir, $bundleDir);

function handleSymfony5(string $appRootDir)
{
    $bundleFile = "{$appRootDir}/config/bundles.php";
    $bundleList = file_get_contents($bundleFile);
    $bundles = array_map(
        function ($line) {
            $fqdn = trim(trim($line, "- "));

            return "{$fqdn}::class => [ 'all'=> true ],";
        },
        file(__DIR__."/bundles.yaml")
    );

    $bundleList = \str_replace('];', implode(PHP_EOL, $bundles).PHP_EOL.'];', $bundleList);
    file_put_contents($bundleFile, $bundleList);

    $bundleName = substr(trim(file(__DIR__."/configs.yaml")[0]), 0, -1);

    // Inject the configurations
    file_put_contents(
        "{$appRootDir}/config/packages/{$bundleName}.yaml",
        file_get_contents(__DIR__."/configs.yaml")
    );

    // Inject the routes
    file_put_contents(
        "{$appRootDir}/config/routes/{$bundleName}.yaml",
        file_get_contents(__DIR__."/routes.yaml")
    );
}

function trickComposer(string $appRootDir, string $bundleDir)
{
    $composerJsonPath = "{$appRootDir}/composer.json";
    $data = json_decode(file_get_contents($composerJsonPath), true);
    $bundleComposerJson = json_decode(file_get_contents("{$bundleDir}/composer.json"), true);

    // re map paths, one directory before because composer create-project install in a dir
    $psr4 = array_map(
        function ($item) {
            return "../{$item}";
        },
        $bundleComposerJson["autoload"]['psr-4'] ?? []
    );

    $data["autoload"]['psr-4'] += $psr4;
    $data['require'] += $bundleComposerJson['require'] ?? [];
    $data['require-dev'] += $bundleComposerJson['require-dev'] ?? [];

    // write the composer.json
    file_put_contents(
        $composerJsonPath,
        json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
}

function handleSymfony4(string $appRootDir)
{
    $kernelFilePath = "{$appRootDir}/app/AppKernel.php";
    $kernel = file_get_contents($kernelFilePath);
    $bundles = array_map(
        function ($line) {
            $fqdn = trim(trim($line, "- "));

            return '$bundles[] = new '.$fqdn.';';
        },
        file(__DIR__."/bundles.yaml")
    );
    $bundles[] = 'return $bundles;';
    $kernel = \str_replace($bundles[count($bundles) - 1], implode(PHP_EOL, $bundles), $kernel);
    file_put_contents($kernelFilePath, $kernel);

    // Inject the configurations
    file_put_contents("{$appRootDir}/app/config/config.yml", file_get_contents(__DIR__."/configs.yaml"), FILE_APPEND);

    // Inject the routes
    file_put_contents("{$appRootDir}/app/config/routing.yml", file_get_contents(__DIR__."/routes.yaml"), FILE_APPEND);
}




