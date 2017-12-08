<?php
declare(strict_types = 1);
namespace Cosman\Queue\Http\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 * @author cosman
 *        
 */
class Response extends JsonResponse
{

    /**
     * Creates a formatted response
     * 
     * @param mixed $data
     * @param int $httpCode
     * @param string $message
     */
    protected function createResponse($data = null, int $httpCode = self::HTTP_OK, string $message = null)
    {
        if (! $message) {
            $message = static::$statusTexts[$httpCode];
        }
        
        return new static(array(
            'code' => $httpCode,
            'message' => $message,
            'payload' => $data
        ));
    }

    /**
     * Sends a response
     *
     * @param mixed $data
     * @param int $httpCode
     * @param string $message
     * @return self
     */
    public function respond($data = null, int $httpCode = self::HTTP_OK, string $message = null): self
    {
        return $this->createResponse($data, $httpCode, $message);
    }

    /**
     * Converts a collection to a response
     *
     * @param iterable $collection
     * @param int $total
     * @param number $offset
     * @param int $httpCode
     * @param string $message
     * @return self
     */
    public function collection(iterable $collection = [], int $total = 0, $offset = 0, int $httpCode = self::HTTP_OK, string $message = null): self
    {
        $data = array(
            'collection' => $collection,
            'recordCounts' => $total,
            'hasMore' => $total > ($offset + count($collection))
        );
        
        return $this->createResponse($data, $httpCode, $message);
    }

    /**
     * Converts errors to a response
     *
     * @param mixed $errors
     * @param int $httpCode
     * @param string $message
     * @return self
     */
    public function error($errors, int $httpCode = self::HTTP_INTERNAL_SERVER_ERROR, string $message = null): self
    {
        return $this->createResponse($errors, $httpCode, $message);
    }

    /**
     * Converts an exception to a response
     *
     * @param \Exception $exception
     * @param int $code
     * @return self
     */
    public function exception(\Exception $exception, int $code = self::HTTP_INTERNAL_SERVER_ERROR): self
    {
        return $this->createResponse(null, $code, $exception->getMessage());
    }
}