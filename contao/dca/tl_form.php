<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Palettes
PaletteManipulator::create()
    ->addLegend('freshdesk_legend', 'store_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('freshdesk_enable', 'freshdesk_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_form')
;

$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'freshdesk_enable';
$GLOBALS['TL_DCA']['tl_form']['subpalettes']['freshdesk_enable'] = 'freshdesk_apiUrl,freshdesk_apiKey';

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
    'eval' => ['mandatory' => true, 'decodeEntities' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'maxlength' => 255, 'default' => ''],
];
