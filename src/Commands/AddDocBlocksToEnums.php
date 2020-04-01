<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Commands;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LenderSpender\LaravelEnums\CanBeUnknown;
use LenderSpender\LaravelEnums\Enum;
use ReflectionClass;

class AddDocBlocksToEnums extends Command
{
    protected $signature = 'ide-helper:generate:enums';

    protected $description = 'Generate DocBlocks for Enums';

    private array $enumLocations;

    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->enumLocations = config('laravel-enums.enum_locations', []);
        $this->filesystem = $filesystem;
    }

    public function handle(): void
    {
        $enums = collect($this->enumLocations)
            ->mapWithKeys(fn ($location) => ClassMapGenerator::createMap(base_path($location)))
            ->map(function (string $filePath, string $className) {
                $reflection = new ReflectionClass($className);

                if (! $reflection->isSubclassOf(Enum::class)) {
                    return null;
                }

                $contents = $this->filesystem->get($filePath);
                $docBlocks = $this->generateDocBlocks($reflection);

                if ($docBlocks) {
                    $this->filesystem->put(
                        $filePath,
                        $this->createNewContents($reflection, $contents, $docBlocks)
                    );

                    return true;
                }

                return null;
            })
            ->filter();

        $this->info("Parsed {$enums->count()} enums");
    }

    private function generateDocBlocks(ReflectionClass $reflectionClass): ?string
    {
        $constants = $reflectionClass->getConstants();

        if (count($constants) === 0) {
            return null;
        }

        if (in_array(CanBeUnknown::class, $reflectionClass->getInterfaceNames())) {
            $constants['UNKNOWN'] = 'UNKNOWN';
        }

        $methods = [];
        foreach ($constants as $constant => $value) {
            $methods[] = ' * @method static self ' . $constant . '()';
        }

        return '/**' . PHP_EOL . implode(PHP_EOL, $methods) . PHP_EOL . ' */';
    }

    private function createNewContents(ReflectionClass $reflectionClass, string $contents, string $docBlocks): string
    {
        $docComment = $reflectionClass->getDocComment();

        if ($docComment) {
            $contents = str_replace(PHP_EOL . $docComment, '', $contents);
        }

        return Str::replaceFirst('class', $docBlocks . PHP_EOL . 'class', $contents);
    }
}
