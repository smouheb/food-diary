<?php
/**
 * Created by PhpStorm.
 * User: MacBookAir
 * Date: 24/07/2018
 * Time: 05:38
 */

namespace test\AppBundle\Security;


use AppBundle\Security\GithubUserProvider;
use PHPUnit\Framework\TestCase;
use AppBundle\Entity\User;

class GithubUserProviderTest extends TestCase
{
    private $client;
    private $serialize;
    private $response;
    private $streamdedresponse;

    public function setUp()
    {
        $this->client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->serialize = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->response =  $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();

        $this->streamdedresponse = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

    }

    public function testLoadUserByUsernameReturningAUser()
    {
        //Tester le return ainsi que l'exception

        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamdedresponse);

        $userData = ['login' => 'a login',
                     'name' => 'user name',
                     'email' => 'adress@mail.com',
                     'avatar_url' => 'url to the avatar',
                     'html_url' => 'url to profile'
                    ];

        $this->serialize
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn($userData);

        $githubuserprovider = new GithubUserProvider($this->client, $this->serialize);
        $user = $githubuserprovider->loadUserByUsername('an-access-token');

        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);

        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('AppBundle\Entity\User', get_class($user));


    }

    public function testNegativeUserByUsernameReturningAUser()
    {

        $this->client
             ->expects($this->once())
             ->method('get')
             ->willReturn($this->response);

        $this->response
             ->expects($this->once())
             ->method('getBody')
             ->willReturn($this->streamdedresponse);


        $this->serialize
             ->expects($this->once())
             ->method('deserialize')
             ->willReturn([]);

        $this->expectException('LogicException');
        $githubuserprovider = new GithubUserProvider($this->client, $this->serialize);
        $githubuserprovider->loadUserByUsername('an-access-token');

    }

    public function tearDown()
    {
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;
    }

}