<?php

namespace Sarhan\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Docusign extends AbstractProvider
{
	use BearerAuthorizationTrait {
		getAuthorizationHeaders as getTokenBearerAuthorizationHeaders;
	}
	
	const URL_ROOT = 'https://account.docusign.com/oauth';
	const URL_ROOT_SANDBOX = 'https://account-d.docusign.com/oauth';

	const ENDPOINT_AUTHORIZTION = 'auth';
	const ENDPOINT_ACCESS_TOKEN = 'token';
	const ENDPOINT_RESOURCE_OWNER_DETAILS = 'userinfo';

	const SCOPE_SIGNATURE = 'signature';
	const SCOPE_EXTENDED = 'extended';
	const SCOPE_impersonation = 'impersonation';
	const SCOPES_DEFAULT = [
		self::SCOPE_SIGNATURE,
		self::SCOPE_EXTENDED
	];
	const SCOPES_SEPARATOR = ' ';

	protected $sandbox = false;

	/**
	 * @inheritDoc
	 */
	public function getBaseAuthorizationUrl() : string
	{
		return $this->getUrl(self::ENDPOINT_AUTHORIZTION);
	}

	/**
	 * @inheritDoc
	 */
	public function getBaseAccessTokenUrl(array $params) : string
	{
		return $this->getUrl(self::ENDPOINT_ACCESS_TOKEN);
	}

	/**
	 * @inheritDoc
	 */
	public function getResourceOwnerDetailsUrl(AccessToken $token) : string
	{
		return $this->getUrl(self::ENDPOINT_RESOURCE_OWNER_DETAILS);
	}

	public function getUrl($path)
	{
		return sprintf(
			'%s/%s',
			$this->sandbox ? self::URL_ROOT_SANDBOX : self::URL_ROOT,
			$path
		);
	}

	protected function getDefaultScopes()
	{
		return self::SCOPES_DEFAULT;
	}

	protected function checkResponse(ResponseInterface $response, $data)
	{
		if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
	}

	protected function createResourceOwner(array $response, AccessToken $token)
	{
		return new DocusignUser($response, $token);
	}

	protected function getScopeSeparator()
    {
        return self::SCOPES_SEPARATOR;
    }

    protected function getDefaultHeaders()
    {
    	return ['Authorization' => 'Basic ' . $this->getBasicAuth()];
    }

	private function getBasicAuth() : string
	{
		return base64_encode(sprintf('%s:%s', $this->clientId, $this->clientSecret));
	}
}