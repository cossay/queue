<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Validation;

use Symfony\Component\Validator\Constraints as Assert;
use Cosman\Queue\Store\Validation\Constraint\Collection;
use Cosman\Queue\Store\Validation\Constraint\ClientUniqueEmail;

/**
 * Client validator class
 *
 * @author cosman
 *        
 */
class ClientValidator extends BaseValidator
{

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Validation\BaseValidator::getDefinedConstraints()
     */
    protected function getDefinedConstraints(array $data = []): Collection
    {
        $options = [];
        
        $options['name'] = array(
            new Assert\NotBlank(array(
                'message' => 'Account name must not be blank.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Account name must be a string.'
            )),
            new Assert\Length(array(
                'max' => 150,
                'maxMessage' => 'Account name must not contain more than {{ limit }} characters.'
            ))
        );
        
        $options['email'] = array(
            new Assert\NotBlank(array(
                'message' => 'Account email must not be blank.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Account email must be a string.'
            )),
            new Assert\Email(array(
                'strict' => true,
                'message' => 'Account email address must be a valid email address.'
            )),
            new ClientUniqueEmail(array(
                'message' => 'Provided client email address already in use by another client.',
                'clientId' => $data['clientId'] ?? null
            ))
        );
        
        $options['password'] = array(
            new Assert\NotBlank(array(
                'message' => 'Account password must not be blank.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Account password must be a string.'
            ))
        );
        
        return new Collection($options);
    }
}