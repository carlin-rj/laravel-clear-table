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
				$totalDeletedForRange = 0;

				$lastId = 0;
				$chunkSize = $tableConfig['chunk_size'] ?? 1000;

				do {
					// 重新构建查询
					$query = $this->newModel($tableConfig);
					$tableConfig['conditions']($query);

					// 限制条件：日期范围 + id > lastId（无排序）
					$rows = $query
						->whereBetween($tableConfig['date_column'], [$range['start'], $range['end']])
						->where('id', '>', $lastId)
						->limit($chunkSize)
						->get(['id']);

					if ($rows->isEmpty()) {
						break;
					}

					$idsToDelete = $rows->pluck('id')->toArray();

					// 记录最后一个 ID 作为下一轮起点（自然顺序增长）
					$lastId = max($idsToDelete);

					// 删除当前批次
					if (!empty($idsToDelete)) {
						$deletedCount = $this->newModel($tableConfig)
							->whereIn('id', $idsToDelete)
							->delete();
						$totalDeletedForRange += $deletedCount;
					}

					$this->info("Deleted {$deletedCount} from {$tableName} in range {$range['start']} - {$range['end']}, Total: {$totalDeletedForRange}");

					// 内存释放
					unset($rows, $idsToDelete);
					gc_collect_cycles();
				} while (true);

				$this->info("Completed range {$range['start']} - {$range['end']} for table {$tableName}. Total deleted: {$totalDeletedForRange}");
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
