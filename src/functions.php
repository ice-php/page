<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 返回一个分页实例
 * @return Page
 */
function page()
{
    return Page::instance();
}