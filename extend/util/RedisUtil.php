<?php


namespace util;


use Predis\Client;
use think\facade\Config;

class RedisUtil
{
    private static $_instance;
    public $redis;
    private static $db = 'redis';
    private static $connectionPools = [];

    private function __construct($connection){
        if (!empty(self::$connectionPools[$connection])) {
            $this->redis = self::$connectionPools[$connection];
            return;
        }
        $redisConf = Config::get("cache.stores.$connection");
        $redisServer = [
            'host' => $redisConf['host'],
            'port' => $redisConf['port'],
            'database' => $redisConf['select']
        ];
        if(isset($redisConf['password'])){
            $redisServer['password']=$redisConf['password'];
        }
        $conn = new Client($redisServer);
        self::$connectionPools[$connection] = $conn;
        $this->redis = $conn;
    }

    private function __clone(){}

    public static function getInstance($connection = 'redis'){
        if (self::$db == $connection) {
            if(!(self::$_instance instanceof self)){
                self::$_instance = new self($connection);
            }
        } else {
            self::$_instance = new self($connection);
            self::$db = $connection;
        }
        return self::$_instance;
    }

}