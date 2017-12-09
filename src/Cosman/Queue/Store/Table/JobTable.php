<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Table;

/**
 * Job table
 *
 * @author cosman
 *        
 */
class JobTable extends BaseTable
{

    const NAME = 'jobs';

    const FIELD_ID = 'jobs.id';
    
    const FIELD_CODE = 'jobs.code';

    const FIELD_TITLE = 'jobs.title';

    const FIELD_DESCRIPTION = 'jobs.description';

    const FIELD_PROJECT_ID = 'jobs.project_id';
    
    const FIELD_HEADERS = 'jobs.headers';
    
    const FIELD_PAYLOAD = 'jobs.payload';

    const FIELD_DELAY = 'jobs.delay';
    
    const FIELD_RETRY_DELAY = 'jobs.retry_delay';

    const FIELD_RETRIES = 'jobs.retries';

    const FIELD_RETRY_COUNTS = 'jobs.retry_counts';

    const FIELD_CALLBACK_URL = 'jobs.callback_url';
    
    const FIELD_REQUEST_METHOD = 'jobs.request_method';

    const FIELD_IS_EXECUTED = 'jobs.is_executed';
    
    const FIELD_IS_SUCCESSFUL = 'jobs.is_successful';
    
    const FIELD_IS_PROCESSING = 'jobs.is_processing';
    
    const FIELD_NEXT_EXECUTION = 'jobs.next_execution';

    const FIELD_CREATED_AT = 'jobs.created_at';

    const FIELD_UPDATED = 'jobs.updated_at';
}