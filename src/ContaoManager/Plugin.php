<?php

declare(strict_types=1);

namespace Terminal42\FreshdeskTicketBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Terminal42\FreshdeskTicketBundle\Terminal42FreshdeskTicketBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            (new BundleConfig(Terminal42FreshdeskTicketBundle::class))->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
