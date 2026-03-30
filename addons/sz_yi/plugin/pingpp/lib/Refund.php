<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class Refund extends ApiResource
{
	public function instanceUrl()
	{
		$id = $this['id'];
		$charge = $this['charge'];

		if (!$id) {
			throw new Error\InvalidRequest('Could not determine which URL to request: ' . 'class instance has invalid ID: ' . $id, null);
		}

		$id = Util\Util::utf8($id);
		$charge = Util\Util::utf8($charge);
		$base = Charge::classUrl();
		$chargeExtn = urlencode($charge);
		$extn = urlencode($id);
		return $base . '/' . $chargeExtn . '/refunds/' . $extn;
	}

	public function save($opts = NULL)
	{
		return $this->_save($opts);
	}
}

?>
