<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Table;

/**
 * client table class
 *
 * @author cosman
 *        
 */
class ClientTable extends BaseTable
{

    const NAME = 'clients';

    const FIELD_ID = 'clients.id';

    const FIELD_NAME = 'clients.name';

    const FIELD_TOKEN = 'clients.token';

    const FIELD_EMAIL = 'clients.email';
    
    const FIELD_BLOCKED = 'clients.is_blocked';

    const FIELD_CREATED_AT = 'clients.created_at';

    const FIELD_UPDATED_AT = 'clients.updated_at';
}