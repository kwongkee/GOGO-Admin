<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanFile
{
	private $_data;
	private $_metaData;
	private $_source;

	public function __construct($name, $data = NULL, $mimeType = NULL)
	{
		$this->_data['name'] = $name;
		$this->_source = $data;

		if (!$mimeType) {
			$ext = pathinfo($name, PATHINFO_EXTENSION);
			$mimeType = MIMEType::getType($ext);
		}

		$this->_data['mime_type'] = $mimeType;
		$user = LeanUser::getCurrentUser();
		$this->_metaData['owner'] = $user ? $user->getObjectId() : 'unknown';

		if ($this->_source) {
			$this->_metaData['size'] = strlen($this->_source);
		}
	}

	static public function createWithUrl($name, $url, $mimeType = NULL)
	{
		$file = new LeanFile($name, null, $mimeType);
		$file->_data['url'] = $url;
		$file->_metaData['__source'] = 'external';
		return $file;
	}

	static public function createWithData($name, $data, $mimeType = NULL)
	{
		$file = new LeanFile($name, $data, $mimeType);
		return $file;
	}

	static public function createWithLocalFile($filepath, $mimeType = NULL)
	{
		$content = file_get_contents($filepath);

		if ($content === false) {
			throw new \RuntimeException('Read file error at ' . $filepath);
		}

		return static::createWithData(basename($filepath), $content, $mimeType);
	}

	public function get($key)
	{
		if (isset($this->_data[$key])) {
			return $this->_data[$key];
		}

		return null;
	}

	public function getName()
	{
		return $this->get('name');
	}

	public function getObjectId()
	{
		return $this->get('objectId');
	}

	public function getCreatedAt()
	{
		return $this->get('createdAt');
	}

	public function getUpdatedAt()
	{
		return $this->get('updatedAt');
	}

	public function getMimeType()
	{
		return $this->get('mime_type');
	}

	public function getUrl()
	{
		return $this->get('url');
	}

	public function getThumbUrl($width, $height, $quality = 100, $scaleToFit = true, $format = 'png')
	{
		if (!$this->getUrl()) {
			throw new \RuntimeException('File resource not available.');
		}

		if (($width < 0) || ($height < 0)) {
			throw new \InvalidArgumentException('Width or height must' . ' be positve.');
		}

		if ((100 < $quality) || ($quality < 0)) {
			throw new \InvalidArgumentException('Quality must be between' . ' 0 and 100.');
		}

		$mode = ($scaleToFit ? 2 : 1);
		return $this->getUrl() . '?imageView/' . $mode . '/w/' . $width . '/h/' . $height . '/q/' . $quality . '/format/' . $format;
	}

	public function getSize()
	{
		return $this->getMeta('size');
	}

	public function getOwnerId()
	{
		return $this->getMeta('owner');
	}

	public function setMeta($key, $val)
	{
		$this->_metaData[$key] = $val;
		return $this;
	}

	public function getMeta($key = NULL)
	{
		if (!$key) {
			return $this->_metaData;
		}

		if (isset($this->_metaData[$key])) {
			return $this->_metaData[$key];
		}

		return null;
	}

	static private function genFileKey()
	{
		$octets = array_map(function() {
			$num = floor((1 + LeanClient::randomFloat()) * 65536);
			return substr(dechex($num), 1);
		}, range(0, 4));
		return implode('', $octets);
	}

	private function isExternal()
	{
		return $this->getMeta('__source') == 'external';
	}

	private function _mergeData($data, $meta = array())
	{
		foreach (array('createdAt', 'updatedAt') as $key) {
			if (isset($data[$key]) && is_string($data[$key])) {
				$data[$key] = array('__type' => 'Date', 'iso' => $data[$key]);
			}
		}

		foreach ($data as $key => $val) {
			$this->_data[$key] = LeanClient::decode($val, $key);
		}

		foreach ($meta as $key => $val) {
			$this->_metaData[$key] = LeanClient::decode($val, $key);
		}
	}

	public function mergeAfterSave($data)
	{
		$meta = array();

		if (isset($data['metaData'])) {
			$meta = $data['metaData'];
			unset($data['metaData']);
		}

		if (isset($data['size'])) {
			$meta['size'] = $data['size'];
			unset($data['size']);
		}

		$this->_mergeData($data, $meta);
	}

	public function mergeAfterFetch($data)
	{
		$meta = array();

		if (isset($data['metaData'])) {
			$meta = $data['metaData'];
			unset($data['metaData']);
		}

		$this->_mergeData($data, $meta);
	}

	public function isDirty()
	{
		$id = $this->getObjectId();
		return empty($id);
	}

	public function save()
	{
		if (!$this->isDirty()) {
			return NULL;
		}

		$data = array('name' => $this->getName(), 'ACL' => $this->get('ACL'), 'mime_type' => $this->getMimeType(), 'metaData' => $this->getMeta());

		if ($this->isExternal()) {
			$data['url'] = $this->getUrl();
			$resp = LeanClient::post('/files/' . $this->getName(), $data);
			$this->mergeAfterSave($resp);
			return NULL;
		}

		$key = static::genFileKey();
		$key .= '.' . pathinfo($this->getName(), PATHINFO_EXTENSION);
		$data['key'] = $key;
		$resp = LeanClient::post('/qiniu', $data);
		$token = $resp['token'];
		unset($resp['token']);
		$this->mergeAfterSave($resp);
		LeanClient::uploadToQiniu($token, $this->_source, $key, $this->getMimeType());
	}

	static public function fetch($objectId)
	{
		$file = new LeanFile('');
		$resp = LeanClient::get('/files/' . $objectId);
		$file->mergeAfterFetch($resp);
		return $file;
	}

	public function destroy()
	{
		if (!$this->getObjectId()) {
			return false;
		}

		LeanClient::delete('/files/' . $this->getObjectId());
	}

	public function encode()
	{
		if (!$this->getObjectId()) {
			throw new \RuntimeException('Cannot serialize unsaved file.');
		}

		return array('__type' => 'File', 'id' => $this->getObjectId(), 'name' => $this->getName(), 'url' => $this->getUrl());
	}
}


?>
