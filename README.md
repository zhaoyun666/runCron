# runCron
计划任务项目框架
#项目目录详解
#core
  ++DB.php 核心数据库操作类
#app
  ++modules
    ++module
      ++single文件 操作业务逻辑
    ++module
      ++single文件
      
#library
  ++dao
    ++db1
      ++tableDB.php 操作单个表的数据库操作
      ....
    .....
  ++tools
    ++snailRedis.php
    ++snailMemcache.php
    ++snailMongo.php
    ...
#config
  comm.conf.php
#run.php //入口文件
