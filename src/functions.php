<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 返回一个分页实例
 * @param $size int 每页行数
 * @param $order string 排序依据字段
 * @param $dir string 排序方向
 * @return Page
 */
function page($size = null, $order = 'id', $dir = 'desc')
{
    return Page::instance($size, $order, $dir);
}