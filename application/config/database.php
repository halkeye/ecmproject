<?php defined('SYSPATH') or die('No direct access allowed.');

function database_config() {
  if (getenv("CLEARDB_DATABASE_URL")) {
    $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
    $dsn = 'mysql:dbname=' . substr($url["path"], 1) . ';host=' . $url["host"];
    $user = $url["user"];
    $password = $url["pass"];
  } else {
    if (file_exists('/tmp/mysql.sock')) {
      $dsn = 'mysql:dbname=ecms;unix_socket=/tmp/mysql.sock';
    } else {
      $dsn = 'mysql:dbname=ecms;host=localhost';
    }
    $user = 'root';
    $password = FALSE;
  }

  return array
  (
    'default' => array
    (
      'type'       => 'pdo',
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
        'dsn'        => $dsn,
        'username'   => $user,
        'password'   => $password,
        'persistent' => FALSE,
      ),
      'identifier'   => '`',
      'table_prefix' => '',
      'charset'      => 'utf8',
      'caching'      => FALSE,
      'profiling'    => TRUE,
    ),
  );
}
return database_config();
