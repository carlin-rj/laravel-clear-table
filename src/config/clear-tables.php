<?php

return [
    //示例
    [
        //批次处理删除n条数据
        'chunk_size' => 2000,
        //模型类
        'model' => ShopeeGlobalCollectSkuLog::class,
        //是否优化表
        'is_optimized' => true,
        //表条件
        'conditions' => function ($query) {
            $query->where('type', 1);
            // 你可以在这里加入任何其他复杂的查询逻辑....
        },
        //通过时间字段分段删除(这个字段要求为索引类型的时间字段，否则效果适得其反)
        'date_column' => 'created_at',
    ],
];
