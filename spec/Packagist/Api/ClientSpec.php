<?php

namespace spec\Packagist\Api;

use PhpSpec\ObjectBehavior;

use Packagist\Api\Result\Factory;

use Http\Discovery\HttpClientDiscovery as ClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery as FactoryDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestFactoryInterface as RequestFactory;
use Psr\Http\Message\RequestInterface as Request;

class ClientSpec extends ObjectBehavior
{
    function let(HttpClient $client, Factory $factory)
    {
        $this->beConstructedWith($client, $factory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Packagist\Api\Client');
    }

    function it_search_for_packages(ClientDiscovery $hcd, FactoryDiscovery $fd, RequestFactory $rfi, Client $client, Factory $factory, Request $request, Response $response)
    {
        $data = file_get_contents('spec/Packagist/Api/Fixture/search.json');
        $response->getBody()->shouldBeCalled()->willReturn($data);

        $hcd::find()->shouldBeCalled()->willReturn($client);
        $fd::findRequestFactory()->shouldBeCalled()->willReturn($rfi);
        $rfi->createRequest('get', 'https://packagist.org/search.json?q=sylius')->shouldBeCalled()->willReturn($request);
        try {
            $client->sendRequest($request)->shouldBeCalled()->willReturn($response);
        } catch (ClientExceptionInterface $e) {
        }
        $factory->create(json_decode($data, true))->shouldBeCalled()->willReturn(array());

        $this->search('sylius');
    }

    function it_searches_for_packages_with_filters(ClientDiscovery $hcd, FactoryDiscovery $fd, RequestFactory $rfi, Client $client, Factory $factory, Response $response)
    {
        $data = file_get_contents('spec/Packagist/Api/Fixture/search.json');
        $response->getBody()->shouldBeCalled()->willReturn($data);

        $hcd::find()->shouldBeCalled()->willReturn($client);
        $fd::findRequestFactory()->shouldBeCalled()->willReturn($rfi);
        $rfi->createRequest('get', 'https://packagist.org/search.json?tag=storage&q=sylius')->shouldBeCalled()->willReturn($request);
        try {
            $client->sendRequest($request)->shouldBeCalled()->willReturn($response);
        } catch (ClientExceptionInterface $e) {
        }

        $factory->create(json_decode($data, true))->shouldBeCalled()->willReturn(array());

        $this->search('sylius', array('tag' => 'storage'));
    }

    function it_gets_popular_packages(ClientDiscovery $hcd, FactoryDiscovery $fd, RequestFactory $rfi, Client $client, Factory $factory, Response $response)
    {
        $data = file_get_contents('spec/Packagist/Api/Fixture/popular.json');
        $response->getBody()->shouldBeCalled()->willReturn($data);

        $client->request('get', 'https://packagist.org/explore/popular.json?page=1')->shouldBeCalled()->willReturn($response);

        $factory->create(json_decode($data, true))->shouldBeCalled()->willReturn(array_pad(array(), 5, null));

        $this->popular(2)->shouldHaveCount(2);
    }

    function it_gets_package_details(ClientDiscovery $hcd, FactoryDiscovery $fd, RequestFactory $rfi, Client $client, Factory $factory, Response $response)
    {
        $data = file_get_contents('spec/Packagist/Api/Fixture/get.json');
        $response->getBody()->shouldBeCalled()->willReturn($data);

        $client->request('get', 'https://packagist.org/packages/sylius/sylius.json')->shouldBeCalled()->willReturn($response);

        $factory->create(json_decode($data, true))->shouldBeCalled();

        $this->get('sylius/sylius');
    }

    function it_lists_all_package_names(ClientDiscovery $hcd, FactoryDiscovery $fd, RequestFactory $rfi, Client $client, Factory $factory, Response $response)
    {
        $data = file_get_contents('spec/Packagist/Api/Fixture/all.json');
        $response->getBody()->shouldBeCalled()->willReturn($data);

        $client->request('get', 'https://packagist.org/packages/list.json')->shouldBeCalled()->willReturn($response);

        $factory->create(json_decode($data, true))->shouldBeCalled();

        $this->all();
    }

    function it_filters_package_names_by_type(ClientDiscovery $hcd, FactoryDiscovery $fd, RequestFactory $rfi, Client $client, Factory $factory, Response $response)
    {
        $data = file_get_contents('spec/Packagist/Api/Fixture/all.json');
        $response->getBody()->shouldBeCalled()->willReturn($data);

        $client->request('get', 'https://packagist.org/packages/list.json?type=library')->shouldBeCalled()->willReturn($response);

        $factory->create(json_decode($data, true))->shouldBeCalled();

        $this->all(array('type' => 'library'));
    }

    function it_filters_package_names_by_vendor(ClientDiscovery $hcd, FactoryDiscovery $fd, RequestFactory $rfi, Client $client, Factory $factory, Response $response)
    {
        $data = file_get_contents('spec/Packagist/Api/Fixture/all.json');
        $response->getBody()->shouldBeCalled()->willReturn($data);

        $client->request('get', 'https://packagist.org/packages/list.json?vendor=sylius')->shouldBeCalled()->willReturn($response);

        $factory->create(json_decode($data, true))->shouldBeCalled();

        $this->all(array('vendor' => 'sylius'));
    }
}
