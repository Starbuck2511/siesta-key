<?php
namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationRestHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message =  $exception->getMessage();
        $error['error'] = $message;
        $data = json_encode($error, JSON_FORCE_OBJECT);
        $response = new Response(null, Response::HTTP_UNAUTHORIZED);
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {

        $apiKey = $token->getUser()->getApiKey();
        $userId = $token->getUser()->getId();
        $userName = $token->getUser()->getUsername();
        $email = $token->getUser()->getEmail();


        $data['authToken'] = $apiKey;
        $data['userid'] = $userId;
        $data['username'] = $userName;
        $data['email'] = $email;


        $data = json_encode($data, JSON_FORCE_OBJECT);
        $response = new Response(null, Response::HTTP_OK);
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }
}