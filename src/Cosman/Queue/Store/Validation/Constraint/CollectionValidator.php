<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 *
 * @author cosman
 *        
 */
class CollectionValidator extends ConstraintValidator
{

    /**
     *
     * {@inheritdoc}
     * @see \Symfony\Component\Validator\Constraints\CollectionValidator::validate()
     */
    public function validate($value, Constraint $constraint, $pathPrefix = '')
    {
        if (! $constraint instanceof Collection) {
            throw new UnexpectedTypeException($constraint, Collection::class);
        }
        
        if (null === $value) {
            return;
        }
        
        if (! is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }
        
        $context = $this->context;
        
        foreach ($constraint->constraints as $field => $fieldConstraint) {
            
            $fullPath = $pathPrefix . $field;
            
            if (! array_key_exists($field, $value)) {
                $value[$field] = [];
            }
            
            if ($fieldConstraint instanceof Collection) {
                $this->validate($value[$field], $fieldConstraint, $fullPath . '.');
            } else {
                if (! is_array($fieldConstraint)) {
                    $fieldConstraint = [
                        $fieldConstraint
                    ];
                }
                
                foreach ($fieldConstraint as $oneConstraint) {
                    if (! $this->hasErrorForField($fullPath)) {
                        $context->getValidator()
                            ->inContext($context)
                            ->atPath($fullPath)
                            ->validate($value[$field], $oneConstraint);
                    }
                }
            }
        }
    }

    protected function hasErrorForField(string $field): bool
    {
        foreach ($this->context->getViolations() as $violation) {
            
            if ($violation->getPropertyPath() == $field) {
                return true;
            }
        }
        
        return false;
    }
}