<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Palettes
PaletteManipulator::create()
    ->addLegend('freshdesk_legend', 'store_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('freshdesk_enable', 'freshdesk_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_form');

$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'freshdesk_enable';
$GLOBALS['TL_DCA']['tl_form']['subpalettes']['freshdesk_enable'] = 'freshdesk_apiUrl,freshdesk_apiKey,freshdesk_mapper,freshdesk_uploads';

// Fields
$GLOBALS['TL_DCA']['tl_form']['fields']['freshdesk_enable'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => ['type' => 'boolean', 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_form']['fields']['freshdesk_apiUrl'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'decodeEntities' => true, 'rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_form']['fields']['freshdesk_apiKey'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_form']['fields']['freshdesk_mapper'] = [
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'eval' => [
        'tl_class' => 'clr',
        'columnFields' => [
            'freshdesk_mapperKey' => [
                'label' => &$GLOBALS['TL_LANG']['tl_form']['freshdesk_mapperKey'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => ['style' => 'width:180px'],
            ],
            'freshdesk_mapperValue' => [
                'label' => &$GLOBALS['TL_LANG']['tl_form']['freshdesk_mapperValue'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => ['decodeEntities' => true, 'style' => 'width:360px'],
            ],
            'freshdesk_mapperType' => [
                'label' => &$GLOBALS['TL_LANG']['tl_form']['freshdesk_mapperType'],
                'exclude' => true,
                'inputType' => 'select',
                'options' => ['string', 'integer'],
                'reference' => &$GLOBALS['TL_LANG']['tl_form']['freshdesk_mapperTypeRef'],
                'eval' => ['style' => 'width:180px'],
            ],
        ],
    ],
    'sql' => ['type' => 'blob', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_form']['fields']['freshdesk_uploads'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => [\Terminal42\FreshdeskTicketBundle\EventListener\FormListener::class, 'onUploadsOptionsCallback'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];
