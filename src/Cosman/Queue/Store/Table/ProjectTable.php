<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Table;

/**
 *
 * @author cosman
 *        
 */
class ProjectTable extends BaseTable
{

    const NAME = 'projects';

    const FIELD_ID = 'projects.id';

    const FIELD_CLIENT_ID = 'projects.client_id';

    const FIELD_CODE = 'projects.code';

    const FIELD_NAME = 'projects.name';
    
    const FIELD_DESCRIPTION = 'projects.description';

    const FIELD_CREATED_AT = 'projects.created_at';

    const FIELD_UPDATED_AT = 'projects.updated_at';
}