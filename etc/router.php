<?php
// router.php

// This file is a custom router for the PHP built-in server to support Shopware 5.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Office 365 autodiscover feature to prevent CSRF errors
if (preg_match('#^/autodiscover/autodiscover.xml$#i', $uri)) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Redirect VCS directories
if (preg_match('#/\.(svn|git|hg|bzr|cvs)(/|$)#', $uri)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Redirect root folder files
if (preg_match('#/(autoload\.php|composer\.(json|lock|phar)|README\.md|UPGRADE-(.*)\.md|CONTRIBUTING\.md|eula.*\.txt|\.gitignore|.*\.dist|\.env.*)$#', $uri)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Restrict access to shop config files
if (preg_match('#/web/cache/(config_\d+\.json|all.less)$#', $uri)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Restrict access to theme configurations
if (preg_match('#/themes/(.*)(.*\.lock|package\.json|\.gitignore|Gruntfile\.js|all\.less|node_modules/.*)$#', $uri)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Fix for missing authorization-header on fast_cgi installations
if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['HTTP_AUTHORIZATION'];
}

// Handle other requests
$requestedFile = __DIR__ . $uri;
if (file_exists($requestedFile) && !is_dir($requestedFile)) {
    return false; // Serve the requested resource as-is.
}

// Route the request to shopware.php
require_once __DIR__ . '/shopware.php';
