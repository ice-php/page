<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 系统分页类
 * 与Model,TPL配合 可实现分页
 * 方便说不上,但功能完整
 * @author Ice
 */
class Page
{

    //默认的分页尺寸
    const PAGE_SIZE = 20;

    // 页号参数名称
    const PAGE = '_page';

    // 每页尺寸参数名称
    const SIZE = '_size';

    // 排序依据参数名称
    const SORT = '_sort';

    // 排序方向参数名称
    const DIR = '_dir';

    // 以下可供开发人员在Model/Logical/View中访问
    public $page, $size, $offset, $sort, $dir, $count, $allPage;

    /**
     * 构造方法,获取当前页面传入的分页参数
     * @param $size int 每页行数
     * @param $order string 排序依据字段
     * @param $dir string 排序方向
     */
    private function __construct($size, $order, $dir)
    {
        $req = Request::instance();

        // 页号参数,默认为1,不许小于1
        $this->page = intval($req [self::PAGE]) ?: 1;
        $this->page = max($this->page, 1);

        // 从参数中取分页尺寸
        if ($req [self::SIZE]) {
            $this->size = intval($req [self::SIZE]);
        } elseif ($size) {
            // 如果没有,从参数中取
            $this->size = $size;
        } else {
            // 如果没有,默认20
            $this->size = self::PAGE_SIZE;
        }

        // 不许小于1
        $this->size = max($this->size, 1);

        // 本页起始量
        $this->offset = ($this->page - 1) * $this->size;

        // 从参数中取排序字段,默认为id
        if ($req [self::SORT]) {
            $this->sort = $req [self::SORT];
        } elseif ($order) {
            // 如果用词未指定,使用默认
            $this->sort = $order;
        } else {
            // 最后使用ID
            $this->sort = 'id';
        }

        // 从参数中取排序方向,默认升序
        if (in_array(strtolower($req [self::DIR] ?: ''), ['asc', 'desc'])) {
            $this->dir = strtolower($req [self::DIR] ?: '');
        } elseif ($dir) {
            // 如果用户未指定,使用默认值
            $this->dir = $dir;
        } else {
            // 如果没有默认值,降序
            $this->dir = 'desc';
        }
    }

    /**
     * 单例化
     * @param $size int 每页行数
     * @param $order string 排序依据字段
     * @param $dir string 排序方向
     * @return Page
     */
    public static function instance($size = null, $order = 'id', $dir = 'desc'): Page
    {
        static $handle;
        if (!$handle) {
            $handle = new self ($size, $order, $dir);
        }
        return $handle;
    }

    /**
     * 开发人员将满足条件的数据总数传入,以记录
     * 如果不传递参数,将返回之前传递的满足条件的数据总数
     *
     * @param int|mixed $count
     * @return int
     */
    public function count($count = false): int
    {
        // 如果不传递参数,返回记录总数
        if ($count === false) {
            return $this->count;
        }

        // 记录总数
        $this->count = intval($count);

        // 计算总页数
        $this->allPage = ceil($count / $this->size);

        // 调整当前页数,不能大于总页数
        $this->page = max(1, min($this->page, $this->allPage));

        return $this->count;
    }

    /**
     * 返回Limit参数
     * @return array [offset,length]
     */
    public function limit(): array
    {
        return [(($this->page ?: 1) - 1) * $this->size, $this->size];
    }

    //记录当前Module,Controller,Action
    private static $module, $controller, $action;

    /**
     * 设置当前Module,Controller,Action
     * @param string $module 模块名称
     * @param string $controller 控制器名称
     * @param string $action 动作名称
     */
    public static function setMCA(string $module, string $controller, string $action): void
    {
        self::$module = $module;
        self::$controller = $controller;
        self::$action = $action;
    }

    /**
     * 构造分页URL地址
     * @param $module string 模块名称
     * @param  $controller string 控制器名称
     * @param  $action string 动作名称
     * @param  $params array 参数
     * @return string
     */
    public function url($module = null, $controller = null, $action = null, $params = array()):string
    {
        $module=$module?:self::$module;
        $controller=$controller?:self::$controller;
        $action=$action?:self::$action;

        // 如果参数中指定了page,换成_PAGE
        if (isset ($params ['page'])) {
            $page = $params ['page'];
            unset ($params ['page']);
            $params [self::PAGE] = $page;
        } else {
            $params [self::PAGE] = $this->page;
        }

        // 调整参数中的分页尺寸参数
        if (isset ($params ['size'])) {
            $size = $params ['size'];
            unset ($params ['size']);
            $params [self::SIZE] = $size;
        } else {
            $params [self::SIZE] = $this->size;
        }

        // 调整参数中的排序依据参数
        if (isset ($params ['sort'])) {
            $sort = $params ['sort'];
            unset ($params ['sort']);
            $params [self::SORT] = $sort;
        } else {
            $params [self::SORT] = $this->sort;
        }

        // 调整参数中的排序方向参数
        if (isset ($params ['dir'])) {
            $dir = $params ['dir'];
            unset ($params ['dir']);
            $params [self::DIR] = $dir;
        } else {
            $params [self::DIR] = $this->dir;
        }

        // 把搜索条件也加上
        if ($this->where) {
            $params = array_merge($params, $this->where);
        }

        // 构造URL
        return url($module, $controller, $action, $params);
    }

    // 保存页面搜索条件
    public $where;

    /**
     * 有参数则设置搜索条件,无参数则返回搜索条件
     *
     * @param string $where
     * @return mixed
     */
    public function where($where = null)
    {
        if ($where === null) {
            return $this->where;
        }
        $this->where = $where;

        return $this->where;
    }

    /**
     * 构造 ORDER BY 子句
     * @return array
     */
    public function orderby():array
    {
        return [$this->sort, $this->dir];
    }
}