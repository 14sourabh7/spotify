<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Config\ConfigFactory;
use Phalcon\Http\Response;

use Phalcon\Session\Manager as SessionManager;
use Phalcon\Session\Adapter\Stream;

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');



require(BASE_PATH . '/vendor/autoload.php');



// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
    ]
);

$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH . "/components",
    ]
);

$loader->register();



$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$application = new Application($container);


//config di
$filename = '../app/config/config.php';
$factory = new ConfigFactory();

$config =  $factory->newInstance('php', $filename);

$container->set(
    'config',
    $config,
    true
);



//response
$container->set(
    'response',
    function () {
        return
            $response = new Response();
    }
);

//session
$container->set(
    'session',
    function () {
        $session = new SessionManager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session->setAdapter($files);
        $session->start();
        return $session;
    }
);

//di for api helper location class
$container->set('spotify', new \App\Components\Spotify());



try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
