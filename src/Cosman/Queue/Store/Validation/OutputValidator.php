<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Validation;

use Cosman\Queue\Store\Validation\Constraint\Collection;

/**
 *
 * @author cosman
 *        
 */
class OutputValidator extends BaseValidator
{

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Validation\BaseValidator::getDefinedConstraints()
     */
    protected function getDefinedConstraints(array $data = []): Collection
    {}
}