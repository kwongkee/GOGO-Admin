<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class GeoPoint
{
	private $latitude;
	private $longitude;

	public function __construct($latitude = 0, $longitude = 0)
	{
		if (($latitude <= 90) && (-90 <= $latitude) && ($longitude <= 180) && (-180 <= $longitude)) {
			$this->latitude = $latitude;
			$this->longitude = $longitude;
			return NULL;
		}

		throw new \InvalidArgumentException('Invalid latitude or ' . 'longitude for geo point');
	}

	public function getLatitude()
	{
		return $this->latitude;
	}

	public function getLongitude()
	{
		return $this->longitude;
	}

	public function radiansTo(GeoPoint $point)
	{
		$d2r = M_PI / 180;
		$lat1rad = $this->getLatitude() * $d2r;
		$lon1rad = $this->getLongitude() * $d2r;
		$lat2rad = $point->getLatitude() * $d2r;
		$lon2rad = $point->getLongitude() * $d2r;
		$deltaLat = $lat1rad - $lat2rad;
		$deltaLon = $lon1rad - $lon2rad;
		$sinLat = sin($deltaLat / 2);
		$sinLon = sin($deltaLon / 2);
		$a = ($sinLat * $sinLat) + (cos($lat1rad) * cos($lat2rad) * $sinLon * $sinLon);
		$a = min(1, $a);
		return 2 * asin(sqrt($a));
	}

	public function kilometersTo(GeoPoint $point)
	{
		return $this->radiansTo($point) * 6371;
	}

	public function milesTo(GeoPoint $point)
	{
		return $this->radiansTo($point) * 3958.8000000000002;
	}

	public function encode()
	{
		return array('__type' => 'GeoPoint', 'latitude' => $this->latitude, 'longitude' => $this->longitude);
	}
}


?>
