<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 *
 * @author cosman
 *        
 */
class ClientUniqueEmail extends Constraint
{

    public $message = 'Provided client email address is already taken.';
    
    public $clientId = null;

    /**
     *
     * {@inheritdoc}
     * @see \Symfony\Component\Validator\Constraint::validatedBy()
     */
    public function validatedBy()
    {
        return 'cosman.queue.validator.constraint.client.email.unique';
    }
}