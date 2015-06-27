<?php

define('PROJECT_ROOT', realpath(__DIR__.'/..'));

require(PROJECT_ROOT.'/vendor/autoload.php');



error_reporting(E_ALL);
ini_set('display_errors', 1);



/**
 * Load Configruation
 */
$config = require(PROJECT_ROOT.'/config.php');



/**
 * Prepare dependency injector
 */
$injector = new \Auryn\Injector();
$injector->share($injector);



/**
 * Prepare Logger
 */
$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter([
    'handlers' => [
        new \Monolog\Handler\StreamHandler(PROJECT_ROOT.'/logs/'.date('Y-m-d').'.log')
    ]
]);



/**
 * Prepare Slim
 */
$slim = new \Slim\Slim([
    'log.writer' => $logger,
    'view' => new \Slim\Views\Twig(),
    'templates.path' => PROJECT_ROOT.'/src/views'
]);
$injector->share($slim);



/**
 * Prepare View
 */
$slim->view->parserOptions['debug'] = true;
#$slim->view->parserOptions['cache'] = $slim->config('templates.path').'/cache';
$slim->view->parserExtensions[] = new \Slim\Views\TwigExtension();



/**
 * Load the Middleware class with clasures for loading controllers and middlewares
 */
$mws = $injector->make('ZerobRSS\Middlewares');



/**
 * Prepare Routes
 */
$slim->get('/',                 $mws->db(), $mws->controllerLoader('Root', 'index'));
$slim->get('/assets/css/:file',             $mws->controllerLoader('Scss', 'get'));



/**
 * Run application
 */
$slim->run();
