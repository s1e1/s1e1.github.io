<?php
/**
 * Helper pour les URLs
 */

function url($path = '') {
    $baseUrl = defined('APP_URL') ? APP_URL : '';
    $path = ltrim($path, '/');
    return $baseUrl . '/' . $path;
}

function asset($path) {
    return '/public/assets/' . ltrim($path, '/');
}

