<?php
/**
 * Database Cache Adapter for SimpleCache
 */
namespace Wind\Cache;

use Wind\Db\Connection;
use Wind\Db\Db;
use function Amp\call;

class DbCache implements \Psr\SimpleCache\CacheInterface
{

    /**
     * Cache table name
     *
     * @var string
     */
    private $table;

    /**
     * Database connection
     *
     * @var Connection
     */
    private $db;

    public function __construct() {
        $config = config('cache.db', []);
        $this->table = isset($config['table']) ? $config['table'] : 'cache';
        $this->db = isset($config['connection']) ? Db::connection($config['connection']) : Db::connection();
    }

    private function query()
    {
        return $this->db->table($this->table);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null) {
        return call(function() use ($key, $default) {
            $data = yield $this->query()->where(['key'=>$key])->fetchOne();

            if ($data) {
                if ($data['expired_at'] > time() || $data['expired_at'] == 0) {
                    return unserialize($data['value']);
                } else {
                    yield $this->delete($key);
                }
            }

            return $default;
        });
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null) {
        return $this->query()->replace([
            'key' => $key,
            'value' => serialize($value),
            'created_at' => time(),
            'expired_at' => $ttl !== null ? time() + $ttl : 0
        ]);
    }

    /**
     * @inheritDoc
     */
    public function delete($key) {
        return $this->query()->where(['key' => $key])->delete();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null) {
        return call(function() use ($keys, $default) {
            $rows = yield $this->query()->where(['key'=>$keys])->indexBy('key')->fetchAll();

            $data = [];
            foreach ($keys as $k) {
                if (isset($rows[$k])) {
                    $v = $rows[$k];
                    if ($v['expired_at'] > time() || $v['expired_at'] == 0) {
                        $data[$k] = unserialize($v['value']);
                    } else {
                        yield $this->delete($k);
                        $data[$k] = $default;
                    }
                } else {
                    $data[$k] = $default;
                }
            }
            return $data;
        });
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null) {
        return call(function() use ($values, $ttl) {
            foreach ($values as $k => $v) {
                yield $this->set($k, $v, $ttl);
            }
            return true;
        });
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys) {
        return $this->query()->where(['key'=>$keys])->delete();
    }

    /**
     * @inheritDoc
     */
    public function has($key) {
        return call(function() use ($key) {
            $expiredAt = yield $this->query()->select('expired_at')->where(['key'=>$key])->scalar();
            if ($expiredAt !== null) {
                if ($expiredAt == 0 || $expiredAt > time()) {
                    return true;
                } else {
                    yield $this->delete($key);
                }
            }
            return false;
        });
    }

    public function clear() {
        $table = $this->db->prefix($this->table);
        return $this->db->execute("TURNCATE TABLE `$table`");
    }
}

