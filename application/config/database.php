<?php defined('SYSPATH') or die('No direct access allowed.');

function database_config() {
  $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
  return array
  (
    'default' => array
    (
      'type'       => 'mysql',
      'connection' => array(
        /**
         * The following options are available for MySQL:
         *
         * string   hostname     server hostname, or socket
         * string   database     database name
         * string   username     database username
         * string   password     database password
         * boolean  persistent   use persistent connections?
         *
         * Ports and sockets may be appended to the hostname.
         */
        'hostname'   => $url["host"] || file_exists('/tmp/mysql.sock') ? ':/tmp/mysql.sock' : 'localhost',
        'database'   => substr($url["path"], 1) || 'ecms',
        'username'   => $url["user"] || 'root',
        'password'   => $url["pass"] || FALSE,
        'persistent' => FALSE,
      ),
      'table_prefix' => '',
      'charset'      => 'utf8',
      'caching'      => FALSE,
      'profiling'    => TRUE,
    ),
  );
}
return database_config();
