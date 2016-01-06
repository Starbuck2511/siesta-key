<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Core\Error\ErrorProcessorInterface;

class ExceptionListener
{

    private $errorProcessor;

    public function __construct(ErrorProcessorInterface $errorProcessor)
    {
        $this->errorProcessor = $errorProcessor;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        $errors['message'] = $exception->getMessage();
        $errors['code'] = $exception->getCode();

        $data = $this->errorProcessor->processErrors($errors);

            // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($data);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Send the modified response object to the event
        $event->setResponse($response);
    }
}