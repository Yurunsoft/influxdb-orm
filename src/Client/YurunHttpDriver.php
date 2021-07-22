<?php

namespace Yurun\InfluxDB\ORM\Client;

use InfluxDB\Driver\DriverInterface;
use InfluxDB\Driver\Exception;
use InfluxDB\Driver\QueryDriverInterface;
use Yurun\Util\HttpRequest;

class YurunHttpDriver implements DriverInterface, QueryDriverInterface
{
    /**
     * Array of options.
     *
     * @var array
     */
    private $parameters;

    /**
     * 响应对象
     *
     * @var \Yurun\Util\YurunHttp\Http\Response
     */
    private $response;

    /**
     * 基础地址
     *
     * @var string
     */
    private $baseUri;

    public function __construct(string $baseUri)
    {
        $this->baseUri = $baseUri . '/';
    }

    /**
     * Called by the client write() method, will pass an array of required parameters such as db name.
     *
     * will contain the following parameters:
     *
     * [
     *  'database' => 'name of the database',
     *  'url' => 'URL to the resource',
     *  'method' => 'HTTP method used'
     * ]
     *
     * @return mixed
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Send the data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function write($data = null)
    {
        $this->response = $this->getHttpRequest()->post($this->baseUri . $this->parameters['url'], $data);
    }

    /**
     * Should return if sending the data was successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        $statuscode = $this->response->getStatusCode();

        if (!\in_array($statuscode, [200, 204], true))
        {
            throw new Exception('HTTP Code ' . $statuscode . ' ' . $this->response->getBody());
        }

        return true;
    }

    /**
     * @return ResultSet
     */
    public function query()
    {
        $response = $this->getHttpRequest()->get($this->baseUri . $this->parameters['url']);

        $raw = (string) $response->getBody();

        return $this->asResultSet($raw);
    }

    /**
     * 获取请求对象
     *
     * @return \Yurun\Util\HttpRequest
     */
    protected function getHttpRequest()
    {
        $request = new HttpRequest();
        if ($auth = ($this->parameters['auth'] ?? null))
        {
            $request->userPwd(...$auth);
        }

        return $request;
    }

    /**
     * @return ResultSet
     *
     * @throws \InfluxDB\Client\Exception
     */
    protected function asResultSet(string $raw)
    {
        return new ResultSet($raw);
    }
}
