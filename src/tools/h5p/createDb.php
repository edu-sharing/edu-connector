<?php

require_once __DIR__ . '/../../../config.php';

$pdo = new PDO("mysql:host=" . DBHOST, DBUSER, DBPASSWORD);
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
try {
    $pdo->query("DROP DATABASE `" . DBNAME . "`");
} catch(\Exception $e) {

}
$pdo -> query("CREATE DATABASE `".DBNAME."`");
$pdo -> query("USE `".DBNAME."`");


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
  `id` int(10) UNSIGNED NOT NULL,
  `machine_name` varchar(127) NOT NULL,
  `major_version` int(10) UNSIGNED NOT NULL,
  `minor_version` int(10) UNSIGNED NOT NULL,
  `patch_version` int(10) UNSIGNED NOT NULL,
  `h5p_major_version` int(10) UNSIGNED DEFAULT NULL,
  `h5p_minor_version` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(511) NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL,
  `updated_at` int(10) UNSIGNED NOT NULL,
  `is_recommended` int(10) UNSIGNED NOT NULL,
  `popularity` int(10) UNSIGNED NOT NULL,
  `screenshots` text,
  `license` text,
  `example` varchar(511) NOT NULL,
  `tutorial` varchar(511) DEFAULT NULL,
  `keywords` text,
  `categories` text,
  `owner` varchar(511) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

echo 'Success :)';
exit();
