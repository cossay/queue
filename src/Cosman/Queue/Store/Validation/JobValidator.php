<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Validation;

use Cosman\Queue\Store\Validation\Constraint\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Cosman\Queue\Store\Model\Job;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @author cosman
 *        
 */
class JobValidator extends BaseValidator
{

    /**
     *
     * {@inheritdoc}
     * @see \Cosman\Queue\Store\Validation\BaseValidator::getDefinedConstraints()
     */
    protected function getDefinedConstraints(array $data = []): Collection
    {
        $options = [];
        
        $options['title'] = array(
            new Assert\NotBlank(array(
                'message' => 'Job title required.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Job title must be a string.'
            )),
            new Assert\Length(array(
                'max' => 255,
                'maxMessage' => 'Job title must not be longer than {{ limit }} characters.'
            ))
        );
        
        $options['description'] = array(
            new Assert\NotBlank(array(
                'message' => 'Job description required.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Job description must be a string.'
            )),
            new Assert\Length(array(
                'max' => 500,
                'maxMessage' => 'Job description must not be longer than {{ limit }} characters.'
            ))
        );
        
        /*
         * $options['payload'] = array(
         * new Assert\NotBlank(array(
         * 'message' => 'Job payload required.'
         * ))
         * );
         */
        
        $options['delay'] = array(
            new Assert\NotBlank(array(
                'message' => 'Job delay required.'
            )),
            new Assert\Type(array(
                'type' => 'integer',
                'message' => 'Job delay must be an integer.'
            )),
            new Assert\GreaterThanOrEqual(array(
                'value' => 0,
                'message' => 'Job delay must be greather or equal to {{ compared_value }}.'
            ))
        );
        
        $options['retry_delay'] = array(
            new Assert\NotBlank(array(
                'message' => 'Job retry delay required.'
            )),
            new Assert\Type(array(
                'type' => 'integer',
                'message' => 'Job retry delay must be an integer.'
            )),
            new Assert\GreaterThanOrEqual(array(
                'value' => 0,
                'message' => 'Job retry delay must be greather or equal to {{ compared_value }}.'
            ))
        );
        
        $options['retries'] = array(
            new Assert\NotBlank(array(
                'message' => 'Job retry required.'
            )),
            new Assert\Type(array(
                'type' => 'integer',
                'message' => 'Job retry must be an integer.'
            )),
            new Assert\GreaterThanOrEqual(array(
                'value' => Job::MIN_JOB_RETRIES,
                'message' => 'Job retry must be greather or equal to {{ compared_value }}.'
            )),
            new Assert\LessThanOrEqual(array(
                'value' => Job::MAX_JOB_RETRIES,
                'message' => 'Job retry must be less than or equal to {{ compared_value }}.'
            ))
        );
        
        $options['callback_url'] = array(
            new Assert\NotBlank(array(
                'message' => 'Job callback URL required.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Job callback URL must be a string.'
            )),
            new Assert\Url(array(
                'message' => 'Provided job callback URL is not a valid URL.'
            ))
        );
        
        $options['request_method'] = array(
            new Assert\NotBlank(array(
                'message' => 'Job request method required.'
            )),
            new Assert\Type(array(
                'type' => 'string',
                'message' => 'Job request method must be a string.'
            )),
            new Assert\Choice(array(
                'choices' => array(
                    Request::METHOD_GET,
                    Request::METHOD_POST,
                    Request::METHOD_PUT,
                    Request::METHOD_DELETE
                    // Request::METHOD_PATCH,
                    // Request::METHOD_PURGE,
                    // Request::METHOD_CONNECT,
                    // Request::METHOD_HEAD,
                    // Request::METHOD_OPTIONS,
                    // Request::METHOD_TRACE
                ),
                'message' => 'Provided job request method is not currently supported.'
            ))
        );
        
        $options['headers'] = array(
            new Assert\Type(array(
                'type' => 'array',
                'message' => 'Job headers must a map or an associative array.'
            ))
        );
        
        return new Collection($options);
    }
}