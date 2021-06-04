<?php

namespace Terminal42\FreshdeskTicketBundle\EventListener;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\Connection;

class FormFieldListener
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * FormFieldListener constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * On data container load callback.
     */
    public function onLoadCallback(DataContainer $dc): void
    {
        // Determine whether Freshdesk is enabled
        switch (Input::get('act')) {
            case 'edit':
                $enabled = $this->connection->fetchOne('SELECT freshdesk_enable FROM tl_form WHERE id=(SELECT pid FROM tl_form_field WHERE id=?)', [$dc->id]);
                break;
            case 'editAll':
            case 'overrideAll':
                $enabled = $this->connection->fetchOne('SELECT freshdesk_enable FROM tl_form WHERE id=?', [$dc->id]);
                break;
            default:
                return;
        }

        // Update palettes if Freshdesk is enabled
        if ($enabled) {
            $paletteManipulator = PaletteManipulator::create()->addField('freshdesk_send', 'type', PaletteManipulator::POSITION_AFTER);

            foreach ($GLOBALS['TL_DCA'][$dc->table]['palettes'] as $name => $fields) {
                if (in_array($name, ['__selector__', 'submit', 'default', 'headline', 'explanation'])) {
                    continue;
                }

                $paletteManipulator->applyToPalette($name, $dc->table);
            }
        }
    }
}
