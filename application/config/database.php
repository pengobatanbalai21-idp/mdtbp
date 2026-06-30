<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| Sesuaikan hostname, username, password, database sesuai server Anda
| -------------------------------------------------------------------
*/

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn'      => '',
    // Ambil dari environment (Docker) bila ada; fallback ke default lokal (XAMPP).
    // Produksi tidak terpengaruh (pakai config/production/database.php).
    'hostname' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'root',       // ganti sesuai user MySQL Anda
    'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '', // ganti sesuai password
    'database' => getenv('DB_NAME') ?: 'clinic_db',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt'  => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE,
);
