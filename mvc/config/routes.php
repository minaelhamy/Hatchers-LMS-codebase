<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    spl_autoload_register(function($className) {
        if ( strpos($className, 'CI_') !== 0 ) {
            $file = APPPATH . 'libraries/' . $className . '.php';
            if ( file_exists($file) && is_file($file) ) {
                @include_once( $file );
            }
        }
    });

    $route['version'] = 'app/version';

    $hustlerHost = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
    if ($hustlerHost === 'hustler.hatchers.ai' || $hustlerHost === 'www.hustler.hatchers.ai') {
        $route['default_controller'] = 'hustler/index';
    } else {
        $route['default_controller'] = 'signin/index';
    }

    $route['hustler'] = 'hustler/index';
    $route['hustler/login'] = 'hustler/login';
    $route['hustler/dashboard'] = 'hustler/dashboard';
    $route['hustler/chat'] = 'hustler/chat';
    $route['hustler/market-access'] = 'hustler/market_access';
    $route['hustler/generate-market-access'] = 'hustler/generate_market_access';
    $route['hustler/logout'] = 'hustler/logout';
