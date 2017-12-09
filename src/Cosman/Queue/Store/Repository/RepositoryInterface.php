<?php
declare(strict_types=1);
namespace Cosman\Queue\Store\Repository;

use Illuminate\Database\Connection;

interface RepositoryInterface {
    
    /**
     * 
     * @return Connection
     */
    public function getConnection(): Connection;
}