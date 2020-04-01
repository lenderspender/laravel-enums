<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Commands;

use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Composer\Autoload\ClassMapGenerator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LenderSpender\LaravelEnums\CanBeUnknown;
use LenderSpender\LaravelEnums\DocBlock;
use ReflectionClass;

class AddEnumDocBlockToModels extends Command
{
    protected $signature = 'ide-helper:generate:model-enums';

    protected $description = 'Generate Enum DocBlocks for Models';

    private array $modelLocations;
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->modelLocations = config('laravel-enums.model_locations', []);
        $this->filesystem = $filesystem;
    }

    public function handle(): void
    {
        $models = collect($this->modelLocations)
            ->mapWithKeys(fn ($location) => ClassMapGenerator::createMap(base_path($location)))
            ->map(function (string $filePath, string $className) {
                return $this->enumerate($className, $filePath);
            })
            ->filter();

        $this->info("Added enum information to {$models->count()} models");
    }

    public function enumerate(string $className, string $filePath): bool
    {
        $reflection = new ReflectionClass($className);

        if (! $reflection->isSubclassOf(Model::class)) {
            return false;
        }

        $contents = $this->filesystem->get($filePath);
        $docBlock = $this->generateDocBlocks($reflection);

        if (! $docBlock) {
            return false;
        }

        $this->filesystem->put(
            $filePath,
            $this->createNewContents($reflection, $contents, $docBlock)
        );

        return true;
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

        foreach ($phpdoc->getTagsByName('property') as $tag) {
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
