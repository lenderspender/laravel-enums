<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\src\Commands;

use Illuminate\Console\Command;
use LenderSpender\LaravelEnums\EnumerateModelDocBlock;

class AddEnumDocBlockToModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ide-helper:generate:model-enums';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Enum DocBlocks for Models';

    /**
     * @var \LenderSpender\LaravelEnums\EnumerateModelDocBlock
     */
    private $enumerateModelDocBlock;

    public function __construct(EnumerateModelDocBlock $enumerateModelDocBlock)
    {
        parent::__construct();

        $this->enumerateModelDocBlock = $enumerateModelDocBlock;
    }

    public function handle(): void
    {
        $this->enumerateModelDocBlock->enumerate(app_path('Models'), 'App\Models');
    }
}
