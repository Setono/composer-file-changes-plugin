<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges;

use Composer\Factory;
use Webmozart\Assert\Assert;

final class ComposerData
{
    private array $data;

    public function __construct(string $composerFile = null)
    {
        $composerFile = $composerFile ?? Factory::getComposerFile();

        /** @var mixed $data */
        $data = json_decode(file_get_contents($composerFile), true, 512, \JSON_THROW_ON_ERROR);
        Assert::isArray($data);

        $this->data = $data;
    }

    public function getExtra(): array
    {
        $extra = $this->data['extra'] ?? [];
        Assert::isArray($extra);

        return $extra;
    }
}
