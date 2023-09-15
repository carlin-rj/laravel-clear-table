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
        $tableConfigs = config('clear-tabless');
        if (isset($specificTable)) {
            if (! isset($tableConfigs[$specificTable])) {
                $this->error("{$specificTable} not configure clear_tables");

                return;
            } else {
                $tableConfigs = [$tableConfigs[$specificTable]];
            }
        }

        foreach ($tableConfigs as $tableName => $tableConfig) {
            $dateRanges = generateDateRanges($interval, $unit, now()->subDays($startDay), now()->subDays($endDay));
            foreach ($dateRanges as $range) {
                $totalDeletedForRange = 0;  // 初始化计数器，用于计算当前日期范围内的删除总数
                /** @var Builder $query */
                $query = $tableConfig['model']::query();
                $tableConfig['conditions']($query);  // Apply conditions
                $query->whereBetween($tableConfig['date_column'], [$range['start'], $range['end']])
                    ->select('id')
                    ->chunkById($tableConfig['chunk_size'] ?? 1000, function ($logs) use (&$totalDeletedForRange, $tableConfig) {
                        $idsToDelete = $logs->pluck('id')->toArray();
                        if (! empty($idsToDelete)) {
                            $deletedCount = $tableConfig['model']::query()->whereIn('id', $idsToDelete)->delete();
                            $totalDeletedForRange += $deletedCount;
                        }
                    });
                $this->info("Deleted logs from {$range['start']} to {$range['end']}. Total: $totalDeletedForRange");
            }

            // 优化表
            if (! empty($tableConfig['is_optimized'])) {
                DB::statement('OPTIMIZE TABLE '.$tableName);
                $this->info("Optimized the {$tableName} table.");
            }
        }
    }
}
