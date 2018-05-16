<?php namespace SlackClient;

use GuzzleHttp\Client;

class communication {
	protected $token;
	protected $channel;
	public function __construct($token, $channel = false) {
		$this->token   = $token;
		$this->channel = $channel;
	}
	/**
	 * Sends a CURL request to the Slack API, using application/x-www-form-urlencoded.
	 * The token is already sent, so is unneeded in $args. Channel is also, if provided.
	 * @param string $token Your app token for accessing API features.
	 * @param string $slackapi Which api segment you're calling (e.g. post.update)
	 * @param array $args 
	 * @param string $type HTTP request type
	 */
	public function sendRequest($slackapi, $args = [], $type = "POST") {
		$args['token'] = $this->token;
		if ($this->channel !== false) {
			$args['channel'] = $this->channel;
		}
		
		$client   = new Client(['base_uri' => 'https://slack.com/api/']);
		$response = $client->request($type, $slackapi, [
			'form_params' => $args
		]);
		
		$result = json_decode((string)$response->getBody(), true);
		
		if ($result['ok'] == false) {
			error_log($result['error']);
			return false;
		} else {
			return $result;
		}
	}
}