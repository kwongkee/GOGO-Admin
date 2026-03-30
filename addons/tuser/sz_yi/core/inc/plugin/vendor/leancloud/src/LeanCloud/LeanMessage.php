<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanMessage
{
	private $data;
	private $options;

	public function __construct($data = array(), $options = array())
	{
		$this->data = $data;
		$this->options = $options;
	}

	public function setData($key, $val)
	{
		$this->data[$key] = $val;
	}

	public function setOption($key, $val)
	{
		$this->options[$key] = $val;
		return $this;
	}

	public function setChannels($channels)
	{
		return $this->setOption('channels', $channels);
	}

	public function setWhere(LeanQuery $query)
	{
		if ($query->getClassName() != '_Installation') {
			throw new \RuntimeException('Query must be over ' . '_Installation table.');
		}

		return $this->setOption('where', $query);
	}

	public function setPushTime(\DateTime $time)
	{
		return $this->setOption('push_time', $time);
	}

	public function setExpirationInterval($interval)
	{
		return $this->setOption('expiration_interval', $interval);
	}

	public function setExpirationTime(\DateTime $time)
	{
		return $this->setOption('expiration_time', $time);
	}

	public function encode()
	{
		$out = $this->data;

		if (isset($this->options['where'])) {
			$query = $this->options['where']->encode();
			$out['where'] = json_decode($query['where'], true);
		}

		return $out;
	}

	public function send()
	{
		$out = $this->encode();
		$out['conv_id'] = $this->getConvId();
		$resp = LeanClient::post('/rtm/messages', $out, null, array(), true);
		return $resp;
	}

	public function getConvId()
	{
		$object_id = @file_get_contents('conv_id.txt');

		if (!$object_id) {
			$res = $this->createConvId();
			$object_id = $res['objectId'];
			file_put_contents('conv_id.txt', $object_id);
		}

		return $object_id;
	}

	public function createConvId()
	{
		$data = json_decode('{"name": "Notification Channel","sys": true}', true);
		$resp = LeanClient::post('/classes/_Conversation', $data, null, array(), true);
		return $resp;
	}
}


?>
