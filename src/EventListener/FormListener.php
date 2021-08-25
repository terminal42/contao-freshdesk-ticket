<?php

namespace Terminal42\FreshdeskTicketBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class FormListener
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param Connection $connection
     * @param string $projectDir
     */
    public function __construct(Connection $connection, string $projectDir)
    {
        $this->connection = $connection;
        $this->projectDir = $projectDir;
    }

    /**
     * On uploads options callback.
     */
    public function onUploadsOptionsCallback(DataContainer $dc): array
    {
        $options = [];
        $supportedFormFieldTypes = ['upload', 'fineUploader'];
        $records = $this->connection->fetchAllAssociative(
            'SELECT type, name, label FROM tl_form_field WHERE pid=? AND type IN (?) ORDER BY sorting',
            [$dc->id, $supportedFormFieldTypes],
            [ParameterType::INTEGER, Connection::PARAM_STR_ARRAY]
        );

        foreach ($records as $record) {
            $options[$record['name']] = sprintf('%s [%s]', $record['label'], $record['name']);
        }

        return $options;
    }

    /**
     * @Hook(value="processFormData")
     */
    public function onProcessFormData(array $submitted, array $form, array $files = null): void
    {
        if (!$form['freshdesk_enable'] || !$form['freshdesk_apiUrl'] || !$form['freshdesk_apiKey']) {
            return;
        }

        $data = [];

        // Generate the data based on mapper
        foreach ((array) StringUtil::deserialize($form['freshdesk_mapper']) as $item) {
            if ($item['freshdesk_mapperKey'] === '' || $item['freshdesk_mapperValue'] === '') {
                continue;
            }

            $value = StringUtil::parseSimpleTokens($item['freshdesk_mapperValue'], $submitted);

            // Convert the data type
            switch ($item['freshdesk_mapperType']) {
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'string':
                    $value = (string) $value;
                    break;
                default:
                    throw new \RuntimeException(sprintf('Unsupported data type: %s', $item['freshdesk_mapperValue']));
            }

            $data[$item['freshdesk_mapperKey']] = $value;
        }

        if (count($data) === 0) {
            return;
        }

        // Add the default priority and status, if none set
        $data['priority'] = $data['priority'] ?? 1;
        $data['status'] = $data['status'] ?? 2;

        $uploads = [];

        // Generate the form file uploads
        if (is_array($uploadFormFields = StringUtil::deserialize($form['freshdesk_uploads']))) {
            foreach ($uploadFormFields as $uploadFormField) {
                if (isset($files[$uploadFormField])) {
                    $uploads[] = DataPart::fromPath($this->projectDir . '/' . $files[$uploadFormField]['name']);
                }
            }
        }

        $requestData = [
            'auth_basic' => [$form['freshdesk_apiKey'], 'X'],
        ];

        // Send the multipart/form-data or JSON depending on whether there are file uploads, or not
        if (count($uploads) > 0) {
            $formData = new FormDataPart($data);
            $requestData['headers'] = $formData->getPreparedHeaders()->toArray();
            $requestData['body'] = $formData->bodyToIterable();
        } else {
            $requestData['json'] = $data;
        }

        $response = HttpClient::createForBaseUri(rtrim($form['freshdesk_apiUrl'], '/') . '/api/v2/')->request('POST', 'tickets', $requestData);

        if ($response->getStatusCode() !== 201) {
            throw new \RuntimeException(sprintf('Freshdesk ticket creation failed with status code: %s', $response->getStatusCode()));
        }
    }
}
