<?php

require_once __DIR__ . '/../config.php';

$pdo = new PDO("mysql:host=" . DBHOST, DBUSER, DBPASSWORD);
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$pdo->exec("SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");


// refer to https://h5p.org/sites/default/files/class-h5p-plugin.txt

$pdo -> query("CREATE DATABASE IF NOT EXISTS `".DBNAME."`");
$pdo -> query("USE `".DBNAME."`");

try {
    $result = $pdo->query("SELECT 1 FROM h5p_contents LIMIT 1");
    echo "Tables already exists";
    exit();
} catch (Exception $e) {
}

$pdo -> query("CREATE TABLE `h5p_contents` (
  `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `library_id` int(10) UNSIGNED NOT NULL,
  `parameters` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `filtered` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embed_type` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disable` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `content_type` varchar(127) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` varchar(127) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$pdo -> query("CREATE TABLE `h5p_contents_libraries` (
  `content_id` int(10) UNSIGNED NOT NULL,
  `library_id` int(10) UNSIGNED NOT NULL,
  `dependency_type` varchar(31) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `drop_css` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$pdo -> query("CREATE TABLE `h5p_libraries` (
  `id` int(10) AUTO_INCREMENT PRIMARY KEY,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `major_version` int(10) UNSIGNED NOT NULL,
  `minor_version` int(10) UNSIGNED NOT NULL,
  `patch_version` int(10) UNSIGNED NOT NULL,
  `runnable` int(10) UNSIGNED NOT NULL,
  `restricted` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `fullscreen` int(10) UNSIGNED NOT NULL,
  `embed_types` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preloaded_js` text COLLATE utf8mb4_unicode_ci,
  `preloaded_css` text COLLATE utf8mb4_unicode_ci,
  `drop_library_css` text COLLATE utf8mb4_unicode_ci,
  `semantics` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tutorial_url` varchar(1023) COLLATE utf8mb4_unicode_ci NOT NULL,
  `has_icon` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$pdo -> query("CREATE TABLE `h5p_libraries_libraries` (
  `library_id` int(10) UNSIGNED NOT NULL,
  `required_library_id` int(10) UNSIGNED NOT NULL,
  `dependency_type` varchar(31) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$pdo -> query("CREATE TABLE `h5p_libraries_languages` (
  `library_id` int(10) UNSIGNED NOT NULL,
  `language_code` varchar(31) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translation` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$pdo-> query("CREATE TABLE `h5p_libraries_hub_cache` (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    machine_name VARCHAR(127) NOT NULL,
    major_version INT UNSIGNED NOT NULL,
    minor_version INT UNSIGNED NOT NULL,
    patch_version INT UNSIGNED NOT NULL,
    h5p_major_version INT UNSIGNED,
    h5p_minor_version INT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(511) NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    updated_at INT UNSIGNED NOT NULL,
    is_recommended INT UNSIGNED NOT NULL,
    popularity INT UNSIGNED NOT NULL,
    screenshots TEXT,
    license TEXT,
    example VARCHAR(511) NOT NULL,
    tutorial VARCHAR(511),
    keywords TEXT,
    categories TEXT,
    owner VARCHAR(511),
    PRIMARY KEY  (id),
    KEY name_version (machine_name,major_version,minor_version,patch_version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

echo 'Success';
exit();
