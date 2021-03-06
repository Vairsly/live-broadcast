<?php

namespace App\Base;

use App\Utility\Pool\MysqlPool;
use App\Utility\Pool\MysqlObject;
use EasySwoole\Component\Di;
use EasySwoole\ORM\AbstractModel;



abstract class BaseModel extends AbstractModel
{
	protected $db;
	protected $table = 'admin_auth';
    private static $instance=[];

    static function getInstance(...$args)
    {
        $obj_name = static::class;
        if(!isset(self::$instance[$obj_name])){
            self::$instance[$obj_name] = new static(...$args);
        }
        return self::$instance[$obj_name];
    }

    /**
     * @param $filedName
     * @param $value
     * @param array $where
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function setValue($filedName, $value, $where = [])
    {
        return self::create()->update([$filedName=> $value], $where);
    }

    /**
     * 新增数据
     * @param array $data
     * @return bool|int
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function insert($data = [])
    {
        return self::create($data)->save();
    }

    /**
     * @param $options
     * @return BaseModel|array|bool|AbstractModel|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function find($options)
    {
        if (!is_array($options)) {
            $options = ['id' => $options];
        }
        return self::create()->where($options)->get();
    }

    /**
     * 设置排序
     * @param mixed ...$args
     * @return AbstractModel
     */
    public function orderBy(...$args)
    {
        return parent::order($args); // TODO: Change the autogenerated stub
    }
}