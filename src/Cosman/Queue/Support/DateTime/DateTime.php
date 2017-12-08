<?php
declare(strict_types = 1);
namespace Cosman\Queue\Support\DateTime;

/**
 *
 * @author cosman
 *        
 */
class DateTime extends \DateTime implements \JsonSerializable
{

    /**
     *
     * {@inheritdoc}
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->format(static::W3C);
    }

    /**
     *
     * @param string $datetime
     * @return self|NULL
     */
    public static function createDateTime(?string $datetime): ?self
    {
        try {
            return new static($datetime);
        } catch (\Exception $e) {
            return null;
        }
    }
}
