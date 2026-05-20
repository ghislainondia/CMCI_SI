<?php

namespace ChurchCRM\model\ChurchCRM;

use ChurchCRM\model\ChurchCRM\Base\PersonQuery as BasePersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Skeleton subclass for performing query and update operations on the 'person_per' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class PersonQuery extends BasePersonQuery
{
  /**
   * Filter by disciple maker (per_DiscipleMakerID).
   * Custom until ORM models are regenerated from schema.xml.
   *
   * @param int|int[]|null $discipleMakerId
   */
  public function filterByDiscipleMakerId($discipleMakerId = null, ?string $comparison = null): self
  {
    $comparison = $comparison ?? Criteria::EQUAL;

    if ($discipleMakerId === null) {
      return $this->addUsingAlias('per_DiscipleMakerID', null, Criteria::ISNULL);
    }

    return $this->addUsingAlias('per_DiscipleMakerID', $discipleMakerId, $comparison);
  }
}
