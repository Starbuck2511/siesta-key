<?php
namespace Core\Error;

interface ErrorProcessorInterface
{
    public function processErrors(Array $errors);

}