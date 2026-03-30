<?php
// Ä£¿éLTDÌá¹©
class GeoPointTest extends PHPUnit_Framework_TestCase
{
	public function testInitializeDefaultGeoPoint()
	{
		$point = new \LeanCloud\GeoPoint();
		$this->assertEquals(0, $point->getLatitude());
		$this->assertEquals(0, $point->getLongitude());
	}

	public function testInitializeGeoPoint()
	{
		$point = new \LeanCloud\GeoPoint(90, 180);
		$this->assertEquals(90, $point->getLatitude());
		$this->assertEquals(180, $point->getLongitude());
		$point = new \LeanCloud\GeoPoint(90, -180);
		$point = new \LeanCloud\GeoPoint(-90, 180);
		$point = new \LeanCloud\GeoPoint(-90, -180);
	}

	public function testEncodeGeoPoint()
	{
		$point = new \LeanCloud\GeoPoint(39.899999999999999, 116.40000000000001);
		$out = $point->encode();
		$this->assertEquals('GeoPoint', $out['__type']);
		$this->assertEquals(39.899999999999999, $out['latitude']);
		$this->assertEquals(116.40000000000001, $out['longitude']);
	}

	public function testInvalidPoints()
	{
		$this->setExpectedException('InvalidArgumentException', 'Invalid latitude or longitude ' . 'for geo point');
		new \LeanCloud\GeoPoint(180, 90);
	}

	public function testRadiansDistance()
	{
		$point = new \LeanCloud\GeoPoint(39.899999999999999, 116.40000000000001);
		$this->assertEquals(0, $point->radiansTo($point));
		$rad = $point->radiansTo(new \LeanCloud\GeoPoint(-39.899999999999999, -63.600000000000001));
		$this->assertEquals(M_PI, $rad, '', 9.9999999999999995E-8);
		$rad = $point->radiansTo(new \LeanCloud\GeoPoint(0, 116.40000000000001));
		$this->assertEquals((39.899999999999999 * M_PI) / 180, $rad, '', 9.9999999999999995E-8);
	}
}

?>
