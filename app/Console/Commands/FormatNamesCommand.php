<?php

namespace App\Console\Commands;

use App\Services\FormatNamesService;
use Exception;
use Illuminate\Console\Command;
use League\Csv\Reader;

class FormatNamesCommand extends Command
{
    public const NAME_KEY = 'homeowner';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'format-names {--file=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected FormatNamesService $formatNamesService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Parsing file: ' . $this->option('file'));
        $this->info('--------------------------------------------------');

        try {
            $names = $this->parseCsvData($this->option('file'));

            $ouput = $this->formatNamesService->formatNames($names, JSON_PRETTY_PRINT);

            $this->line($ouput);
        } catch (Exception $ex) {
            report($ex);
            $this->error('An error occurred while processing the file');
            $this->error($ex->getMessage());
        }
    }

    public function parseCsvData(string $filePath): array
    {
        $names = [];

        $csv = Reader::from($filePath, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $names[] = $record[self::NAME_KEY];
        }

        return $names;
    }
}
