<?php

namespace Sarhan\OAuth2\Client\Test\Provider;

use Sarhan\OAuth2\Client\Provider\DocusignUser;
use League\OAuth2\Client\Token\AccessToken;

class DocusignUserTest extends \PHPUnit\Framework\TestCase
{
    private $token;
    private $user;
    private $userInfo = [
        'sub' => '564f7988-0823-409a-ac8a',
        'name' => 'Example J Smith',
        'given_name' => 'Example',
        'family_name' => 'Smith',
        'created' => '2018-04-13T22:03:03.45',
        'email' => 'Example.Smith@exampledomain.com',
        'accounts' => [
            [
                'account_id' => '18b4799a-b53a-4475-ba4d-b5b4b8a97604',
                'is_default' => false,
                'account_name' => 'ExampleAccount1',
                'base_uri' => 'https://demo.docusign.net/account1'
            ],
            [
                'account_id' => '18b4799a-b53a-4475-ba4d-b5b4b8a97999',
                'is_default' => true,
                'account_name' => 'ExampleAccount2',
                'base_uri' => 'https://demo.docusign.net/account2'
            ]
        ]
    ];

    public function setUp(): void
    {
        $this->token = new AccessToken(
            ['access_token' => 'YTdhNWQ1NzUtY2E0Yy00ZmUxLThkMDAtYzZ']
        );
        $this->user = new DocusignUser($this->userInfo, $this->token);
    }

    public function testGetId()
    {
        $this->assertEquals('564f7988-0823-409a-ac8a', $this->user->getId());
    }

    public function testToArray()
    {
        $this->assertEquals($this->userInfo, $this->user->toArray());
    }

    public function testGetName()
    {
        $this->assertEquals('Example J Smith', $this->user->getName());
    }

    public function testGetEmail()
    {
        $this->assertEquals('Example.Smith@exampledomain.com', $this->user->getEmail());
    }

    public function testGetDefaultAccount()
    {
        $this->assertEquals($this->userInfo['accounts'][1], $this->user->getDefaultAccount());
    }

    public function testGetToken()
    {
        $this->assertEquals($this->token, $this->user->getToken());
    }
}
