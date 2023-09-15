# 项目介绍

## 要求
```
laravel > 5.8
php >= 7.3
```
## 安装

```
composer require mckue/laravel-clear-table
```

生成配置文件
```
php artisan vendor:publish --tag=clear-tables
```

将会生成一个config/clear-tables.php配置文件

## 编写配置
```
<?php
return [
    //示例
    [
		//批次处理删除n条数据
      	'chunk_size'=>2000,
		//模型类
    	'model'        => ShopeeGlobalCollectSkuLog::class,
		//是否优化表
    	'is_optimized' => true,
		//表条件
    	'conditions'   => function ($query) {
    		$query->where('type', 1);
    		// 你可以在这里加入任何其他复杂的查询逻辑....
    	},
		//通过时间字段分段删除(这个字段要求为索引类型的时间字段，否则效果适得其反)
    	'date_column' =>'created_at',
    ],
];
```

## 执行命令 or 定时任务

执行命令会跑配置项的所有的表
```
php artisan clear-tables
```

指定表清理
``` 
php artisan clear-tables --table=your_table
```

清理参数
``` 
php artisan clear-tables {startDay=40} {endDay=30} {interval=10} {unit=day} {--table=}

startDay=生成时间段的开始天数 当前时间往前推n天
endDay=生成时间段的结束天数
interval=时间段间隔
unit=时间段间隔单位
```

### 定时任务

```
 //指定表
 $schedule->command('clear-table --table=your_table', '* * * * *');

 //不指定
 $schedule->command('clear-table', '* * * * *');
```
