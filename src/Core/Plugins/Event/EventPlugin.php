<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace ESD\Core\Plugins\Event;


use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;
use ESD\Core\Message\Message;
use ESD\Core\Message\MessageProcessor;
use ESD\Core\PlugIn\AbstractPlugin;

/**
 * Event 插件加载器
 * Class EventPlug
 * @package ESD\Core\Plugins\Event
 */
class EventPlugin extends AbstractPlugin
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws \Exception
     */
    public function init(Context $context)
    {
        parent::init($context);
        //创建事件派发器
        $this->eventDispatcher = DIGet(EventDispatcher::class);
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        class_exists(MessageProcessor::class);
        class_exists(EventMessageProcessor::class);
        DIGet(EventCall::class, [$this->eventDispatcher, ""]);
        DIGet(Channel::class);
        class_exists(Message::class);
        return;
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @throws \ESD\Core\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        //注册事件派发处理函数
        MessageProcessor::addMessageProcessor(new EventMessageProcessor($this->eventDispatcher));
        //ready
        $this->ready();
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Event";
    }
}