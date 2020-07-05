<?php


namespace App\Entity\Interfaces;

/**
 * This interface is used in the loading the Validation class and validating the entity
 *  keep in mind that upon adding this interface to the entity it IS required to build the validation logic
 *  if the validation logic is missing upon applying create/update interface- exception will be thrown
 *
 * Also keep in mind that one of the given interfaces must be applied to the entity, otherwise nothing will happen
 * @see ValidateEntityForUpdateInterface
 * @see ValidateEntityForCreateInterface
 *
 * Instead of applying validator directly on entity, validation is handled outside of it, because it's required
 *  sometimes to know stated of database and thus repository calling logic should be excluded from entity
 *
 * Interface ValidateEntityInterface
 * @package App\Entity\Interfaces
 */
interface ValidateEntityInterface{

}