<?php

declare(strict_types=1);

namespace Koriym\AppStateDiagram;

use stdClass;

use function dirname;
use function file_put_contents;
use function is_array;
use function is_dir;
use function json_encode;
use function ksort;
use function mkdir;
use function preg_replace;
use function sprintf;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

final class Dumper
{
    /**
     * @param array<string, AbstractDescriptor> $descriptors
     */
    public function __invoke(array $descriptors, string $alpsFile, string $schema): void
    {
        ksort($descriptors);
        foreach ($descriptors as $descriptor) {
            $this->dumpSemantic($descriptor, dirname($alpsFile), $schema);
        }
    }

    private function dumpSemantic(AbstractDescriptor $descriptor, string $dir, string $schema)
    {
        $writeDir = $this->mkDir($dir);
        $type = $descriptor->type ?? 'semantic';
        $normarlizedDescriptor = $descriptor->normalize($schema);
        $this->save($writeDir, $type, $descriptor->id, $normarlizedDescriptor);
    }

    private function save(string $dir, string $type, string $id, stdClass $class): void
    {
        $file = sprintf('%s/%s.%s.json', $dir, $type, $id);
        $jsonTabSpace4 = json_encode($class, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $json =  $this->convertTabSpaceTwo($jsonTabSpace4);
        file_put_contents($file, $json);
    }

    private function mkDir(string $dir): string
    {
        $dir = sprintf('%s/descriptor', $dir);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    private function convertTabSpaceTwo(string $json): string
    {
        return preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1', $json);
    }
}
