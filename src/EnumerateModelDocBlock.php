<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums;

use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use ReflectionClass;

class EnumerateModelDocBlock
{
    /**
     * @var \Illuminate\Filesystem\FilesystemManager
     */
    private $filesystemManager;

    public function __construct(FilesystemManager $filesystemManager)
    {
        $this->filesystemManager = $filesystemManager;
    }

    public function enumerate(string $directory, string $namespace): void
    {
        $filesystem = $this->filesystemManager->createLocalDriver(['root' => $directory]);
        $classes = collect($filesystem->files(null, true))
            ->filter(function (string $filename) {
                return Str::endsWith($filename, '.php');
            })->mapWithKeys(function (string $filename) use ($namespace) {
                $className = $namespace . '\\' . str_replace('.php', '', $filename);

                return [
                    $filename => class_exists($className) ? $className : null,
                ];
            })
            ->filter(function ($key) {
                return ! is_null($key);
            });

        $classes->each(function (string $className, string $filename) use ($filesystem) {
            $reflection = new ReflectionClass($className);

            $contents = $filesystem->get($filename);
            $docBlock = $this->generateDocBlocks($reflection);

            if ($docBlock) {
                $filesystem->put(
                    $filename,
                    $this->createNewContents($reflection, $contents, $docBlock)
                );
            }
        });
    }

    private function createNewContents(ReflectionClass $reflectionClass, string $contents, DocBlock $docBlock): string
    {
        $docComment = $reflectionClass->getDocComment();

        if ($docComment) {
            $contents = str_replace(PHP_EOL . $docComment, '', $contents);
        }

        return Str::replaceFirst(
            'class',
            (new DocBlockSerializer())->getDocComment($docBlock) . PHP_EOL . 'class',
            $contents
        );
    }

    private function generateDocBlocks(ReflectionClass $reflectionClass): ?DocBlock
    {
        if (! $reflectionClass->hasProperty('enums')) {
            return null;
        }

        $enumProperty = $reflectionClass->getProperty('enums');
        $enumProperty->setAccessible(true);

        $class = $reflectionClass->newInstanceWithoutConstructor();
        $enums = $enumProperty->getValue($class);
        ksort($enums);

        $phpdoc = new DocBlock($reflectionClass, new Context($reflectionClass->getNamespaceName()));

        foreach($phpdoc->getTagsByName('property') as $tag) {
            if (strpos($tag->getContent(), 'Enum') !== false) {
                $phpdoc->deleteTag($tag);
            }
        }

        foreach ($enums as $attribute => $class) {
            $tagLine = trim("@property \\{$class} \${$attribute}");

            if (in_array(CanBeUnknown::class, class_implements($class), true)) {
                $tagLine = trim("@property \\{$class}|null \${$attribute}");
            }
            $tag = Tag::createInstance($tagLine, $phpdoc);
            $phpdoc->prependTag($tag);
        }

        return $phpdoc;
    }
}
