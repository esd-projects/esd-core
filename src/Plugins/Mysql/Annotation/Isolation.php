<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/10
 * Time: 10:12
 */

namespace ESD\Plugins\Mysql\Annotation;

/**
 * 事务隔离
 * Class Isolation
 * @package ESD\Plugins\Mysql\Annotation
 */
class Isolation
{
    /**
     * 这是默认值，表示使用底层数据库的默认隔离级别。
     * 对大部分数据库而言，通常这值就是： READ_COMMITTED 。
     */
    const DEFAULT = "DEFAULT";
    /**
     * 该隔离级别表示一个事务可以读取另一个事务修改但还没有提交的数据。
     * 该级别不能防止脏读和不可重复读，因此很少使用该隔离级别。
     */
    const READ_UNCOMMITTED = "READ_UNCOMMITTED";
    /**
     * 该隔离级别表示一个事务只能读取另一个事务已经提交的数据。
     * 该级别可以防止脏读，这也是大多数情况下的推荐值。
     */
    const READ_COMMITTED = "READ_COMMITTED";
    /**
     * 该隔离级别表示一个事务在整个过程中可以多次重复执行某个查询，并且每次返回的记录都相同。
     * 即使在多次查询之间有新增的数据满足该查询，这些新增的记录也会被忽略。该级别可以防止脏读和不可重复读。
     */
    const REPEATABLE_READ = "REPEATABLE_READ";
    /**
     * 所有的事务依次逐个执行，这样事务之间就完全不可能产生干扰，也就是说，该级别可以防止脏读、不可重复读以及幻读。
     * 但是这将严重影响程序的性能。通常情况下也不会用到该级别。
     */
    const SERIALIZABLE = "SERIALIZABLE";
}