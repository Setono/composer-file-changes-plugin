<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Configuration;

interface ConfigurationResolverInterface
{
    public function resolve(): Configuration;
}
