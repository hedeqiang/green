<?php

/*
 * This file is part of the hedeqiang/green.
 *
 * (c) hedeqiang<laravel_code@163.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
     *
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
    public function __initialization()
    {
        try {
            AlibabaCloud::accessKeyClient($this->config->get('accessKeyId'), $this->config->get('accessKeySecret'))
                ->regionId($this->config->get('regionId', 'cn-beijing'))
                ->timeout($this->config->get('timeout', 6))
                ->connectTimeout($this->config->get('connectTimeout', 10))// 连接超时10秒
                ->debug($this->config->get('debug', false)) // 开启调试
                ->asDefaultClient();
        } catch (ClientException $e) {
            throw new ClientException($e->getErrorMessage(), $e->getErrorCode());
        }
    }

    /**
     * 文本垃圾内容检测.
     *
     * @param array | string $content 待检测文本，最长10000个中文字符（含标点）
     * @param null           $bizType 该字段用于标识业务场景
     *
     * @return \AlibabaCloud\Client\Result\Result|array
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function textScan($content, $bizType = null)
    {
        $tasks = $this->getTask($content, 'text');

        $body = [
            'scenes' => ['antispam'],
            'tasks' => $tasks,
        ];
        if (!empty($bizType)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('textScan', $body);
    }

    /**
     * 文本检测内容反馈.
     *
     * @param string|null $taskId  云盾内容安全服务器返回的，唯一标识该检测任务的ID
     * @param string|null $content 被检测的内容，最长10,000个字符
     * @param string|null $label   反馈的分类，与具体的scene对应。取值范围参考文本反垃圾scene 和 label说明。
     * @param string|null $note    备注，比如文本中的关键文字
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function textFeedback(string $taskId = null, string $content = null, string $label = null, string $note = null)
    {
        $body = [
            'taskId' => $taskId,
            'dataId' => uniqid(),
            'content' => $content,
            'label' => $label,
            'note' => $note,
        ];

        return $this->response('textFeedback', $body);
    }

    /**
     * 图片同步检测.
     *
     * @param string | array $url     待检测图像的URL
     * @param string[]       $scenes  指定图片检测的应用场景
     * @param string|null    $bizType 该字段用于标识业务场景
     * @param array          $extras  额外调用参数
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function imageSyncScan($url, $scenes = ['porn', 'terrorism', 'ad', 'qrcode', 'live', 'logo'], string $bizType = null, $extras = [])
    {
        $tasks = $this->getTask($url, 'img');

        $body = [
            'tasks' => $tasks,
            'scenes' => $scenes,
        ];
        if (!empty($extras)) {
            $body['extras'] = $extras;
        }
        if (!empty($bizType)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('imageSyncScan', $body);
    }

    /**
     * 图片异步检测.
     *
     * @param string | array $url      待检测图像的URL
     * @param string[]       $scenes   指定图片检测的应用场景
     * @param null           $seed     随机字符串，该值用于回调通知请求中的签名。当使用callback时，该字段必须提供。
     * @param null           $callback 异步检测结果回调通知您的URL，支持HTTP/HTTPS。该字段为空时，您必须定时检索检测结果。
     * @param null           $bizType  该字段用于标识业务场景
     * @param array          $extras   额外调用参数
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function imageAsyncScan($url, $scenes = ['porn', 'terrorism', 'ad', 'qrcode', 'live', 'logo'], $seed = null, $callback = null, $bizType = null, $extras = [])
    {
        $tasks = $this->getTask($url, 'img');

        $body = [
            'tasks' => $tasks,
            'scenes' => $scenes,
            'seed' => $seed,
            'callback' => $callback,
        ];

        if (!empty($extras)) {
            $body['extras'] = $extras;
        }
        if (!empty($bizType)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('imageAsyncScan', $body);
    }

    /**
     * 图片检测结果反馈.
     *
     * @param string|null    $taskId     云盾内容安全服务器返回的，唯一标识该检测任务的ID
     * @param string         $suggestion 用户期望URL的检测结果，传递该参数时必须传递scenes参数。取值范围：pass：正常 block：违规
     * @param string|null    $url        如果需要将图片样本流入云盾控制台的系统回流库中，则必须传递该参数
     * @param array|string[] $scenes     指定反馈的场景 porn：智能鉴黄 terrorism：暴恐涉政识别 ad：广告识别
     * @param string|null    $note       备注
     * @param string|null    $label      参考对应图片审核scene与label说明，传递该参数表示您认为该图片属于的细分类类别
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function imageScanFeedback(string $taskId = null, string $suggestion = 'block', string $url = null, array $scenes = ['porn', 'terrorism', 'ad'], string $note = null, string $label = null)
    {
        $body = [
            'taskId' => $taskId,
            'suggestion' => $suggestion,
            'url' => $url,
            'scenes' => $scenes,
            'note' => $note,
            'label' => $label,
        ];

        return $this->response('imageScanFeedback', $body);
    }

    /**
     * 图片异步检测结果.
     *
     * @param array $taskIds 要查询的taskId列表。最大长度不超过1,000。
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function imageAsyncScanResults(array $taskIds)
    {
        return $this->response('imageAsyncScanResults', $taskIds);
    }

    /**
     * 提交文件检测任务
     *
     * @param string|array $url
     * @param string[]     $textScenes  检测内容包含文本时，指定检测场景，取值：antispam
     * @param string[]     $imageScenes 检测内容包含图片时，指定检测场景
     * @param null         $bizType
     * @param null         $callback    异步检测结果回调通知您的URL，支持HTTP/HTTPS
     * @param null         $seed        该值用于回调通知请求中的签名
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function fileAsyncScan($url, $textScenes = ['antispam'], $imageScenes = ['porn', 'ad', 'terrorism', 'sface', 'qrcode', 'live', 'logo'], $bizType = null, $callback = null, $seed = null)
    {
        $tasks = $this->getTask($url, 'file');
        $body = [
            'tasks' => $tasks,
            'callback' => $callback,
            'seed' => $seed,
            'textScenes' => $textScenes,
            'imageScenes' => $imageScenes,
        ];
        if (!empty($bizType)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('fileAsyncScan', $body);
    }

    /**
     * 查询文件检测结果.
     *
     * @param array $taskId 要查询的taskId列表。最大长度不超过10个。
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function fileAsyncScanResults(array $taskId)
    {
        return $this->response('fileAsyncScanResults', $taskId);
    }

    /**
     * 短语音同步检测.
     *
     * @param string|array $url     需要检测的语音文件的下载地址，需要HTTP（S）协议可访问的公网链接
     * @param string[]     $scenes  指定检测场景，取值：antispam
     * @param null         $bizType 该字段用于标识业务场景
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function voiceSyncScan($url, $scenes = ['antispam'], $bizType = null)
    {
        $tasks = $this->getTask($url, 'file');
        $body = [
            'tasks' => $tasks,
            'scenes' => $scenes,
        ];
        if (!empty($bizType)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('voiceSyncScan', $body);
    }

    /**
     * 语音异步检测.
     *
     * @param $url
     * @param string[] $scenes   指定检测场景，取值：antispam
     * @param null     $bizType  该字段用于标识业务场景
     * @param null     $callback 异步检测结果回调通知您的URL。支持HTTP和HTTPS。
     * @param null     $seed     随机字符串，该值用于回调通知请求中的签名
     * @param false    $live     是否为直播流。默认为false，表示为普通语音文件检测；若需要检测语音流，该值必须传入true。
     * @param false    $offline  是否近线检测模式
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function voiceAsyncScan($url, $scenes = ['antispam'], $bizType = null, $callback = null, $seed = null, $live = false, $offline = false)
    {
        $tasks = $this->getTask($url, 'file');
        $body = [
            'tasks' => $tasks,
            'scenes' => $scenes,
            'live' => $live,
            'offline' => $offline,
            'seed' => $seed,
            'callback' => $callback,
        ];
        if (!empty($bizType)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('voiceAsyncScan', $body);
    }

    /**
     * @param array $taskId 要查询的taskId列表，最大长度不超过100
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function voiceAsyncScanResults(array $taskId)
    {
        return $this->response('voiceAsyncScanResults', $taskId);
    }

    /**
     * 取消语音检测任务
     *
     * @param array $taskId 要取消的taskId列表，最多支持取消100个任务
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function voiceCancelScan(array $taskId)
    {
        return $this->response('voiceCancelScan', $taskId);
    }

    /**
     * @param string[] $scenes
     * @param null     $bizType
     * @param null     $framePrefix
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function videoSyncScan(array $tasks, $scenes = ['porn', 'terrorism', 'ad', 'live', 'logo', 'sface'], $bizType = null)
    {
//        $tasks = [
//            [ 'url' => 'https://www.w3schools.com/html/movie.mp4','offset' => '5','framePrefix' => '0'],
//            [ 'url' => 'https://www.w3schools.com/html/movie.mp4','offset' => '10','framePrefix' => '0'],
//            [ 'url' => 'https://www.w3schools.com/html/movie.mp4','offset' => '15','framePrefix' => '0'],
//        ];

        $data['dataId'] = uniqid();
        $data['frames'] = $tasks;

        $body = [
            'tasks' => $data,
            'scenes' => $scenes,
        ];
        if (!empty($bizType)) {
            $body['bizType'] = $bizType;
        }
//        return $body;

        return $this->response('videoSyncScan', $body);
    }

    /**
     * 视频异步检测:.
     *
     * @param $url
     * @param string[] $scenes
     * @param null     $seed
     * @param null     $callback
     * @param array    $audioScenes
     * @param false    $live
     * @param false    $offline
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function videoAsyncScan($url, $scenes = ['porn', 'terrorism'], $seed = null, $callback = null, $audioScenes = [], $live = false, $offline = false)
    {
        $tasks = $this->getTask($url, 'video');
        $body = [
            'tasks' => $tasks,
            'scenes' => $scenes,
            'live' => $live,
            'offline' => $offline,
            'seed' => $seed,
            'audioScenes' => $audioScenes,
            'callback' => $callback,
        ];

        return $this->response('videoAsyncScan', $body);
    }

    /**
     * 查询视频异步检测结果.
     *
     * @param array $taskId 要查询的taskId列表。最大长度不超过100。
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function videoAsyncScanResults(array $taskId)
    {
        return $this->response('videoAsyncScanResults', $taskId);
    }

    /**
     * 停止视频检测.
     *
     * @param array $taskId 要查询的taskId列表。最大长度不超过100。
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function videoCancelScan(array $taskId)
    {
        return $this->response('videoCancelScan', $taskId);
    }

    /**
     * 视频检测结果反馈.
     *
     * @param array    $frames
     * @param null     $url
     * @param string   $suggestion
     * @param string[] $scenes
     *
     * @return mixed
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function videoFeedback(string $taskId, $frames = [], string $dataId = null, $url = null, $suggestion = 'block', $scenes = ['porn', 'terrorism', 'ad'])
    {
        $body = [
            'taskId' => $taskId,
            'dataId' => $dataId,
            'url' => $url,
            'suggestion' => $suggestion,
            'scenes' => $scenes,
            'frames' => $frames,
        ];

        return $this->response('videoFeedback', $body);
    }

    /**
     * @param $data
     * @param string $type
     *
     * @return array
     */
    protected function getTask($data, $type = 'text')
    {
        $tasks = [];
        $data = $this->generateArray($data);
        foreach ($data as $k => $v) {
            $arr = ['dataId' => uniqid()];
            if ('text' == $type) {
                $arr['content'] = $v;
            } elseif (in_array($type, ['img', 'file'])) {
                $arr['url'] = $v;
            } elseif ('video' == $type) {
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
     *
     * @return array
     */
    protected function generateArray($data)
    {
        $urls = [];
        if (!is_array($data)) {
            $res = json_decode($data, true);
            if (is_null($res)) {
                $urls[] = $data;
            } else {
                $urls[] = $res;
            }
        } else {
            $urls = $data;
        }

        return $urls;
    }

    /**
     * @return mixed
     *
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
