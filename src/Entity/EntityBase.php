<?php

/**
 * @file
 * Contains \kerasai\Db\Entity\EntityBase.
 */

namespace kerasai\Db\Entity;

use kerasai\Db\Db;

/**
 * Base implementation for Entities.
 */
abstract class EntityBase implements EntityInterface {

  /** @var Db */
  protected $db;

  /**
   * Entity constructor.
   *
   * @param array $data
   * @param Db $db
   *
   * @throws \Exception
   */
  public function __construct($data = array(), $db = NULL) {
    static::setAttrs($this, $data);
    $this->db = static::getDb($db);
  }

  /**
   * Utility function to access default database as needed.
   *
   * @param $db
   *
   * @return \kerasai\Db\Db
   */
  public static function getDb($db) {
    if (!$db) {
      $db = Db::create('default');
    }
    return $db;
  }

  /**
   * Sets attribute data onto the Entity.
   *
   * @param static $entity
   * @param array $data
   */
  protected static function setAttrs($entity, $data) {
    foreach ($data as $name => $value) {
      $entity->{$name} = $value;
    }
  }

  /**
   * Load the entity.
   *
   * @param mixed $id
   * @param Db $db
   *
   * @return static
   *
   * @throws \Exception
   */
  public static function load($id, $db = NULL) {
    if (!is_array($id)) {
      $id = array($id);
    }

    $table = static::table();

    $query[] = "SELECT * FROM {$table}";
    $keys = $params = array();
    foreach (static::keys() as $key) {
      $keys[] = $key . ' = ?';
      $params[] = array_shift($id);
    }
    $query[] = 'WHERE ' . implode(' AND ', $keys);

    $query = implode(' ', $query);
    if ($data = $db->getRow($query, $params)) {
      $entity = new static($data, $db);
      return $entity;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    // Override to perform validation.
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $this->validate();
    $this->store();
  }

  /**
   * Store the entity.
   *
   * Extending classes must implement this method and this is where inserting
   * or updating the database record will occur.
   */
  abstract protected function store();

  /**
   * Performs an insert query.
   */
  protected function insert() {
    $params = array();
    foreach (static::insertParams() as $param) {
      $params[$param] = $this->$param;
    }

    $cols = implode(',', array_keys($params));
    $placeholders = implode(' , ', array_fill(0, count($params), '?'));
    $table = static::table();
    $query = "INSERT INTO $table ($cols) VALUES ($placeholders)";
    $this->db->execute($query, array_values($params));
  }

  /**
   * Performs an update query.
   */
  protected function update() {
    $params = array();
    foreach (static::updateParams() as $param) {
      $params[$param] = $this->$param;
    }

    $table = static::table();
    $query[] = "UPDATE $table SET";

    $values = array();
    foreach ($params as $name => $value) {
      $values[] = $name . ' = ?';
    }
    $query[] = implode(',', $values);

    $query[] = 'WHERE';
    $keys = array();
    foreach ($this->keys() as $key) {
      $keys[] = $key . ' = ?';
      $params[] = $key;
    }
    $query[] = implode(' AND ', $keys);

    $query = implode(' ', $query);
    $this->db->execute($query, $params);
  }

  /**
   * Defines database table containing the data.
   *
   * @return string
   *
   * @throws \Exception
   */
  protected static function table() {
    throw new \Exception('No keys defined.');
  }

  /**
   * Defines the unique key for identifying the entity.
   *
   * @return array
   *
   * @throws \Exception
   */
  protected static function keys() {
    throw new \Exception('No keys defined.');
  }

  /**
   * Defines parameters used for the database insert.
   *
   * @return array
   *
   * @throws \Exception
   */
  protected static function insertParams() {
    throw new \Exception('No insertParams defined.');
  }

  /**
   * Defines parameters used for the database update.
   *
   * @return array
   *
   * @throws \Exception
   */
  protected static function updateParams() {
    throw new \Exception('No updateParams defined.');
  }

}
