<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Table;

use Cosman\Queue\Store\Table\Schema\Table;

/**
 * Base table class
 *
 * @author cosman
 *        
 */
class BaseTable
{

    /**
     * Returns table definition
     * 
     * @return \Cosman\Queue\Store\Table\Schema\Table
     */
    public static function definition()
    {
        return new Table(get_called_class());
    }
}