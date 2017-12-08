<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Validation;

use Cosman\Queue\Store\Validation\Constraint\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @author cosman
 *        
 */
class ProjectValidator extends BaseValidator
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
                'message' => 'Project name required.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Project name must be a string.'
            )),
            new Assert\Length(array(
                'max' => 50,
                'maxMessage' => 'Project name must not be longer than {{ limit }} characters.'
            ))
        );
        
        $options['description'] = array(
            new Assert\NotBlank(array(
                'message' => 'Project description required.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Project description must be a string.'
            )),
            new Assert\Length(array(
                'max' => 255,
                'maxMessage' => 'Project description must not be longer than {{ limit }} characters.'
            ))
        );
        
        return new Collection($options);
    }
}