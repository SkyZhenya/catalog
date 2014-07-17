<?php

namespace Application\Lib;
class Redis {
	/**
	 * @var \Redis
	 */
	protected $redis;
	protected $namespace;
	protected $connected = false;
	const MAX_TRIES = 10;

	public function __construct() {
		$this->redis = new \Redis();
		if(REDIS_ENABLED) {
			$this->connect();
		}
	}

	/**
	 * Connects to redis daemon
	 *
	 */
	protected function connect() {
		$res = $this->redis->pconnect(REDIS_HOST);
		$this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
		$this->redis->select(REDIS_NAMESPACE);
		$this->connected = true;
	}

	/**
	 * assigns a value to a specified param
	 *
	 * @param string $name param name
	 * @param string $value param value
	 * @param int $timeout TTL in seconds (month by default)
	 * @param int $try
	 * @return bool true on success, false on fail MAX_TRIES times
	 */
	public function set($key, $value, $ttl=2678400,  $try=0) {
		if(!REDIS_ENABLED) return;

		if($try > self::MAX_TRIES) {
			return false;
		}
		
		if($ttl) {
			$ret = $this->redis->setex($key, $ttl, $value);
		}
		else {
			$ret = $this->redis->set($key, $value);
		}
		
		if(!$ret) {
			$this->connect();
			$this->set($key, ($value), $ttl, $try+1);
		}

		return $ret;
	}

	/**
	 * get value
	 *
	 * @param string $key
	 * @return string
	 */
	public function get($key) {
		if(!REDIS_ENABLED) return;

		$ret = $this->redis->get($key);
		return $ret;
	}

	static function myHandler() {
		//throw new \Exception('Bad keys!');
	}

	/**
	 * Get the values of all the specified keys. If one or more keys dont exist, 
	 * the array will contain FALSE at the position of the key.
	 *
	 * @param array $names keys to fetch
	 * @return array of mixed
	 */
	public function mget($keys) {
		
		if(!REDIS_ENABLED) return;

		$oldHandler = set_error_handler('\Application\Lib\Redis::myHandler');
		try {
			$ret = @$this->redis->mGet($keys);
		}
		catch(\Exception $e) {
			return [];
		}
		set_error_handler($oldHandler);
		return $ret;
	}

	/**
	 * Remove specified keys.
	 * 
	 * @param mixed $keys: string with key or array of keys
	 * @return int Number of keys deleted
	 */
	public function deleteCache($keys) {
		if($this->connected) {
			return $this->redis->delete($keys);
		}

		return false;
	}
	
	/**
	 * deletes all the keys that start with name
	 * 
	 * @param string $name
	 */
	public function deleteByMask($name) {
		if($this->connected) {
			$keys = $this->redis->getKeys($name.'*');
			$this->redis->delete($keys);
		}
	}

}
