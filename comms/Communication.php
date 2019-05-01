<?php namespace SlackClient;

use GuzzleHttp\Client;

class Communication
{
	protected $token;
	protected $custom_handler;
	public function __construct($token, $custom_handler = null)
	{
		$this->token          = $token;
		$this->custom_handler = $custom_handler;
	}
	/**
	 * Sends a CURL request to the Slack API, using application/x-www-form-urlencoded.
	 * The token is already sent, so is unneeded in $args. Channel is also, if provided.
	 * @param string $token Your app token for accessing API features.
	 * @param string $slackapi Which api segment you're calling (e.g. post.update)
	 * @param array $args
	 * @param string $type HTTP request type
	 */
	public function sendRequest($slackapi, $channel, $args = [], $type = "POST")
	{
		$args['token']    = $this->token;
		$args['channel']  = $channel;
		$args['base_uri'] = 'https://slack.com/api/';
		if (isset($this->custom_handler)) {
			$args['handler'] = $this->custom_handler;
		}

		$client   = new Client($args);
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
