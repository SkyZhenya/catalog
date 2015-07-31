<?php
namespace Application\Lib;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface; 

class Redis implements ServiceLocatorAwareInterface {
	
	/**
	 * @var \Redis
	 */
	protected $redis;
	protected $namespace;
	protected $connected = false;
	protected $serviceLocator;
	protected $config = [];
	const MAX_TRIES = 10;

	/**
	 * Connects to redis daemon
	 *
	 */
	protected function connect() {
		$res = $this->redis->pconnect($this->config['host']);
		$this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
		$this->redis->select($this->config['db']);
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
		if(!$this->config['enabled']) return;

		if($try > self::MAX_TRIES) {
			return false;
		}
		
		if($ttl) {
			$ret = $this->redis->setex($this->config['namespace'].$key, $ttl, $value);
		}
		else {
			$ret = $this->redis->set($this->config['namespace'].$key, $value);
		}
		
		if(!$ret) {
			$this->connect();
			$this->set($this->config['namespace'].$key, ($value), $ttl, $try+1);
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
		if(!$this->config['enabled']) return;

		$ret = $this->redis->get($this->config['namespace'].$key);
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
		if(!$this->config['enabled']) return;

		$oldHandler = set_error_handler('\Application\Lib\Redis::myHandler');
		if(is_array($keys)) foreach($keys as $key => $value) {
			$keys[$key] = $this->config['namespace'].$value;
		}
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
			if(is_array($keys)) foreach($keys as $key => $value) {
				$keys[$key] = $this->config['namespace'].$value;
			}
			else {
				$keys = $this->config['namespace'] . $keys;
			}
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
			$keys = $this->redis->getKeys($this->config['namespace'].$name.'*');
			$this->redis->delete($keys);
		}
	}

	/**
	 * Set serviceManager instance
	 *
	 * @param  ServiceLocatorInterface $serviceLocator
	 * @return void
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;

		$this->config = $serviceLocator->get('Application\Config')['redis'];
		if($this->config['enabled']) {
			$this->redis = new \Redis();
			$this->connect();
		}

	}

	/**
	 * Retrieve serviceManager instance
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

}
