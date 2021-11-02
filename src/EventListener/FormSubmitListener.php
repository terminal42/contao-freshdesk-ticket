<?php

namespace Terminal42\FreshdeskTicketBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\Part\DataPart;
use Terminal42\FreshdeskTicketBundle\Mime\FormDataPart;

/**
 * @Hook(value="processFormData")
 */
class FormSubmitListener
{
    public function __invoke(array $submitted, array $form, array $files = null): void
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
                    try {
                        $uploads[] = DataPart::fromPath($files[$uploadFormField]['tmp_name'], $files[$uploadFormField]['name']);
                    } catch (InvalidArgumentException $e) {
                        continue;
                    }
                }
            }
        }

        $requestData = [
            'auth_basic' => [$form['freshdesk_apiKey'], 'X'],
        ];

        // Send the multipart/form-data or JSON depending on whether there are file uploads, or not
        if (count($uploads) > 0) {
            // Convert all integers to strings as form data can only be string values
            foreach ($data as $k => $v) {
                if (is_int($v)) {
                    $data[$k] = (string) $v;
                }
            }

            $data['attachments'] = $uploads;

            // Use our custom FormDataPart as Freshdesk does not accept "attachments[0]", "attachments[1]" but only multiple "attachments[]"
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
