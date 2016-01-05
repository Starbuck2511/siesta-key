<?php

namespace Core\Error;

use Symfony\Component\Validator\ConstraintViolationList;

class ErrorHandler
{
    private $errorProcessor;

    public function __construct(ErrorProcessorInterface $errorProcessor)
    {
        $this->errorProcessor = $errorProcessor;
    }

    public function handle($error)
    {
        $data = [];

        if (is_array($error)) {
            $data = $this->handleArray($error);
        }

        if ($error instanceof ConstraintViolationList) {
            $data = $this->handleConstraintViolationList($error);
        }

        return $data;
    }

    private function handleArray(Array $error)
    {
        return $this->errorProcessor->processErrors($error);
    }

    private function handleConstraintViolationList(ConstraintViolationList $violationList)
    {
        $errors = [];
        for ($i = 0; $i < $violationList->count(); $i++) {

            $errors['message'] = $violationList[$i]->getMessage();
            $errors['field'] = $violationList[$i]->getPropertyPath();
            $errors['code'] = $violationList[$i]->getCode();
        }

        return $this->errorProcessor->processErrors($errors);
    }
}