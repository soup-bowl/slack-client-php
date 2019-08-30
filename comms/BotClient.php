<?php namespace SlackClient;

use SlackClient\Communication;

class BotClient
{
	protected $token;
	protected $handler;
	protected $client;
	protected $channel;
	public function __construct($token = null, $handler = null)
	{
		if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
			require_once __DIR__ . '/../vendor/autoload.php';
		} else {
			require_once __DIR__ . '/../../../autoload.php';
		}

		if (isset($token)) {
			$this->connect($token, $handler);
		}
	}

	/**
	 * Connects to the Slack system with the access token, and with a specified comms channel.
	 *
	 * @param string $token Bot client oAuth token from Slack.
	 * @param string $handler   Guzzle client override, for testing purposes.
	 * @return this
	 */
	public function connect($token, $handler = null)
	{
		$this->client = new Communication($token, $handler);
		$this->token  = $token;
		
		return $this;
	}

	/**
	 * Sets the channel of operation.
	 *
	 * @param string $channel Field ID (recommended) or literal.
	 * @return this
	 */
	public function setChannel($channel)
	{
		// If the user designates the channel field with a literal, find the ID.
		//
		// Preferable if the user finds this out using identify, then stores this
		// to avoid hitting the rate limit for numerous requests.
		if ($channel[0] == '#') {
			$this->channel = $this->identifyChannel(substr($channel, 1));
		} else {
			$this->channel = $channel;
		}

		return $this;
	}

	/**
	 * Posts a quick message via the chat API. If a timestamp is provided, it will update that message.
	 * @param string $message
	 * @param string|boolean $ts
	 * @param string|boolean $threadts
	 * @return string|boolean message timestamp, or false on error
	 */
	public function message($message, $ts = false, $threadts = false)
	{
		$response = false;
		$args     = [
			'text'       => $message,
			'link_names' => 1
		];
		
		if ($threadts !== false) {
				$args['thread_ts'] = $threadts;
		}

		if (!$ts) {
			// New message.
			$response = $this->client->sendRequest('chat.postMessage', $this->channel, $args);
		} else {
			// Edit previous message.
			$args['ts'] = $ts;
			$response = $this->client->sendRequest('chat.update', $this->channel, $args);
		}
		
		if ($response !== false) {
			return $response['ts'];
		} else {
			return false;
		}
	}

	/**
	 * Deletes a message the bot user has posted.
	 * @param string $ts
	 * @return boolean
	 */
	public function deleteMessage($ts)
	{
		$response = $this->client->sendRequest('chat.delete', $this->channel, ['ts' => $ts]);
		
		if ($response['ok'] === true) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Identifies the channel ID from a given name. Rate limited by Slack.
	 * @url https://api.slack.com/methods/channels.list
	 * @param string $name channel name, without preceeding symbol
	 * @param boolean $public Is the channel a group?
	 */
	public function identifyChannel($name, $public = true)
	{
		$branding = ($public) ? 'channels' : 'groups';
		
		$cl = $this->client->sendRequest("{$branding}.list", $this->channel, ['exclude_archived' => true]);
		
		$bob = array_search($name, array_column($cl[$branding], 'name'));
		
		if ($bob === false) {
			return false;
		} else {
			return $cl[$branding][$bob]['id'];
		}
	}

	/**
	 * Gets a collection of channels from the Slack workspace.
	 */
	public function findChannels() {
		return $this->client->sendRequest("conversations.list", $this->channel, ['exclude_archived' => true]);
	}

	/**
	 * Pin the specified timestamp message to the chat.
	 * @param string $ts
	 */
	public function pin($ts)
	{
		return $this->pinModifier($ts, 'add');
	}

	/**
	 * Pin the specified timestamp message to the chat.
	 * @param string $ts
	 */
	public function unpin($ts)
	{
		return $this->pinModifier($ts, 'remove');
	}

	/**
	 * React to the specified message. Needs to be a Slack code name, without colons.
	 *
	 * @param string $ts
	 * @param string $emoji
	 * @return boolean
	 */
	public function react($ts, $emoji = 'thumbsup')
	{
		// Remove colon denominators if present.
		$emoji = str_replace( ':', '', $emoji );

		$response = $this->client->sendRequest("reactions.add", $this->channel, [
			'name'      => $emoji,
			'timestamp' => $ts
		]);
		
		if ($response['ok'] === true) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Obtains client information.
	 */
	public function botInfo()
	{
		return $this->client->sendRequest('bots.info');
	}

	/**
	 * Tests the Slack API. Normally replies with ok.
	 */
	public function test()
	{
		return $this->client->sendRequest('api.test');
	}

	private function pinModifier($ts, $state)
	{
		$response = $this->client->sendRequest("pins.{$state}", $this->channel, ['timestamp' => $ts]);
		
		if ($response['ok'] === true) {
			return true;
		} else {
			return false;
		}
	}
}
