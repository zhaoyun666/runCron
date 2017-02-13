#runCron
计划任务项目框架
###项目目录详解
	DB核心类
		|——core
		|	|——DB.php 核心数据库操作类
		|——app
	  	|	|——modules
	    |	|	|——module
	    |	|	|	|——single文件 操作业务逻辑
		|——library
	  	|	|——dao 数据库操作类
	    |	|	|——db1
	    |	|	|	|——tableDB.php 操作单个表的数据库操作
	  	|	|——tools 拓展工具
	    |	|	|——snailRedis.php
	    |	|	|——snailMemcache.php
	    |	|	|——snailMongo.php
		|——config
	  	|	|——comm.conf.php 配置文件
		|——run.php //入口文件
