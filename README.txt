2017.8.8
本框架是利用php的协程协程的异步请求框架、


[目录树]

|-app 要写的项目所在目录
|-config 项目配置目录
|-kernel 内核目录
  |-Cache 框架缓存实现源码目录
  |-Db 框架数据库实现目录
  |-event 框架所用事件模版
  |-Facades 门面模式、提供统一接口目录
  |-Protocol --协议实现目录
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
|-route-路由配置
|-autoload 自动加载类
|-index.php 入口文件
|-kernel 内核具体实现 *



[注意]
1、php -v > 7.0.0
2、扩展需要posix pcntl

[操作]
1、配置写到config目录里（要启动的服务写到conf文件里、config/servers目录下为各个项目的配置、一个conf文件一个配置（务必）。config/serve.conf为服务的基本配置）
2、路由写到route目录里
3、
4、运行的方式：
         >正常运行（都是守护模式）
                 php index.php
                 php index.php daemon 全部启动
                 php index.php daemon all/...  全部或者单一项目服务
         >调试运行
                 php index.php debug ...(只能写单一项目服务、如果没有则默认第一个服务项目) 是调试模式 别名是config/servers 下面的文件basename

5、PHP index.php close 全部关闭
6、控制器的返回必须是 return yield .. 比如 return yield 'hello word';


[未完成]
1、内核未写全
2、事件livevent的类
3、定时器的实现
4、异步tcp、udp请求
5、异步mysql、redis实现、
6、其他

[疑惑]

1、异步mysql和redis的可以用swoole实现、但是swoole太全太乱、不是太喜欢、如果不行就直接用PHP写实现、因个人能力不足、这个地方可能copy网上实现












