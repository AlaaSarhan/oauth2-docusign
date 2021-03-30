<?php

namespace Sarhan\OAuth2\Client\Test\Provider;

use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sarhan\OAuth2\Client\Provider\Docusign;
use Sarhan\OAuth2\Client\Test\Double\ResponseDouble;

class DocusignTest extends \PHPUnit\Framework\TestCase
{
    private $options = [
        'clientId' => '7c2b8d7e-83c3-4940-af5e',
        'clientSecret' => 'd7014634-3919-46f6-b766',
        'redirectUri' => 'http://localhost/return'
    ];
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
    private $provider;
    private $token;
    private $basicAuth;
    private $requestProphecy;
    private $responseProphecy;

    public function getUrlDataProvider()
    {
        return [
            [
                new Docusign(['sandbox' => false]),
                'path/to/endpoint',
                'https://account.docusign.com/oauth/path/to/endpoint'
            ],
            [
                new Docusign(['sandbox' => true]),
                'path/to/endpoint',
                'https://account-d.docusign.com/oauth/path/to/endpoint'
            ]
        ];
    }

    protected function setUp(): void
    {
        $this->provider = new Docusign($this->options);
        $this->token = new AccessToken(
            ['access_token' => 'YTdhNWQ1NzUtY2E0Yy00ZmUxLThkMDAtYzZ']
        );
        $this->basicAuth = 'Basic ' . base64_encode(
            '7c2b8d7e-83c3-4940-af5e:d7014634-3919-46f6-b766'
        );
    }

    private function setupHttpClient()
    {
        $this->requestProphecy = $this->prophesize(RequestInterface::class);

        $this->responseProphecy = $this->prophesize(ResponseInterface::class);
        $this->responseProphecy->getBody()->willReturn('{}');
        $this->responseProphecy->getHeader('content-type')->willReturn('json');
        $this->responseProphecy->getStatusCode()->willReturn(200);

        $client = $this->prophesize(ClientInterface::class);
        $requestArgument = \Prophecy\Argument::type(RequestInterface::class);
        $client->send($requestArgument)->willReturn($this->responseProphecy);

        $this->provider->setHttpClient($client->reveal());
    }

    /**
     * @dataProvider getUrlDataProvider
     */
    public function testGetUrl($provider, $path, $expectedUrl)
    {
        $this->assertEquals($expectedUrl, $provider->getUrl($path));
    }

    public function testGetBaseAuthorizationUrl()
    {
        $this->assertStringEndsWith(
            'auth',
            $this->provider->getBaseAuthorizationUrl()
        );
    }

    public function testGetBaseAccessTokenUrl()
    {
        $this->assertStringEndsWith(
            'token',
            $this->provider->getBaseAccessTokenUrl([])
        );
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        $this->assertStringEndsWith(
            'userinfo',
            $this->provider->getResourceOwnerDetailsUrl($this->token)
        );
    }

    public function testAuthorizationHeaders()
    {
        $authHeaders = $this->provider->getHeaders()['Authorization'];
        $this->assertEquals($this->basicAuth, $authHeaders);
    }

    public function testDefaultScopes()
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        $parsedUrl = parse_url($authorizationUrl);
        parse_str($parsedUrl['query'], $params);

        $this->assertEquals(
            $params['scope'],
            'signature extended'
        );
    }

    public function testCheckResponseDoesNotThrowOnSuccess()
    {
        $this->setupHttpClient();

        $this->assertNotNull(
            $this->provider->getParsedResponse($this->requestProphecy->reveal())
        );
    }

    public function testCheckResponseThrowsExceptionOnFailure()
    {
        $this->setupHttpClient();
        $this->responseProphecy->getStatusCode()->willReturn(400);
        $this->responseProphecy->getReasonPhrase()->willReturn('Bad Request');

        $this->expectException(IdentityProviderException::class);

        $this->provider->getParsedResponse($this->requestProphecy->reveal());
    }

    public function testCreateResourceOwner()
    {
        $this->setupHttpClient();
        $this->responseProphecy->getBody()->willReturn(json_encode($this->userInfo));

        $resourceOwner = $this->provider->getResourceOwner($this->token);

        $this->assertEquals($this->userInfo, $resourceOwner->toArray());
    }
}
