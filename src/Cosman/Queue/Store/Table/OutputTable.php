<?php
declare(strict_types = 1);
namespace Cosman\Queue\Store\Table;

/**
 * Output table
 *
 * @author cosman
 *        
 */
class OutputTable extends BaseTable
{

    const NAME = 'outputs';

    const FIELD_ID = 'outputs.id';

    const FIELD_CODE = 'outputs.code';

    const FIELD_JOB_ID = 'outputs.job_id';

    const FIELD_CONTENT = 'outputs.content';

    const FIELD_HEADERS = 'outputs.headers';

    const FIELD_STATUS_CODE = 'outputs.status_code';

    const FIELD_STATUS_MESSAGE = 'outputs.status_message';

    const FIELD_CREATED_AT = 'outputs.created_at';

    const FIELD_UPDATED_AT = 'outputs.updated_at';
}