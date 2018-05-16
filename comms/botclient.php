<?php namespace SlackClient;

require __DIR__.'/../vendor/autoload.php';

use SlackClient\communication;

class botclient {
	protected $token;
	protected $channel;
	protected $client;
	public function __construct($token, $channel) {
		$this->client = new communication($token);
		
		$this->token   = $token;
		
		// If the user designates the channel field with a literal, find the ID.
		//
		// Preferable if the user finds this out using identify, then stores this
		// to avoid hitting the rate limit for numerous requests.
		if ($channel[0] == '#') {
			$this->channel = $this->identifyChannel(substr($channel, 1));
		}else {
			$this->channel = $channel;
		}
	}
	
	/**
	 * Posts a quick message via the chat API. If a timestamp is provided, it will update that message.
	 * @param string $message
	 * @param string|boolean $ts
	 * @return stdClass
	 */
	public function message($message, $ts = false) {
		if (!$ts) {
			// New message.
			return $this->client->sendRequest('chat.postMessage', [
				'channel'    => $this->channel,
				'text'       => $message,
				'link_names' => 1
			]);
		} else {
			// Edit previous message.
			return $this->client->sendRequest('chat.update', [
				'ts'         => $ts,
				'channel'    => $this->channel,
				'text'       => $message,
				'link_names' => 1
			]);
		}
	}
	
	/**
	 * Deletes a message the bot user has posted.
	 * @param string $ts
	 */
	public function deleteMessage($ts) {
		return $this->client->sendRequest('chat.delete', [
			'ts'      => $ts,
			'channel' => $this->channel
		]);
	}
	
	/**
	 * Identifies the channel ID from a given name. Rate limited by Slack.
	 * @url https://api.slack.com/methods/channels.list
	 * @param string $name channel name, without preceeding symbol
	 * @param boolean $public Is the channel a group?
	 */
	public function identifyChannel($name, $public = true) {
		$branding = ($public) ? 'channels' : 'groups';
		
		$cl = $this->client->sendRequest("{$branding}.list", [
			'exclude_archived' => true
		]);
		
		$bob = array_search($name, array_column($cl[$branding], 'name'));
		
		if ($bob === false) {
			return false;
		} else {
			return $cl[$branding][$bob]['id'];
		}
	}
	
	/**
	 * Pin the specified timestamp message to the chat.
	 * @param string $ts 
	 */
	public function pin($ts) {
		return $this->client->sendRequest('pins.add', [
			'channel' => $this->channel,
			'timestamp' => $ts
		]);
	}
	
	/**
	 * Pin the specified timestamp message to the chat.
	 * @param string $ts 
	 */
	public function unpin($ts) {
		return $this->client->sendRequest('pins.remove', [
			'channel' => $this->channel,
			'timestamp' => $ts
		]);
	}
	
	/**
	 * Tests the Slack API. Normally replies with ok.
	 */
	public function test() {
		return $this->client->sendRequest('api.test');
	}
}