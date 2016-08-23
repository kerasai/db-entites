<?php

/**
 * @file
 * Contains \kerasai\Db\Db.
 */

namespace kerasai\Db;

use PDO;

/**
 * PDO database wrapper.
 */
class Db {

  static $dbs = array();
  static $pdoOpts = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  );

  /** @var PDO $pdo */
  protected $pdo;
  protected $stmts = array();

  /**
   * Db constructor.
   *
   * @param array $config
   * @param PDO
   */
  protected function  __construct($config, $pdo) {
    $this->pdo = $pdo;
  }

  /**
   * Instantiates a Db object.
   *
   * @param string $name
   * @param array $config
   * @param PDO $pdo
   *
   * @return \kerasai\Db\Db
   *
   * @throws \Exception
   */
  public static function create($name, $config = array(), $pdo = NULL) {
    if (!isset(static::$dbs[$name])) {
      if (!$pdo) {
        if (empty($config)) {
          throw new \Exception(sprintf('Configuration options not set when instantiating Db "%s".', $name));
        } else {
          $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
          $pdo = new PDO($dsn, $config['user'], $config['password'], static::$pdoOpts);
        }
      }
      static::$dbs[$name] = new static($config, $pdo);
    }

    return static::$dbs[$name];
  }

  /**
   * Execute a query.
   *
   * @param string $query
   * @param array $params
   *
   * @return \PDOStatement
   */
  public function execute($query, $params = NULL) {
    $stmt = $this->prepare($query);
    $stmt->execute($params);
    return $stmt;
  }

  /**
   * Transform query into a prepared statement.
   *
   * @param string $query
   *
   * @return \PDOStatement
   */
  protected function prepare($query) {
    // Perform normalization of $query if needed.

    if (!isset($this->stmts[$query])) {
      $this->stmts[$query] = $this->pdo->prepare($query);
    }

    return $this->stmts[$query];
  }

  /**
   * Get a row of data.
   *
   * @param string $query
   * @param array $params
   *
   * @return array
   */
  public function getRow($query, $params = NULL) {
    $result = $this->execute($query, $params);
    return $result->fetch();
  }

  /**
   * Get all rows of data.
   *
   * @param string $query
   * @param array $params
   *
   * @return array
   */
  public function getRows($query, $params = NULL) {
    $result = $this->execute($query, $params);
    return $result->fetchAll();
  }

  /**
   * Get a column of data.
   *
   * @param string $query
   * @param array $params
   *
   * @return array
   */
  public function getCol($query, $params = NULL) {
    $result = $this->execute($query, $params);
    $col = array();
    while ($val = $result->fetchColumn()) {
      $col[] =$val;
    }
    return $col;
  }

  /**
   * Get the first field from the first result.
   *
   * @param string $query
   * @param array $params
   *
   * @return string
   */
  public function getField($query, $params = NULL) {
    $result = $this->execute($query, $params);
    return $result->fetchColumn();
  }

  /**
   * Get the ID of the last inserted record.
   *
   * @return string
   */
  public function lastId() {
    return $this->pdo->lastInsertId();
  }

}
