<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Validation;

use Cosman\Queue\Store\Validation\Constraint\Collection;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * Base validator class
 *
 * @author cosman
 *        
 */
abstract class BaseValidator
{

    /**
     *
     * @var RecursiveValidator
     */
    protected $validator;

    /**
     *
     * @var array
     */
    protected $errors = [];

    /**
     *
     * @param RecursiveValidator $validator
     */
    public function __construct(RecursiveValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Defines constraints for validator
     *
     * @param array $data
     *            Data being validated
     * @return Collection
     */
    abstract protected function getDefinedConstraints(array $data = []): Collection;

    /**
     * Validates a given array against a constraint
     *
     * @param array $model
     * @return bool
     */
    public function validate(array $model): bool
    {
        $violations = $this->validator->validate($model, $this->getDefinedConstraints($model));
        
        foreach ($violations as $violation) {
            
            $search = array(
                '[',
                ']'
            );
            
            $field = str_replace($search, '', $violation->getPropertyPath());
            
            if (! array_key_exists($field, $this->errors)) {
                $this->errors[$field] = $violation->getMessage();
            }
        }
        
        return 0 === count($this->errors);
    }

    /**
     * Returns validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns first validation message
     *
     * @return string|NULL
     */
    public function getFirstError(): ?string
    {
        $message = null;
        
        if (count($this->errors)) {
            $message = array_values($this->errors)[0];
        }
        
        return $message;
    }
}