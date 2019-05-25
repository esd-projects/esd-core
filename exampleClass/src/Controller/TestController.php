<?php


namespace ESD\ExampleClass\Controller;


use ESD\Plugins\EasyRoute\Annotation\Controller;
use ESD\Plugins\EasyRoute\Annotation\RequestMapping;
use ESD\Plugins\EasyRoute\Annotation\RequestParam;
use ESD\Plugins\EasyRoute\Annotation\RequestRawJson;
use ESD\Plugins\EasyRoute\Controller\EasyController;
use ESD\Plugins\Mysql\GetMysql;
use ESD\Plugins\Redis\GetRedis;

/**
 * Class TestController
 * @package ESD\ExampleClass\Controller
 * @Controller("/test")
 */
class TestController extends EasyController
{
    use GetMysql;

    use GetRedis;

    /**
     * @RequestMapping("/hello", method={"post","get"})
     * @RequestParam("id")
     * @param $id
     * @throws \ESD\Plugins\Mysql\MysqlException
     */
    public function hello($id)
    {
        var_dump($id);

        goWithContext(function () {
            //var_dump($this->redis()->get("test"));
        });
        $result = $this->mysql()->query("show tables");
        $this->response->end(json_encode($result));
    }

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     */
    protected function defaultMethod(?string $methodName)
    {
        // TODO: Implement defaultMethod() method.
    }
}