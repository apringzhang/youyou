<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */

//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;
use Workerman\MySQL;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 配置文件
     * @var array
     */
    private static $config;

    /**
     * 数据库对象
     * @var MySQL\Connection
     */
    private static $db;

    /**
     * 客户认证数组
     * @var
     */
    private static $clientList;

    private static function post($url, $param)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 发送消息
     * @param $client_id string 客户端ID(空为发送给所有人)
     * @param $type string 消息类型
     * @param $data string 消息内容
     * @throws
     */
    private static function sendMessage($client_id, $type, $data)
    {
        $message = [
            'type' => $type,
            'data' => $data,
        ];
        if (!empty($client_id)) {
            Gateway::sendToClient($client_id, json_encode($message));
        } else {
            Gateway::sendToAll(json_encode($message));
        }

    }

    private static function sendGroupMessage($group, $type, $data, $uid, $nickname, $avatar_url)
    {
        $message = [
            'group' => $group,
            'type' => $type,
            'data' => $data,
            'nickname' => $nickname,
            'uid' => $uid,
            'avatar_url' => $avatar_url
        ];
        if ($type == 'audio' || $type == 'image') {
            $message['data'] = str_replace('\\', '/', $message['data']);
        }
        Gateway::sendToGroup($group, json_encode($message));
    }

    /**
     * 当Worker线程启动时触发
     *
     * @param $businessWorker \GatewayWorker\BusinessWorker
     */
    public static function onWorkerStart($businessWorker)
    {
        //加载配置
        self::$config = require_once __DIR__ . '/Config.php';
        //初始化数据库
        self::$db = new MySQL\Connection(
            self::$config['db_host'],
            self::$config['db_port'],
            self::$config['db_user'],
            self::$config['db_password'],
            self::$config['db_name']
        );
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        self::sendMessage($client_id, 'system', '连接成功');
        //限时认证，否则断开链接
        $_SESSION['auth_timer_id'] = Timer::add(self::$config['login_timeout'], function ($client_id) {
            self::sendMessage($client_id, 'error', '登录超时');
            Gateway::closeClient($client_id);
        }, array($client_id), false);
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message)
    {
        $message = json_decode($message, true);
        {
            if (empty($message)) {
                self::sendMessage($client_id, 'error', '非法数据');
                Gateway::closeClient($client_id);
                return;
            }
            echo $client_id . '-------' . "\n";
            if ($message['type'] != 'login') {
                if (empty(self::$clientList[$client_id])) {
                    self::sendMessage($client_id, 'error', '您尚未登录');
                    Gateway::closeClient($client_id);
                }
            }
            var_dump($client_id) . "\n";
            var_dump(self::$clientList[$client_id]) . "\n";
            switch ($message['type']) {
                //登录
                case 'login':
                    try {
                        $param = [
                            'session_id' => $message['session_id'],
                        ];

                        if (empty($message['session_id'])) {
                            throw new Exception('登录失败');
                        }
                        $info = self::post(self::$config['api_url'] . '/index.php/api/user/get_user_info', json_encode($param));
                        $info = json_decode($info, true);

                        //加入授权客户端列表
                        self::$clientList[$client_id] = $info['message']['nick_name'];
                        self::sendMessage($client_id, 'login', $info['message']['nick_name']);
                        self::sendMessage('', 'system', self::$clientList[$client_id] . '进入了房间');
                        
                        $lists = self::post(self::$config['api_url'] . '/index.php/api/group/get_group_list', $param);
                        $lists = json_decode($lists, true);
                        Gateway::bindUid($client_id, $lists['uid']);
                        if ($lists['data']) {
                            foreach ($lists['data'] as $value) {
                                Gateway::joinGroup($client_id, $value);
                            }
                        }
                        
                    } catch (Exception $e) {
                        self::sendMessage($client_id, 'error', $e->getMessage());
                        Gateway::closeClient($client_id);
                        return;
                    }
                    //删除断开链接计时
                    Timer::del($_SESSION['auth_timer_id']);
                    //登录成功消息
                    self::sendMessage($client_id, 'system', '登录成功');
                    break;
                case 'text':
                    if (empty($message['data'])) {
                        self::sendMessage($client_id, 'info', '发送内容不能为空');
                    }
                    $param = [
                        'session_id' => $message['session_id'],
                    ];

                    if (empty($message['session_id'])) {
                        throw new Exception('登录失败');
                    }
                    $info = self::post(self::$config['api_url'] . '/index.php/api/user/get_user_info', json_encode($param));
                    $info = json_decode($info, true);

                    // $message['uid'] = 28;
                    // $message['group'] = 1;
                    self::sendGroupMessage($message['group'], 'text', $message['data'], $info['message']['id'], self::$clientList[$client_id], $info['message']['avatar_url']);
                    //保存聊天记录
                    $resultcs = self::post(self::$config['api_url'] . '/index.php/api/group/group_message', $message);
                    
                    break;
                case 'image':
                    if (empty($message['data'])) {
                        self::sendMessage($client_id, 'info', '发送内容不能为空');
                    }
                    $param = [
                        'session_id' => $message['session_id'],
                    ];

                    if (empty($message['session_id'])) {
                        throw new Exception('登录失败');
                    }
                    $info = self::post(self::$config['api_url'] . '/index.php/api/user/get_user_info', json_encode($param));
                    $info = json_decode($info, true);
                    
                    // var_dump(self::$clientList[$client_id]);
                    // $message['uid'] = 28;
                    // $message['group'] = 1;
                    self::sendGroupMessage($message['group'], 'imgs', $message['data'], $info['message']['id'], self::$clientList[$client_id], $info['message']['avatar_url']);
                    //保存聊天记录
                    $resultcs = self::post(self::$config['api_url'] . '/index.php/api/group/group_message', $message);
                    
                    break;
                case 'audio':
                    if (empty($message['data'])) {
                        self::sendMessage($client_id, 'info', '发送内容不能为空');
                    }
                    $param = [
                        'session_id' => $message['session_id'],
                    ];

                    if (empty($message['session_id'])) {
                        throw new Exception('登录失败');
                    }
                    $info = self::post(self::$config['api_url'] . '/index.php/api/user/get_user_info', json_encode($param));
                    $info = json_decode($info, true);
                    
                    // var_dump(self::$clientList[$client_id]);
                    // $message['uid'] = 28;
                    // $message['group'] = 1;
                    self::sendGroupMessage($message['group'], 'audio', $message['data'], $info['message']['id'], self::$clientList[$client_id], $info['message']['avatar_url']);
                    //保存聊天记录
                    $resultcs = self::post(self::$config['api_url'] . '/index.php/api/group/group_message', $message);
                    
                    break;
                default:
                    self::sendMessage($client_id, 'error', '消息类型错误');
                    Gateway::closeClient($client_id);
            }
        }
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        unset(self::$clientList[$client_id]);
        self::sendMessage($client_id, 'system', '断开连接');
    }
}
