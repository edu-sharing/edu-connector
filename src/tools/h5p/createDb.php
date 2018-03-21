<?php

require_once 'config.php';

$pdo = new PDO("mysql:host=" . DBHOST, DBUSER, DBPASSWORD);
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$pdo -> query("DROP DATABASE `".DBNAME."`");
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

echo 'Success :)<br/>Do not forget to delete custom content from /test';
exit();


unlink ('h5p');

$db = new SQLite3('h5p');

$db-> exec("CREATE TABLE h5p_contents (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      created_at TIMESTAMP NOT NULL DEFAULT 0,
      updated_at TIMESTAMP NOT NULL DEFAULT 0,
      user_id INT UNSIGNED,
      title VARCHAR(255) NOT NULL,
      library_id INT UNSIGNED NOT NULL,
      parameters LONGTEXT NOT NULL,
      filtered LONGTEXT NOT NULL,
      slug VARCHAR(127),
      embed_type VARCHAR(127) NOT NULL,
      disable INT UNSIGNED NOT NULL DEFAULT 0,
      content_type VARCHAR(127) NULL,
      author VARCHAR(127) NULL,
      license VARCHAR(7) NULL,
      keywords TEXT NULL,
      description TEXT NULL)");

// Keep track of content dependencies
$db-> exec("CREATE TABLE h5p_contents_libraries (
      content_id INT UNSIGNED NOT NULL,
      library_id INT UNSIGNED NOT NULL,
      dependency_type VARCHAR(31) NOT NULL,
      weight SMALLINT UNSIGNED NOT NULL DEFAULT 0,
      drop_css TINYINT UNSIGNED NOT NULL,
      PRIMARY KEY  (content_id,library_id,dependency_type))");

// Keep track of data/state when users use content (contents >-< users)
/*
$db-> exec("CREATE TABLE h5p_contents_user_data (
      content_id INT UNSIGNED NOT NULL,
      user_id INT UNSIGNED NOT NULL,
      sub_content_id INT UNSIGNED NOT NULL,
      data_id VARCHAR(127) NOT NULL,
      data LONGTEXT NOT NULL,
      preload TINYINT UNSIGNED NOT NULL DEFAULT 0,
      invalidate TINYINT UNSIGNED NOT NULL DEFAULT 0,
      updated_at TIMESTAMP NOT NULL DEFAULT 0,
      PRIMARY KEY  (content_id,user_id,sub_content_id,data_id))");
*/
// Create a relation between tags and content
/*
$db-> exec("CREATE TABLE h5p_contents_tags (
      content_id INT UNSIGNED NOT NULL,
      tag_id INT UNSIGNED NOT NULL,
      PRIMARY KEY  (content_id,tag_id))");
*/
// Keep track of tags
/*
$db-> exec("CREATE TABLE h5p_tags (
      id INT PRIMARY KEY,
      name VARCHAR(31) NOT NULL)");
*/
// Keep track of results (contents >-< users)
/*
$db-> exec("CREATE TABLE h5p_results (
      id INT PRIMARY KEY,
      content_id INT UNSIGNED NOT NULL,
      user_id INT UNSIGNED NOT NULL,
      score INT UNSIGNED NOT NULL,
      max_score INT UNSIGNED NOT NULL,
      opened INT UNSIGNED NOT NULL,
      finished INT UNSIGNED NOT NULL,
      time INT UNSIGNED NOT NULL)");
*/
// Keep track of h5p libraries
$db-> exec("CREATE TABLE h5p_libraries (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      created_at TIMESTAMP NOT NULL DEFAULT 0,
      updated_at TIMESTAMP NOT NULL DEFAULT 0,
      name VARCHAR(127) NOT NULL,
      title VARCHAR(255) NOT NULL,
      major_version INT UNSIGNED NOT NULL,
      minor_version INT UNSIGNED NOT NULL,
      patch_version INT UNSIGNED NOT NULL,
      runnable INT UNSIGNED NOT NULL,
      restricted INT UNSIGNED NOT NULL DEFAULT 0,
      fullscreen INT UNSIGNED NOT NULL,
      embed_types VARCHAR(255) NOT NULL,
      preloaded_js TEXT NULL,
      preloaded_css TEXT NULL,
      drop_library_css TEXT NULL,
      semantics TEXT NOT NULL,
      tutorial_url VARCHAR(1023),
      has_icon INT UNSIGNED NOT NULL DEFAULT 0)");

// Keep track of h5p libraries content type cache
/*
$db-> exec("CREATE TABLE h5p_libraries_hub_cache (
      id INT UNSIGNED NOT NULL,
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
      PRIMARY KEY  (id))");
*/
// Keep track of h5p library dependencies
$db-> exec("CREATE TABLE h5p_libraries_libraries (
      library_id INT UNSIGNED NOT NULL,
      required_library_id INT UNSIGNED NOT NULL,
      dependency_type VARCHAR(31) NOT NULL,
      PRIMARY KEY  (library_id,required_library_id))");

// Keep track of h5p library translations
$db-> exec("CREATE TABLE h5p_libraries_languages (
      library_id INT UNSIGNED NOT NULL,
      language_code VARCHAR(31) NOT NULL,
      translation TEXT NOT NULL,
      PRIMARY KEY  (library_id,language_code))");
/*
// Keep track of logged h5p events
$db-> exec("CREATE TABLE h5p_events (
      id INT UNSIGNED NOT NULL,
      user_id INT UNSIGNED NOT NULL,
      created_at INT UNSIGNED NOT NULL,
      type VARCHAR(63) NOT NULL,
      sub_type VARCHAR(63) NOT NULL,
      content_id INT UNSIGNED NOT NULL,
      content_title VARCHAR(255) NOT NULL,
      library_name VARCHAR(127) NOT NULL,
      library_version VARCHAR(31) NOT NULL,
      PRIMARY KEY  (id))");
*/
// A set of global counters to keep track of H5P usage
/*
$db-> exec("CREATE TABLE h5p_counters (
      type VARCHAR(63) NOT NULL,
      library_name VARCHAR(127) NOT NULL,
      library_version VARCHAR(31) NOT NULL,
      num INT UNSIGNED NOT NULL,
      PRIMARY KEY  (type,library_name,library_version))");
*/
/*
$db-> exec("CREATE TABLE h5p_libraries_cachedassets (
      library_id INT UNSIGNED NOT NULL,
      hash VARCHAR(64) NOT NULL,
      PRIMARY KEY  (library_id,hash))");
*/
/*
$db-> exec("CREATE TABLE h5p_tmpfiles (
      id INT UNSIGNED NOT NULL,
      path VARCHAR(255) NOT NULL,
      created_at INT UNSIGNED NOT NULL,
      PRIMARY KEY  (id))");
*/
echo 'Success :)';