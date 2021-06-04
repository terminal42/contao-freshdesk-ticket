<?php

declare(strict_types=1);

namespace Terminal42\FreshdeskTicketBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42FreshdeskTicketBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
