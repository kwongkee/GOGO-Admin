<?php
// 模块LTD提供
namespace Qiniu\Processing;

final class ImageUrlBuilder
{
	protected $modeArr = array(0, 1, 2, 3, 4, 5);
	protected $formatArr = array('psd', 'jpeg', 'png', 'gif', 'webp', 'tiff', 'bmp');
	protected $gravityArr = array('NorthWest', 'North', 'NorthEast', 'West', 'Center', 'East', 'SouthWest', 'South', 'SouthEast');

	public function thumbnail($url, $mode, $width, $height, $format = NULL, $interlace = NULL, $quality = NULL, $ignoreError = 1)
	{
		if (!$this->isUrl($url)) {
			return $url;
		}

		if (!in_array(intval($mode), $this->modeArr, true)) {
			return $url;
		}

		if (!$width || !$height) {
			return $url;
		}

		$_obf_DScKNQs_BDY0ESEnDSQYBDMfPT4IDTI_ = 'imageView2/' . $mode . '/w/' . $width . '/h/' . $height . '/';
		if (!is_null($format) && in_array($format, $this->formatArr)) {
			$_obf_DScKNQs_BDY0ESEnDSQYBDMfPT4IDTI_ .= 'format/' . $format . '/';
		}

		if (!is_null($interlace) && in_array(intval($interlace), array(0, 1), true)) {
			$_obf_DScKNQs_BDY0ESEnDSQYBDMfPT4IDTI_ .= 'interlace/' . $interlace . '/';
		}

		if (!is_null($quality) && (0 <= intval($quality)) && (intval($quality) <= 100)) {
			$_obf_DScKNQs_BDY0ESEnDSQYBDMfPT4IDTI_ .= 'q/' . $quality . '/';
		}

		$_obf_DScKNQs_BDY0ESEnDSQYBDMfPT4IDTI_ .= 'ignore-error/' . $ignoreError . '/';
		return $url . ($this->hasQuery($url) ? '|' : '?') . $_obf_DScKNQs_BDY0ESEnDSQYBDMfPT4IDTI_;
	}

	public function waterImg($url, $image, $dissolve = 100, $gravity = 'SouthEast', $dx = NULL, $dy = NULL, $watermarkScale = NULL)
	{
		if (!$this->isUrl($url)) {
			return $url;
		}

		$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ = 'watermark/1/image/' . \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($image) . '/';
		if (is_numeric($dissolve) && ($dissolve <= 100)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'dissolve/' . $dissolve . '/';
		}

		if (in_array($gravity, $this->gravityArr, true)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'gravity/' . $gravity . '/';
		}

		if (!is_null($dx) && is_numeric($dx)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'dx/' . $dx . '/';
		}

		if (!is_null($dy) && is_numeric($dy)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'dy/' . $dy . '/';
		}

		if (!is_null($watermarkScale) && is_numeric($watermarkScale) && (0 < $watermarkScale) && ($watermarkScale < 1)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'ws/' . $watermarkScale . '/';
		}

		return $url . ($this->hasQuery($url) ? '|' : '?') . $_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_;
	}

	public function waterText($url, $text, $font = '黑体', $fontSize = 0, $fontColor = NULL, $dissolve = 100, $gravity = 'SouthEast', $dx = NULL, $dy = NULL)
	{
		if (!$this->isUrl($url)) {
			return $url;
		}

		$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ = 'watermark/2/text/' . \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($text) . '/font/' . \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($font) . '/';

		if (is_int($fontSize)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'fontsize/' . $fontSize . '/';
		}

		if (!is_null($fontColor) && $fontColor) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'fill/' . \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($fontColor) . '/';
		}

		if (is_numeric($dissolve) && ($dissolve <= 100)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'dissolve/' . $dissolve . '/';
		}

		if (in_array($gravity, $this->gravityArr, true)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'gravity/' . $gravity . '/';
		}

		if (!is_null($dx) && is_numeric($dx)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'dx/' . $dx . '/';
		}

		if (!is_null($dy) && is_numeric($dy)) {
			$_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_ .= 'dy/' . $dy . '/';
		}

		return $url . ($this->hasQuery($url) ? '|' : '?') . $_obf_DT0fAhEOPC5ACARbAgMOJiEwMDwxPRE_;
	}

	protected function isUrl($url)
	{
		$urlArr = parse_url($url);
		return $urlArr['scheme'] && in_array($urlArr['scheme'], array('http', 'https')) && $urlArr['host'] && $urlArr['path'];
	}

	protected function hasQuery($url)
	{
		$urlArr = parse_url($url);
		return !empty($urlArr['query']);
	}
}


?>
