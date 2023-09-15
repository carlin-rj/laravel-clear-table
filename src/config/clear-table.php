<?php

//use App\Enums\LogActionTypeEnum;
//use App\Enums\PlatformProductTypeEnum;
//use Modules\Shopee\Models\ShopeeGlobalCollectSkuLog;

return [
	//示例
	//'pb_shopee_global_platform_collect_product_sku_log' => [
	//  'chunk_size'=>2000,
	//	'model'        => ShopeeGlobalCollectSkuLog::class,
	//	'is_optimized' => true,
	//	'conditions'   => function ($query) {
	//		$query->where(ShopeeGlobalCollectSkuLog::_TYPE, PlatformProductTypeEnum::ONLINE)
	//			->where(ShopeeGlobalCollectSkuLog::_ACTION_TYPE, LogActionTypeEnum::SYNC_STOCK);
	//		// 你可以在这里加入任何其他复杂的查询逻辑
	//	},
	//	'date_column' => ShopeeGlobalCollectSkuLog::_CREATED_AT,
	//],
	// ... 其他表的设置 ...
];
