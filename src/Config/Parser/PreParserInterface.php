<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Config\Parser;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface PreParserInterface extends ParserInterface
{
    /**
     * @param \SplFileInfo     $file
     * @param ContainerBuilder $container
     *
     * @return mixed
     */
    public static function preParse(\SplFileInfo $file, ContainerBuilder $container);
}
