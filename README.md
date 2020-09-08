<h1 align="center"> green </h1>

<p align="center"> 阿里云内容安全 PHP SDK.</p>

> 请先阅读 [阿里云内容安全文档](https://help.aliyun.com/document_detail/63004.html?spm=a2c4g.11186623.6.606.4a9160d1jDj9ak)

## Installing

```shell
$ composer require hedeqiang/green -vvv
```

## Usage
```php
require __DIR__ .'/vendor/autoload.php';

use Hedeqiang\Green\Green;

$config = [
    'accessKeyId' => '',
    'accessKeySecret' => '',
    //'regionId' => '',
    //timeout => 6
    //'connectTimeout' => 6,
    //debug' => false,
];

$green = new Green($config);
```

### 文本内容检测
```php
$green->textScan(['文本1','文本2']); # 支持数组、字符串
```

### 文本检测内容反馈
```php
$green->textFeedback($taskId); # 支持数组、字符串
```
### 图片同步检测
```php
$green->imageSyncScan($url, $scenes = ['porn', 'terrorism','ad']); # url 支持数组、字符串
```

### 图片异步检测
```php
$green->imageAsyncScan($url, $scenes = ['porn', 'terrorism','ad']); # url 支持数组、字符串
```

### 图片异步检测结果
```php
$green->imageAsyncScanResults($taskIds);
```

### 图片检测结果反馈
```php
$green->imageScanFeedback($taskId);
```

### More...

## 在 Laravel 中使用
#### 发布配置文件
```php
php artisan vendor:publish --tag=green
```
##### 编写 .env 文件
```
GREEN_ACCESS_KEY_ID=
GREEN_ACCESS_KEY_SECRET=
GREEN_REGION_ID=cn-beijing
GREEN_TIMEOUT=6
GREEN_CONNECT_TIMEOUT=6
GREEN_DEBUG=false
```

### 方法参数注入
参数和上面一样

```php
public function index(Green $green)
{
    $response = $green->textScan(['文本1','文本2']); # 支持数组、字符串
}
```
### 服务名访问
```php
public function index()
{
    $response = app('green')->textScan(['文本1','文本2']); # 支持数组、字符串
}
```

### Facades 门面使用(可以提示)
```php
use Hedeqiang\Green\Facades\Green;
$response = Green::green()->textScan(['文本1','文本2']);
```


## 返回格式示例

```json
{
    "code":200,
    "data":[
        {
            "code":200,
            "content":"文本1",
            "dataId":"5f508a2dec2a6",
            "msg":"OK",
            "results":[
                {
                    "label":"normal",
                    "rate":99.91,
                    "scene":"antispam",
                    "suggestion":"pass"
                }
            ],
            "taskId":"txt5iHPuCGHb024i2AWj92PTK-1t5arO"
        },
        {
            "code":200,
            "content":"文本2",
            "dataId":"5f508a2dec2a8",
            "msg":"OK",
            "results":[
                {
                    "label":"normal",
                    "rate":99.91,
                    "scene":"antispam",
                    "suggestion":"pass"
                }
            ],
            "taskId":"txt1KDW04MfrTP5ijNpdjokAe-1t5arO"
        }
    ],
    "msg":"OK",
    "requestId":"F07776F3-E584-4A8C-B4CB-B7AA954823C1"
}
```

TODO

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/hedeqiang/green/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/hedeqiang/green/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT
