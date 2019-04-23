<?php

declare(strict_types=1);

namespace Rixafy\IpAddress\Exception;

use Exception;
use Ramsey\Uuid\UuidInterface;

class IpAddressNotFoundException extends Exception
{
	public static function byId(UuidInterface $id): self
	{
		return new self('IpAddress with id "' . $id . '" not found.');
	}

	public static function byAddress(string $address): self
	{
		return new self('IpAddress with address "' . $address . '" not found.');
	}
}