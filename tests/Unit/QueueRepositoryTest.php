<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Wearesho\Yii\Guzzle;
use yii\queue;

class QueueRepositoryTest extends TestCase
{
    public function dataProvider(): array
    {
        $request = new Request('POST', 'https://zsu.gov.ua/', [
            'x-donate-to' => 'Come Back Alive',
        ], 'body-request');
        $response = new Response(200, [
            'x-foo' => 'bar',
        ], 'body-response');
        $exception = new \Exception("Test Error");

        $factory = new Guzzle\Log\Factory();

        return [
            [$request, $response, $exception, new Guzzle\Log\Job(
                $factory->fromRequest($request),
                $factory->fromResponse($response),
                $factory->fromException($exception)
            )],
            [$request, $response, null, new Guzzle\Log\Job(
                $factory->fromRequest($request),
                $factory->fromResponse($response),
                null,
            )],
            [$request, null, $exception, new Guzzle\Log\Job(
                $factory->fromRequest($request),
                null,
                $factory->fromException($exception),
            )],
            [$request, null, null, new Guzzle\Log\Job(
                $factory->fromRequest($request),
                null,
                null,
            )],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSave(
        Request $request,
        ?Response $response,
        ?\Exception $exception,
        Guzzle\Log\Job $expectedJob
    ): void {
        $queue = $this->createMock(queue\Queue::class);
        $queue
            ->expects($this->once())
            ->method('push')
            ->with($expectedJob);

        $repository = new Guzzle\Log\QueueRepository([
            'queue' => $queue,
        ]);
        $repository->save($request, $response, $exception);
    }
}
