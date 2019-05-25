<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/10
 * Time: 10:05
 */

namespace ESD\Plugins\Mysql\Aspect;


use ESD\Core\Channel\Channel;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Mysql\Annotation\Isolation;
use ESD\Plugins\Mysql\Annotation\Propagation;
use ESD\Plugins\Mysql\Annotation\Transactional;
use ESD\Plugins\Mysql\GetMysql;
use ESD\Plugins\Mysql\TransactionException;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

class MysqlAspect extends OrderAspect
{
    use GetMysql;

    /**
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(ESD\Plugins\Mysql\Annotation\Transactional)")
     * @return mixed
     * @throws \Throwable
     */
    public function aroundTransactional(MethodInvocation $invocation)
    {
        $transactional = $invocation->getMethod()->getAnnotation(Transactional::class);
        if ($transactional instanceof Transactional) {
            $db = $this->mysql($transactional->name);
            $_transaction_in_progress = $db->isTransactionInProgress();
            $needNewGo = false;
            $needTransaction = false;
            switch ($transactional->propagation) {
                case Propagation::REQUIRED:
                    /**
                     * 如果当前存在事务，则加入该事务；
                     * 如果当前没有事务，则创建一个新的事务。
                     */
                    if (!$_transaction_in_progress) {
                        $needTransaction = true;
                    }
                    break;
                case Propagation::SUPPORTS:
                    /**
                     * 如果当前存在事务，则加入该事务；
                     * 如果当前没有事务，则以非事务的方式继续运行。
                     */
                    //不需要做任何操作
                    break;
                case Propagation::MANDATORY:
                    /**
                     * 如果当前存在事务，则加入该事务；
                     * 如果当前没有事务，则抛出异常。
                     */
                    if (!$_transaction_in_progress) {
                        throw new TransactionException("Propagation::MANDATORY传播模式下当前没有事务");
                    }
                    break;
                case Propagation::REQUIRES_NEW:
                    /**
                     * 创建一个新的事务，如果当前存在事务，则把当前事务挂起。
                     */
                    $needNewGo = true;
                    $needTransaction = true;
                    break;
                case Propagation::NOT_SUPPORTED:
                    /**
                     * 以非事务方式运行，如果当前存在事务，则把当前事务挂起。
                     */
                    if ($_transaction_in_progress) {
                        $needNewGo = true;
                    }
                    $needTransaction = false;
                    break;
                case Propagation::NEVER:
                    /**
                     * 以非事务方式运行，如果当前存在事务，则抛出异常。
                     */
                    $needTransaction = false;
                    if ($_transaction_in_progress) {
                        throw new TransactionException("Propagation::NEVER传播模式下当前不能存在事务");
                    }
                    break;
                case Propagation::NESTED:
                    throw new TransactionException("Propagation::NESTED 暂不支持");
                    break;
                default:
                    throw new TransactionException("propagation设置不正确");
            }
            if ($needNewGo) {
                //需要创建一个新的协程来执行mysql
                $channel = DIGet(Channel::class);
                goWithContext(function () use ($transactional, $invocation, $needTransaction, $channel) {
                    $db = $this->mysql($transactional->name);
                    try {
                        if ($needTransaction) {
                            $result = $this->startTransaction($transactional, $db, $invocation);
                        } else {
                            $result = $invocation->proceed();
                        }
                    } catch (\Throwable $e) {
                        $result = $e;
                    }
                    $channel->push($result);
                });
                $get = $channel->pop();
                if ($get instanceof \Throwable) {
                    throw $get;
                } else {
                    return $get;
                }
            } else {
                if ($needTransaction) {
                    return $this->startTransaction($transactional, $db, $invocation);
                } else {
                    return $invocation->proceed();
                }
            }
        }
    }

    /**
     * @param Transactional $transactional
     * @param \MysqliDb $db
     * @param MethodInvocation $invocation
     * @return mixed|null
     * @throws TransactionException
     * @throws \Throwable
     */
    private function startTransaction(Transactional $transactional, \MysqliDb $db, MethodInvocation $invocation)
    {
        switch ($transactional->isolation) {
            case Isolation::DEFAULT:
                $this->mysql($transactional->name)->rawQuery("set session transaction isolation level read committed;");
                break;
            case Isolation::READ_COMMITTED:
                $this->mysql($transactional->name)->rawQuery("set session transaction isolation level read committed;");
                break;
            case Isolation::READ_UNCOMMITTED:
                $this->mysql($transactional->name)->rawQuery("set session transaction isolation level read uncommitted;");
                break;
            case Isolation::REPEATABLE_READ:
                $this->mysql($transactional->name)->rawQuery("set session transaction isolation level repeatable read;");
                break;
            case Isolation::SERIALIZABLE:
                $this->mysql($transactional->name)->rawQuery("set session transaction isolation level serializable;");
                break;
            default:
                throw new TransactionException("isolation设置不正确");
        }
        $db->startTransaction();
        $result = null;
        try {
            $result = $invocation->proceed();
            $db->commit();
        } catch (\Throwable $e) {
            if ($e instanceof $transactional->rollbackFor) {
                if ($transactional->noRollbackFor != null) {
                    if (!($e instanceof $transactional->noRollbackFor)) {
                        $db->rollback();
                    }
                } else {
                    $db->rollback();
                }
            }
            throw $e;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "MysqlAspect";
    }
}