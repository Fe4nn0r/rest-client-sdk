<?php

namespace Mapado\RestClientSdk\Client;

use Mapado\RestClientSdk\SdkClient;

/**
 * Class AbstractClient
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
abstract class AbstractClient
{
    /**
     * sdk
     *
     * @var RestClient
     * @access protected
     */
    protected $restClient;

    /**
     * sdk
     *
     * @var SdkClient
     * @access private
     */
    protected $sdk;

    /**
     * __construct
     *
     * @param RestClient
     * @access public
     */
    public function __construct(SdkClient $sdk)
    {
        $this->sdk = $sdk;
        $this->restClient = $this->sdk->getRestClient();
    }

    /**
     * find
     *
     * @param string $id
     * @access public
     * @return object
     */
    public function find($id)
    {
        $mapping = $this->sdk->getMapping();
        $key = $mapping->getKeyFromClientName(get_called_class());
        $modelName = $mapping->getModelName($key);

        // add slash if needed to have a valid hydra id
        if (!strstr($id, '/')) {
            $id = $key . '/' . $id;

            if ($prefix = $mapping->getIdPrefix()) {
                $id = $prefix . '/' . $id;
            }
        }

        $data = $this->restClient->get($id);

        return $this->deserialize($data, $modelName);
    }

    /**
     * findAll
     *
     * @access public
     * @return array
     */
    public function findAll()
    {
        $mapping = $this->sdk->getMapping();
        $prefix = $mapping->getIdPrefix();
        $key = $mapping->getKeyFromClientName(get_called_class());
        $data = $this->restClient->get('/' . $prefix . '/' . $key);

        if ($data && !empty($data['hydra:member'])) {
            $serializer = $this->sdk->getSerializer();

            $modelName = $this->sdk->getMapping()->getModelName($key);

            $list = [];
            if (!empty($data) && !empty($data['hydra:member'])) {
                foreach ($data['hydra:member'] as $instanceData) {
                    $list[] = $this->deserialize($instanceData, $modelName);
                }
            }

            return $list;
        }
        return [];
    }

    /**
     * remove
     *
     * @param object $model
     * @access public
     * @return void
     */
    public function remove($model)
    {
        return $this->restClient->delete($model->getId());
    }

    /**
     * update
     *
     * @param object $model
     * @access public
     * @return void
     */
    public function update($model)
    {
        $key = $this->sdk->getMapping()->getKeyFromClientName(get_called_class());
        $modelName = $this->sdk->getMapping()->getModelName($key);

        $data = $this->restClient->put($model->getId(), $this->sdk->getSerializer()->serialize($model, $modelName));

        return $this->deserialize($data, $modelName);
    }

    /**
     * persist
     *
     * @param object $model
     * @access public
     * @return void
     */
    public function persist($model)
    {
        $prefix = $this->sdk->getMapping()->getIdPrefix();
        $key = $this->sdk->getMapping()->getKeyFromClientName(get_called_class());
        $modelName = $this->sdk->getMapping()->getModelName($key);

        $path = $prefix . '/' . $key;
        $data = $this->restClient->post($path, $this->sdk->getSerializer()->serialize($model, $modelName));

        $modelName = $this->sdk->getMapping()->getModelName($key);

        return $this->deserialize($data, $modelName);
    }

    /**
     * deserialize
     *
     * @param array $data
     * @param string $modelName
     * @access private
     * @return object
     */
    private function deserialize($data, $modelName)
    {
        if (!$data) {
            return null;
        }

        return $this->sdk->getSerializer()->deserialize($data, $modelName);
    }
}
