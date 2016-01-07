<?php
namespace Core\Data;

use Symfony\Component\Serializer\Serializer;


class DataHandler
{

    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function prepare($data, $config = [], $type = 'json')
    {

        $dataType = gettype($data);

        if ('json' === $type) {
            switch ($dataType) {
                case 'object':
                    $data = $this->serializer->serialize($data, 'json', $config);
                    break;
                case 'array':
                    $data = json_encode($data, JSON_FORCE_OBJECT);
                    break;
                case 'boolean':
                    $data = json_encode($data, JSON_FORCE_OBJECT);
                    break;
                case 'string':
                    $data = json_encode($data, JSON_FORCE_OBJECT);
                    break;
                default:
                    $data = json_encode($data, JSON_FORCE_OBJECT);
                    break;
            }
        }

        return $data;
    }
}