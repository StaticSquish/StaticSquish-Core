<?php


namespace filedatabrowserstaticgenerator\models;

/**
 *  @license 3-clause BSD
 */
class FieldListValue extends BaseField {

  protected $values = array();

  /**
   * Get the value of Values
   *
   * @return mixed
   */
  public function getValues()
  {
      return $this->values;
  }

  public function addValue(FieldValue $fieldValue) {
    $this->values[] = $fieldValue;
  }

}
