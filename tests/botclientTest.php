<?php
namespace SlackClientTest;

use SlackClient\BotClient;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use \PHPUnit\Framework\TestCase;

/**
 * Kebabble mentions test case.
 */
class BotClientTest extends TestCase
{
	/* TODO */
	public function testFullCycularApproach()
	{
		$json_dir = __DIR__ . '/json';
		$client   = new BotClient();
		$client->connect(
			'xorg-abc-0123456789',
			'#general',
			HandlerStack::create(
				new MockHandler([
					new Response(200, [], file_get_contents("{$json_dir}/channels.json")),
					new Response(200, [], file_get_contents("{$json_dir}/postmessage.json")),
					new Response(200, [], file_get_contents("{$json_dir}/update.json")),
					new Response(200, [], "{\"ok\": true}"),
					new Response(200, [], file_get_contents("{$json_dir}/delete.json")),
				])
			)
		);
		$resp_01 = $client->message('This is a test message.');
		$resp_02 = $client->message('This is an updated message.', $resp_01);
		$resp_03 = $client->pin($resp_02);
		$resp_04 = $client->deleteMessage($resp_02);

		$this->assertIsBool($resp_04);
	}
}
