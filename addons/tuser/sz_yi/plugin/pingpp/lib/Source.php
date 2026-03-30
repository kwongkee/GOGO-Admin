<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class Source extends ApiResource
{
	public function instanceUrl()
	{
		$id = $this['id'];

		if (!$id) {
			$class = get_class($this);
			$msg = 'Could not determine which URL to request: ' . $class . ' instance ' . 'has invalid ID: ' . $id;
			throw new Error\InvalidRequest($msg, null);
		}

		if ($this['customer']) {
			$parent = $this['customer'];
			$base = Customer::classUrl();
			$path = 'sources';
		}
		else {
			return null;
		}

		$parent = Util\Util::utf8($parent);
		$id = Util\Util::utf8($id);
		$_obf_DRc_FTsmFRkDKRsLNzQ2AQw5JxAMHjI_ = urlencode($parent);
		$extn = urlencode($id);
		return $base . '/' . $_obf_DRc_FTsmFRkDKRsLNzQ2AQw5JxAMHjI_ . '/' . $path . '/' . $extn;
	}

	public function delete($params = NULL, $opts = NULL)
	{
		return $this->_delete($params, $opts);
	}

	public function save($opts = NULL)
	{
		return $this->_save($opts);
	}
}

?>
