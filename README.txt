2017.8.8
[目录树]

|-app 要写的项目所在目录
|-cache 框架缓存
|-config 项目配置目录
|-db 框架数据库
|-event 框架所用事件模版
|-facades 门面模式、提供统一接口
|-kernel 内核目录
  |-Config.php 配置源码
  |-Connetion.php 连接池
  |-Pipleline 管道
  |-Request 解析请求
  |-Response 设置返回
  |-Route 路由源码
  |-Scheduler 调度器 *
  |-Serve 服务器源码
  |-Task 任务抽象源码
  |-Timer 定时器实现
  |-Work
|-lib 一些库、比如函数
|-middle --中间件
|-protocol --协议实现
|-route-路由配置
|-autoload 自动加载类
|-index.php 入口文件
|-kernel 内核具体实现 *



[注意]
1、php -v > 7.0.0
2、扩展需要posix pcntl

[操作]

1、配置写到config目录里
2、路由写到route目录里（要启动的服务写到conf文件里）
3、php index.php 和 php index.php debug ...(此处写别名) 是调试模式 别名是conf文件里的【】里面的
4、php index.php reload/stop/restart ...(此处写别名) 别名如果不写、默认全部工程
5、














