<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/health', 'HealthController::index');

$routes->group('api', function($routes) {
    $routes->get('coasters', 'CoasterController::index');      
    $routes->post('coasters', 'CoasterController::create');     
    $routes->get('coasters/(:segment)', 'CoasterController::show/$1'); 
    $routes->put('coasters/(:segment)/status', 'CoasterController::update/$1');
    $routes->delete('coasters/(:segment)', 'CoasterController::delete/$1');

    $routes->post('coasters/(:segment)/wagons', 'WagonController::createWagon/$1');
    $routes->delete('coasters/(:segment)/wagons/(:segment)', 'WagonController::delete/$1/$2'); 

});

