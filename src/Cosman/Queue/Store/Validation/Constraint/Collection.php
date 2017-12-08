<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
;

/**
 *
 * @author cosman
 *        
 */
class Collection extends Constraint
{

    public $constraints = [];

    /**
     *
     * @param array $constraints
     */
    public function __construct(array $constraints = [])
    {
        $this->constraints = $constraints;
    }
}