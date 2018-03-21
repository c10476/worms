<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/16
 * Time: 22:47
 */

namespace worms\response;

use worms\core\AppException;
use worms\core\Response;

class View extends Response
{
    /**
     * View constructor.
     *
     * @param array $options 配置
     */
    public function __construct(array $options = [])
    {
        $options = array_merge([
            'core_path' => __DIR__ . '/view',
            'index'     => '',
            'twig'      => [
                'debug' => true,
            ],
        ], $options);
        parent::__construct($options);
        $this->contentType('text/html');
        $this->setPath();
    }

    /**
     * @desc   error
     * @author storn
     *
     * @param string $msg   错误描述
     * @param string $errno 错误码
     * @param mixed  $data  其他
     */
    public function error($msg, $errno, $data = null)
    {
        $loader = new \Twig_Loader_Filesystem($this->options['core_path']);
        $twig   = new \Twig_Environment($loader, $this->options['twig']);
        $this
            ->set('errno', $errno)
            ->set('msg', $msg)
            ->set('data', $data);
        $this->content = $twig->render('error.twig', $this->data);
        $this->send();
    }

    /**
     * @desc   exception 异常输出
     * @author storn
     *
     * @param \Exception $e
     */
    public function exception(\Throwable $e)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/view');
        $twig   = new \Twig_Environment($loader, $this->options['twig']);
        if ($e instanceof AppException) {
            $this->set('code', $e->getErrno());
        } else {
            $this->set('code', $e->getCode());
        }
        $this
            ->set('msg', $e->getMessage())
            ->set('data', array_merge(
                    ["@{$e->getFile()} +{$e->getLine()}"]
                    , explode("\n", $e->getTraceAsString()))
            );
        $this->content = $twig->render('exception.twig', $this->data);
        $this->send();
    }

    /**
     * @desc   set 渲染变量
     * @author storn
     *
     * @param string $key   key
     * @param mixed  $value 值
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @desc   setPath 设置tpl路径
     * @author storn
     *
     * @param array|string $paths 路径
     *
     * @return $this
     */
    public function setPath($paths = [])
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        $this->options(['paths' => $paths]);

        return $this;
    }

    /**
     * @desc   setIndex
     * @author storn
     *
     * @param string $index 模板索引
     *
     * @return $this
     */
    public function setIndex($index)
    {
        $this->options(['index' => $index]);

        return $this;
    }

    /**
     * @desc   parseContent
     * @author storn
     */
    protected function parseContent()
    {
        try {
            $loader        = new \Twig_Loader_Filesystem($this->options['paths']);
            $twig          = new \Twig_Environment($loader, $this->options['twig']);
            $this->content = $twig
                ->render(
                    $this->options['index'], $this->data
                );
        } catch (\Exception $e) {
            $this->error(
                $e->getMessage(),
                'tpl_missing',
                explode("\n", $e->getTraceAsString())
            );
        }
    }
}