<?php

namespace Mckue\LaravelClearTable\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClearTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-tables {startDay=40} {endDay=30} {interval=10} {unit=day} {--table=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时清理指定表数据';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $startDay = (int) $this->argument('startDay');
        $endDay = (int) $this->argument('endDay');
        $interval = (int) $this->argument('interval');
        $unit = $this->argument('unit');
        $specificTable = $this->option('table');

        if ($startDay < $endDay) {
            $this->error('startDay必须大于endDay');

            return;
        }
        $tableConfigs = config('clear-tables');
        if (isset($specificTable)) {
            $tableConfig = $this->findTableConfig($specificTable, $tableConfigs);
            if (empty($tableConfig)) {
                $this->error("{$specificTable} not configure clear_tables");

                return;
            }
            $tableConfigs = [$tableConfig];
        }

        if (empty($tableConfigs)) {
            $this->error('not configure clear_tables');

            return;
        }

        foreach ($tableConfigs as $tableConfig) {
            $tableName = $this->getTableName($tableConfig);
            $dateRanges = generateDateRanges($interval, $unit, now()->subDays($startDay), now()->subDays($endDay));
            foreach ($dateRanges as $range) {
                $totalDeletedForRange = 0;  // 初始化计数器，用于计算当前日期范围内的删除总数
                /** @var Builder $query */
                $query = $this->newModel($tableConfig);
                $tableConfig['conditions']($query);  // Apply conditions
                $query->whereBetween($tableConfig['date_column'], [$range['start'], $range['end']])
                    ->select('id')
                    ->chunkById($tableConfig['chunk_size'] ?? 1000, function ($logs) use (&$totalDeletedForRange, $tableConfig) {
                        $idsToDelete = $logs->pluck('id')->toArray();
                        if (! empty($idsToDelete)) {
                            $deletedCount = $this->newModel($tableConfig)->whereIn('id', $idsToDelete)->delete();
                            $totalDeletedForRange += $deletedCount;
                        }
                    });
                $this->info("Deleted logs {$tableName} from {$range['start']} to {$range['end']}. Total: $totalDeletedForRange");
            }

            // 优化表
            if (! empty($tableConfig['is_optimized'])) {
                DB::statement('OPTIMIZE TABLE '.$tableName);
                $this->info("Optimized the {$tableName} table.");
            }
        }
    }

    private function newModel(array $tableConfig)
    {
        if (is_subclass_of($tableConfig['model'], \Illuminate\Database\Eloquent\Model::class)) {
            return $tableConfig['model']::query();
        } else {
            return DB::table($tableConfig['model']);
        }
    }

    private function getTableName(array $tableConfig): string
    {
        return is_subclass_of($tableConfig['model'], \Illuminate\Database\Eloquent\Model::class) ? (new $tableConfig['model'])->getTable() : $tableConfig['model'];
    }

    private function findTableConfig(string $tableName, array $tableConfigs): ?array
    {
        foreach ($tableConfigs as $tableConfig) {
            if ($tableName === $this->getTableName($tableConfig)) {
                return $tableConfig;
            }
        }

        return null;
    }
}
