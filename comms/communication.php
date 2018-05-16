<?php namespace SlackClient;

require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;

class communication {
	/**
	 * Sends a CURL request to the Slack API, using application/x-www-form-urlencoded.
	 * @param string $token Your app token for accessing API features.
	 * @param string $slackapi Which api segment you're calling (e.g. post.update)
	 * @param array $args 
	 * @param string $type HTTP request type
	 */
	public function sendRequest($token, $slackapi, $args = [], $type = "POST") {
		$args['token'] = $token;
		$client   = new Client(['base_uri' => 'https://slack.com/api/']);
		$response = $client->request($type, $slackapi, [
			'form_params' => $args
		]);
		
		$result = json_decode((string)$response->getBody(), true);
		
		if ($result['ok'] == false) {
			error_log($result['error']);
			var_dump($result['error']);
			return false;
		} else {
			return $result;
		}
	}
}