<?php

class Database
{
  private static ?PDO $instance = null;

  private function __construct() {}
  private function __clone() {}

  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      $pdo = require __DIR__ . '/EnvLoader.php';

      self::$instance = $pdo;
    }

    return self::$instance;
  }

  public static function prepare(string $query): PDOStatement
  {
    return self::getInstance()->prepare($query);
  }
}
