<?php

namespace Sarhan\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class DocusignUser implements ResourceOwnerInterface
{
	private $userInfo;
	private $token;

	public function __construct(
		array $userInfo,
		AccessToken $token
	) {
		$this->userInfo = $userInfo;
		$this->token = $token;
	}

	/**
     * @inheritDoc
     */
    public function getId() : string
    {
    	return $this->userInfo['sub'];
    }

    /**
     * @inheritDoc
     */
    public function toArray() : array
    {
    	return $this->userInfo;
    }

    public function getName() : string
    {
    	return $this->userInfo['name'];
    }

    public function getEmail() : string
    {
    	return $this->userInfo['email'];
    }

    /**
     * Get default user account, if any exists.
     * 
     * @return array|null
     */
    public function getDefaultAccount() : ?array
    {
    	foreach ($this->userInfo['accounts'] as $account) {
    		if ($account['is_default']) {
    			return $account;
    		}
    	}

    	return null;
    }

    public function getToken() : AccessToken
    {
    	return $this->token;
    }
}