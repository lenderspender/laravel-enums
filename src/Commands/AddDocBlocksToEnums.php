<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use LenderSpender\LaravelEnums\CanBeUnknown;
use ReflectionClass;

class AddDocBlocksToEnums extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ide-helper:generate:enums';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate DocBlocks for Enums';

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    private $filesystem;

    public function __construct(FilesystemManager $filesystemManager)
    {
        parent::__construct();

        $this->filesystem = $filesystemManager->createLocalDriver(['root' => app_path('Enums')]);
    }

    public function handle(): void
    {
        $enums = collect($this->filesystem->files())
            ->filter(function ($filename) {
                return $filename != 'Enum.php' && ! Str::startsWith($filename, '.');
            });

        $enums->each(function ($filename) {
            $reflection = new ReflectionClass('App\\Enums\\' . str_replace('.php', '', $filename));
            $contents = $this->filesystem->get($filename);
            $docBlocks = $this->generateDocBlocks($reflection);

            if ($docBlocks) {
                $this->filesystem->put(
                    $filename,
                    $this->createNewContents($reflection, $contents, $docBlocks)
                );
            }
        });

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
