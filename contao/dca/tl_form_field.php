<?php

// Config
$GLOBALS['TL_DCA']['tl_form_field']['config']['onload_callback'][] = [\Terminal42\FreshdeskTicketBundle\EventListener\FormFieldListener::class, 'onLoadCallback'];

// Fields
$GLOBALS['TL_DCA']['tl_form_field']['fields']['freshdesk_send'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => ['type' => 'boolean', 'default' => 0],
];
