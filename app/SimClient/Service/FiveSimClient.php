<?php
declare(strict_types=1);

namespace App\SimClient\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Kernel\Exception\RuntimeException;

class FiveSimClient
{
    private Client $client;

    public function __construct(private readonly string $token)
    {
        if ($token === '') {
            throw new RuntimeException('请先配置 5sim Token');
        }

        $this->client = new Client([
            'base_uri' => 'https://5sim.net/v1/',
            'timeout' => 30,
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ],
            'verify' => true,
        ]);
    }

    /**
     * @throws RuntimeException
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $uri, $options);
            return json_decode((string)$response->getBody(), true) ?? [];
        } catch (GuzzleException $e) {
            throw new RuntimeException('请求 5sim 接口失败：' . $e->getMessage());
        }
    }

    /**
     * @throws RuntimeException
     */
    public function buyActivation(string $country, string $operator, string $product): array
    {
        return $this->request('GET', sprintf('user/buy/activation/%s/%s/%s', $country, $operator, $product));
    }

    /**
     * @throws RuntimeException
     */
    public function checkOrder(string $orderId): array
    {
        return $this->request('GET', "user/check/{$orderId}");
    }

    /**
     * @throws RuntimeException
     */
    public function cancelOrder(string $orderId): array
    {
        return $this->request('GET', "user/cancel/{$orderId}");
    }
}
