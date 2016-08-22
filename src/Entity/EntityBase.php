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

  const table = NULL;
  const idAttribute = NULL;

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

    if (!static::idAttribute) {
      throw new \Exception(sprintf('No idAttribute value specified on "%s".', static::class));
    }

    if (!static::table) {
      throw new \Exception(sprintf('No table value specified on "%s".', static::class));
    }
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
   * @param Db $db
   * @param int $id
   *
   * @return static
   *
   * @throws \Exception
   */
  public static function load($id, $db = NULL) {
    $idAttr = static::idAttribute;
    $table = static::table;

    if (self::idAttribute === NULL) {
      throw new \Exception(sprintf('idAttribute not set for "%s".', static::class));
    }
    $query = "SELECT * FROM {$table} WHERE {$idAttr} = ?";
    if ($data = $db->getRow($query, array($id))) {
      $entity = new static($db, $data);
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

}
