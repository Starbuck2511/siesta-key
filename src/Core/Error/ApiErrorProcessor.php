<?php

namespace Core\Error;

class ApiErrorProcessor implements ErrorProcessorInterface
{

    public function processErrors(Array $errors) {

        $error['error'] = $errors;
        return json_encode($error, JSON_FORCE_OBJECT);

    }
}