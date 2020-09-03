<?php

namespace Hedeqiang\Green;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Green\Green as AliYunGreen;

class Green
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Green constructor.
     * @param array $config
     * @throws ClientException
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        $this->__initialization();
    }

    /**
     * @throws ClientException
     */
    public function __initialization() {
        try {
            AlibabaCloud::accessKeyClient($this->config->get('accessKeyId'), $this->config->get('accessKeySecret'))
                ->regionId($this->config->get('regionId', 'cn-beijing'))
                ->timeout($this->config->get('timeout', 6))
                ->connectTimeout($this->config->get('connectTimeout', 10))// 连接超时10秒
                ->debug($this->config->get('debug', false)) // 开启调试
                ->asDefaultClient();
        } catch (ClientException $e) {
            throw new ClientException($e->getErrorMessage(),$e->getErrorCode());
        }
    }

    /**
     * 文本垃圾内容检测
     * @param array | string $content 待检测文本，最长10000个中文字符（含标点）。
     * @param null $bizType 该字段用于标识业务场景
     * @return \AlibabaCloud\Client\Result\Result|array
     * @throws ClientException
     * @throws ServerException
     */
    public function textScan($content,$bizType = null)
    {
        $tasks = $this->getTask($content,'text');

        $body = [
            'scenes' => ['antispam'],
            'tasks' => $tasks,
        ];
        if(!empty($bizType)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('textScan', $body);
    }

    /**
     * 文本检测内容反馈
     * @param string|null $taskId 云盾内容安全服务器返回的，唯一标识该检测任务的ID
     * @param string|null $dataId 对应的请求中的dataId。
     * @param string|null $content 	被检测的内容，最长10,000个字符。
     * @param string|null $label 反馈的分类，与具体的scene对应。取值范围参考文本反垃圾scene 和 label说明。
     * @param string|null $note 备注，比如文本中的关键文字。
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function textFeedback(string $taskId = null,string $dataId = null,string $content = null,string $label = null,string $note = null)
    {
        $body = [
            'taskId' => $taskId,
            'dataId' => $dataId,
            'content'   => $content,
            'label' => $label,
            'note' => $note,
        ];

        return $this->response('TextFeedback ',$body);
    }

    /**
     * 图片同步检测
     * @param string | array $url 待检测图像的URL。
     * @param string[] $scenes 指定图片检测的应用场景。
     * @param string|null $bizType 该字段用于标识业务场景
     * @param array $extras 额外调用参数。
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function imageSyncScan($url, $scenes = ['porn', 'terrorism','ad','qrcode','live','logo'], string $bizType = null, $extras=[])
    {
        $tasks = $this->getTask($url, 'img');

        $body = [
            'tasks' => $tasks,
            'scenes' => $scenes,
        ];
        if(!empty($extras)) {
            $body['extras'] = $extras;
        }
        if(!empty($bizType)) {
            $body['bizType'] = $bizType;
        }
        return $this->response('ImageSyncScan', $body);
    }

    /**
     * 图片异步检测
     * @param string | array $url 待检测图像的URL。
     * @param string[] $scenes 指定图片检测的应用场景。
     * @param null $seed 随机字符串，该值用于回调通知请求中的签名。当使用callback时，该字段必须提供。
     * @param null $callback 异步检测结果回调通知您的URL，支持HTTP/HTTPS。该字段为空时，您必须定时检索检测结果。
     * @param null $bizType 该字段用于标识业务场景
     * @param array $extras 额外调用参数。
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function imageAsyncScan($url, $scenes = ['porn', 'terrorism','ad','qrcode','live','logo'], $seed = null, $callback= null,$bizType = null, $extras=[])
    {
        $tasks = $this->getTask($url, 'img');

        $body = [
            'tasks' => $tasks,
            'scenes' => $scenes,
            'seed'   => $seed,
            'callback' => $callback,
        ];

        if(!empty($extras)) {
            $body['extras'] = $extras;
        }
        if(!empty($bizType)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('ImageAsyncScan', $body);
    }

    /**
     * 图片检测结果反馈
     * @param string|null $taskId 云盾内容安全服务器返回的，唯一标识该检测任务的ID。
     * @param string $suggestion 用户期望URL的检测结果，传递该参数时必须传递scenes参数。取值范围：pass：正常 block：违规
     * @param string|null $url 如果需要将图片样本流入云盾控制台的系统回流库中，则必须传递该参数。
     * @param array|string[] $scenes 	指定反馈的场景 porn：智能鉴黄 terrorism：暴恐涉政识别 ad：广告识别
     * @param string|null $note 备注。
     * @param string|null $label 参考对应图片审核scene与label说明，传递该参数表示您认为该图片属于的细分类类别
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function imageScanFeedback(string $taskId = null,string $suggestion = 'block',string $url = null,array $scenes = ['porn','terrorism','ad'],string $note = null,string $label = null)
    {
        $body = [
            'taskId' => $taskId,
            'suggestion' => $suggestion,
            'url'   => $url,
            'scenes' => $scenes,
            'note' => $note,
            'label' => $label,
        ];

        return $this->response('ImageScanFeedback',$body);
    }

    /**
     * 图片异步检测结果
     * @param array $taskIds
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function imageAsyncScanResults(array $taskIds = [])
    {
        return $this->response('ImageAsyncScanResults', $taskIds);
    }


    /**
     * @param $data
     * @param string $type
     * @return array
     */
    protected function getTask($data, $type='text') {
        $tasks =[];
        $data = $this->generateArray($data);
        foreach ($data as $k => $v) {
            $arr = ['dataId' => uniqid()];
            if($type == 'text') {
                $arr['content'] = $v;
            } else if(in_array($type,array('img','file'))) {
                $arr['url'] = $v;
            } else if($type == 'video') {
                $arr['url'] = $v;
                $arr['interval'] = 1;
                $arr['maxFrames'] = 200;
            }
            $tasks[] = $arr;
        }
        return  $tasks;
    }

    /**
     * @param $data
     * @return array
     */
    protected function generateArray($data)
    {
        $urls = [];
        if(!is_array($data)) {
            $res = json_decode($data, true);
            if(is_null($res)) {
                $urls[] = $data;
            } else {
                $urls = $res;
            }
        } else {
            $urls = $data;
        }
        return $urls;
    }

    /**
     * @param string $action
     * @param array $body
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function response(string $action, array $body)
    {
        try {
            $body = json_encode($body);
            $result = AliYunGreen::v20180509()->{$action}()->body($body)->request();
            if ($result->isSuccess()) {
                return $result->toArray();
            } else {
                return $result;
            }
        } catch (ClientException $e) {
            throw new ClientException($e->getErrorMessage(), $e->getErrorCode());

        } catch (ServerException $e) {

            throw new ServerException($e->getResult(), $e->getErrorMessage(), $e->getErrorMessage());
        }
    }
}