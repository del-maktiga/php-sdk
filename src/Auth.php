<?php namespace Moota\SDK;

use Moota\SDK\Exceptions\MootaUnathorizedException;

/**
 * Class Auth
 * 
 * @method check
 * 
 * @package Moota\SDK
 */
class Auth
{
    private $mode;
    private $apiKey;
    private $basicAuth;
    private $token;
    private $util;

    public function __construct(Util $util)
    {
        $this->util = $util;
    }

    /**
     * Check Moota Authorization
     *
     * @return bool|null
     */
    public function check(bool $pleaseDie = false)
    {
        if (empty($this->mode)) {
            $this->mode = env('MOOTA_MODE', 'testing');
            $this->apiKey = env('MOOTA_API_KEY');
            $this->basicAuth = $this->util->getAuthHeader();
        }

        if (!empty($this->basicAuth)) {
            $hasBasicAuth = strpos(
                strtolower($this->basicAuth), 'basic'
            ) === 0;

            if ($hasBasicAuth) {
                list($this->token, $other) = explode(
                    ':', substr($this->basicAuth, 6)
                );

                if (
                    $this->mode == 'production'
                    && $this->apiKey == $this->token
                ) {
                    return true;
                }
            }

            if (
                $this->mode == 'testing' && $this->apiKey == $this->token
            ) {
                return true;
            }
        }

        if ( !empty( $this->token = $this->util->getApiKey() ) ) {
            if (
                $this->mode == 'production'
                && $this->apiKey == $this->token
            ) {
                return true;
            }

            if (
                $this->mode == 'testing' && $this->mode == $this->token
            ) {
                return true;
            }
        }

        if ($pleaseDie) {
            // 401: Unauthorized
            http_response_code(401);
            die("Moota SDK User is Not Authorized");
        }

        return false;
    }

    /**
     * @throws MootaUnathorizedException
     * @return bool
     */
    public function checkOrFail()
    {
        if ($this->check()) {
            return true;
        }

        throw new MootaUnathorizedException;
    }
}
