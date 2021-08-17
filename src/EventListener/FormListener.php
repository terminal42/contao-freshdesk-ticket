<?php

namespace Terminal42\FreshdeskTicketBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpClient\HttpClient;

class FormListener
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * FormListener constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @Hook(value="processFormData")
     */
    public function onProcessFormData(array $submitted, array $form, array $files = null): void
    {
        if (!$form['freshdesk_enable'] || !$form['freshdesk_apiUrl'] || !$form['freshdesk_apiKey']) {
            return;
        }

        $formFields = $this->connection->fetchAllAssociative('SELECT type, name FROM tl_form_field WHERE pid=? AND freshdesk_send=?', [$form['id'], 1]);

        if (count($formFields) === 0) {
            return;
        }

        $data = [];

        // Generate the data
        foreach ($formFields as $formField) {
            $value = $submitted[$formField['name']] ?? null;
            if ($value) {
                if ($formField['type'] === 'hidden' && is_numeric($value)) {
                    $value = (int) $value;
                }

                $data[$formField['name']] = $value;
            }
        }

        if (count($data) === 0) {
            return;
        }

        $data = array_merge(
            [
                'priority' => 1,
                'status' => 2,
            ],
            $data
        );

        $response = HttpClient::createForBaseUri(rtrim($form['freshdesk_apiUrl'], '/') . '/api/v2/')->request('POST', 'tickets', [
            'auth_basic' => [$form['freshdesk_apiKey'], 'X'],
            'json' => $data,
        ]);

        if ($response->getStatusCode() !== 201) {
            throw new \RuntimeException(sprintf('Freshdesk ticket creation failed with status code: %s', $response->getStatusCode()));
        }
    }
}
