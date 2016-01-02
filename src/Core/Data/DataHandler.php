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

        if ('json' === $type) {
            $data = $this->serializer->serialize($data, 'json', $config);
        }

        return $data;

    }
}