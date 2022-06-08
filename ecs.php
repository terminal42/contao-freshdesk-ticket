<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([__DIR__.'/tools/ecs/vendor/contao/easy-coding-standard/config/contao.php']);
    $ecsConfig->parallel();

    $ecsConfig->skip([
        '/contao/*',
        HeaderCommentFixer::class => null,
        MethodChainingIndentationFixer::class => [
            '*/DependencyInjection/Configuration.php',
        ],
    ]);

    $parameters = $ecsConfig->parameters();
    $parameters->set(Option::CACHE_DIRECTORY, sys_get_temp_dir().'/ecs_cache');
};
