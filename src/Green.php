<?php

namespace Hedeqiang\Green;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Hedeqiang\Green\Config\Config;
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
    public  function  __initialization() {
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
     * @param array string $content
     * @return \AlibabaCloud\Client\Result\Result|array
     * @throws ClientException
     * @throws ServerException
     */
    public function textScan($content)
    {
        $tasks = $this->getTask($content,'text');

        $body = [
            'scenes' => ['antispam'],
            'tasks' => $tasks,

        ];
        return $this->response('textScan', $body);
    }

    /**
     * 图片同步检测
     * @param $url
     * @param $bizType
     * @param string[] $scenes
     * @param array $extras
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function imageSyncScan($url, $scenes = ['porn', 'terrorism','ad','qrcode','live','logo'], $bizType = '', $extras=[])
    {
        $tasks = $this->getTask($url, 'img');

        $body = [
            'tasks' => $tasks,
            'scenes' => $scenes,
        ];
        if(!empty($extras)) {
            $body['extras'] = $extras;
        }
        if(!empty($extras)) {
            $body['bizType'] = $bizType;
        }
        return $this->response('ImageSyncScan', $body);
    }

    /**
     * 图片异步检测
     * @param $url
     * @param string[] $scenes
     * @param null $seed
     * @param null $callback
     * @param string $bizType
     * @param array $extras
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function imageAsyncScan($url, $scenes = ['porn', 'terrorism'], $seed = null, $callback= null,$bizType = '', $extras=[])
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
        if(!empty($extras)) {
            $body['bizType'] = $bizType;
        }

        return $this->response('ImageAsyncScan', $body);
    }

    /**
     * 图片异步检测结果
     * @param $taskId
     * @param $suggestion
     * @param $url
     * @param string[] $scenes
     * @param $note
     * @param $label
     * @return mixed
     * @throws ClientException
     * @throws ServerException
     */
    public function imageAsyncScanResults($taskId = '',$suggestion ='',$url ='',$scenes = ['porn','terrorism','ad'],$note ='',$label ='')
    {

        $body = [
            'taskId' => $taskId,
//            'suggestion' => $suggestion,
//            'url' => $url,
//            'scenes'   => $scenes,
//            'note' => $note,
//            'label' => $label,
        ];

        return $this->response('ImageAsyncScanResults', $body);
    }



    protected function getTask($data, $type='img') {
        $tasks =[];
        $urls = $this->generateArray($data);
        foreach ($urls as $k => $v) {
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
            $result = AliYunGreen::v20180509()->$action()->body($body)->request();
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