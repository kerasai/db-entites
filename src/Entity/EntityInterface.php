<?php

/**
 * @file
 * Contains \kerasai\Db\EntityInterface.
 */

namespace kerasai\Db\Entity;

use kerasai\Db\Db;

interface EntityInterface {

  /**
   * Entity constructor.
   *
   * @param array $data
   * @param Db $db
   */
  public function __construct($data = array(), $db = NULL);

  /**
   * Load the entity.
   *
   * @param int $id
   * @param Db $db
   *
   * @return EntityInterface
   */
  public static function load($id, $db = NULL);

  /**
   * Perform validation on the Entity.
   */
  public function validate();

  /**
   * Save the entity.
   */
  public function save();

}
