<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Helpers;

final class Image
{
	public static function isValid(string $file): bool
	{
		$type = @exif_imagetype($file);
		switch ($type) {
			case 1:
				$img = @imagecreatefromgif($file);
				break;
			case 2:
				$img = @imagecreatefromjpeg($file);
				break;
			case 3:
				$img = @imagecreatefrompng($file);
				break;
			default:
				return false;
		}
		if ($img) {
			$imageW = imagesx($img);
			$imageH = imagesy($img);

			$last_height = $imageH - 5;

			$foo = [];

			for ($x = 0; $x <= $imageW; $x++) {
				for ($y = $last_height; $y <= $imageH; $y++) {
					$rgb = @imagecolorat($img, $x, $y);

					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;

					if ($r != 0) {
						$foo[] = $r;
					}
				}
			}

			$bar = array_count_values($foo);

			$gray = ($bar['127'] ?? 0) + ($bar['128'] ?? 0) + ($bar['129'] ?? 0);
			$total = count($foo);
			$other = $total - $gray;

			return $other >= $gray;
		}
		return false;
	}
}