<?php

namespace Vencax;

use Nette;
use Exception;

class GoogleLogin extends BaseLogin
{

    const SOCIAL_NAME = "google";

    /** @var Google_Client */
    private $client;

    /** @var array scope */
    private $scope = array();

    /**
     * Google
     * @param $params array - data from config.neon
     * @param $cookieName String cookie name
     * @param Nette\Http\Response $httpResponse
     * @param Nette\Http\Request $httpRequest
     */
    public function __construct( $params, $cookieName, Nette\Http\Response $httpResponse, Nette\Http\Request $httpRequest )
    {
        $this->params = $params;
        $this->cookieName = $cookieName;
        $this->httpResponse = $httpResponse;
        $this->httpRequest = $httpRequest;

        $this->client = new \Google_Client();

        $this->client->setClientId( $this->params["clientId"] );
        $this->client->setClientSecret( $this->params["clientSecret"] );
        $this->client->setRedirectUri( $this->params["callbackURL"] );
    }


    /**
     * Set scope
     * @param array $scope
     */
    public function setScope( array $scope )
    {
        $this->scope = $scope;
    }

    /**
     * Get URL for login
     * @return string
     */
    public function getLoginUrl()
    {
        $this->client->setScopes( $this->scope );

        return $this->client->createAuthUrl();
    }

    /**
     * Return info about login user
     * @param $code
     * @return \Google_Service_Oauth2_Userinfoplus
     * @throws Exception
     */
    public function getMe( $code )
    {
        $google_oauthV2 = new \Google_Service_Oauth2( $this->client );

        try
        {
            $this->client->authenticate( $code );
            $user = $google_oauthV2->userinfo->get();
        }
        catch( \Google_Auth_Exception $e )
        {
            throw new Exception( $e->getMessage() );
        }

        $this->setSocialLoginCookie( self::SOCIAL_NAME );

        return $user;
    }

    /**
     * Is user last login with this service<
     * @return bool
     */
    public function isThisServiceLastLogin()
    {
        if( $this->getSocialLoginCookie() == self::SOCIAL_NAME )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

}
