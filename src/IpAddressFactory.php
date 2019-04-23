<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

class IpAddressFactory
{
	public function create(IpAddressData $data): IpAddress
	{
		return new IpAddress($data);
	}
}
