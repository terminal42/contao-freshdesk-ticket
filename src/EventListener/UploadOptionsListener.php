<?php

namespace Terminal42\FreshdeskTicketBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @Callback(table="tl_form", target="fields.freshdesk_uploads.options")
 */
class UploadOptionsListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(DataContainer $dc): array
    {
        $options = [];
        $supportedFormFieldTypes = ['upload', 'fineUploader'];
        $records = $this->connection->fetchAllAssociative(
            'SELECT type, name, label FROM tl_form_field WHERE pid=? AND type IN (?) ORDER BY sorting',
            [$dc->id, $supportedFormFieldTypes],
            [ParameterType::INTEGER, Connection::PARAM_STR_ARRAY]
        );

        foreach ($records as $record) {
            $options[$record['name']] = sprintf('%s <span class="tl_gray">[%s]</span>', $record['label'], $record['name']);
        }

        return $options;
    }
}
