<?php
// Ä£¿éLTDÌá¹©
namespace Qiniu;

final class Etag
{
	static private function packArray($v, $a)
	{
		return call_user_func_array('pack', array_merge(array($v), (array) $a));
	}

	static private function blockCount($fsize)
	{
		return ($fsize + (Config::BLOCK_SIZE - 1)) / Config::BLOCK_SIZE;
	}

	static private function calcSha1($data)
	{
		$_obf_DQwxMR0vCj0lFSIZAyUBEhonOz0PNRE_ = sha1($data, true);
		$err = error_get_last();

		if ($err !== null) {
			return array(null, $err);
		}

		$byteArray = unpack('C*', $_obf_DQwxMR0vCj0lFSIZAyUBEhonOz0PNRE_);
		return array($byteArray, null);
	}

	static public function sum($filename)
	{
		$_obf_DR0LCQMpIjEdFTYHIhoTJQ4_EiI1AgE_ = fopen($filename, 'r');
		$err = error_get_last();

		if ($err !== null) {
			return array(null, $err);
		}

		$fstat = fstat($_obf_DR0LCQMpIjEdFTYHIhoTJQ4_EiI1AgE_);
		$fsize = $fstat['size'];

		if ((int) $fsize === 0) {
			fclose($_obf_DR0LCQMpIjEdFTYHIhoTJQ4_EiI1AgE_);
			return array('Fto5o-5ea0sNMlW_75VgGJCv2AcJ', null);
		}

		$_obf_DSgxOxlbCy8PJC0UHBYzKgI_ElwvJQE_ = self::blockCount($fsize);
		$_obf_DTUJNBY1HAQPLxYmNgMSJj8rKRczEjI_ = array();

		if ($_obf_DSgxOxlbCy8PJC0UHBYzKgI_ElwvJQE_ <= 1) {
			array_push($_obf_DTUJNBY1HAQPLxYmNgMSJj8rKRczEjI_, 22);
			$fdata = fread($_obf_DR0LCQMpIjEdFTYHIhoTJQ4_EiI1AgE_, Config::BLOCK_SIZE);

			if ($err !== null) {
				fclose($_obf_DR0LCQMpIjEdFTYHIhoTJQ4_EiI1AgE_);
				return array(null, $err);
			}

			list($_obf_DRUePhEDDFwXEREmHhw7MDYJERghKhE_) = self::calcSha1($fdata);
			$_obf_DTUJNBY1HAQPLxYmNgMSJj8rKRczEjI_ = array_merge($_obf_DTUJNBY1HAQPLxYmNgMSJj8rKRczEjI_, $_obf_DRUePhEDDFwXEREmHhw7MDYJERghKhE_);
		}
		else {
			array_push($_obf_DTUJNBY1HAQPLxYmNgMSJj8rKRczEjI_, 150);
			$_obf_DScRFgIrLAUCEiUHOTwWJwoHKCwPMxE_ = array();
			$i = 0;

			while ($i < $_obf_DSgxOxlbCy8PJC0UHBYzKgI_ElwvJQE_) {
				$fdata = fread($_obf_DR0LCQMpIjEdFTYHIhoTJQ4_EiI1AgE_, Config::BLOCK_SIZE);
				list($_obf_DRUePhEDDFwXEREmHhw7MDYJERghKhE_, $err) = self::calcSha1($fdata);

				if ($err !== null) {
					fclose($_obf_DR0LCQMpIjEdFTYHIhoTJQ4_EiI1AgE_);
					return array(null, $err);
				}

				$_obf_DScRFgIrLAUCEiUHOTwWJwoHKCwPMxE_ = array_merge($_obf_DScRFgIrLAUCEiUHOTwWJwoHKCwPMxE_, $_obf_DRUePhEDDFwXEREmHhw7MDYJERghKhE_);
				++$i;
			}

			$tmpData = self::packArray('C*', $_obf_DScRFgIrLAUCEiUHOTwWJwoHKCwPMxE_);
			list($_obf_DREKMT4ZCxoQNB4WXCMNAhcdGj8VGDI_) = self::calcSha1($tmpData);
			$_obf_DTUJNBY1HAQPLxYmNgMSJj8rKRczEjI_ = array_merge($_obf_DTUJNBY1HAQPLxYmNgMSJj8rKRczEjI_, $_obf_DREKMT4ZCxoQNB4WXCMNAhcdGj8VGDI_);
		}

		$etag = \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_(self::packArray('C*', $_obf_DTUJNBY1HAQPLxYmNgMSJj8rKRczEjI_));
		return array($etag, null);
	}
}


?>
