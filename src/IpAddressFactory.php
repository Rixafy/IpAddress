<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

use Nette\Utils\JsonException;

class IpAddressFactory
{
	/** @var IpAddressResolver */
	private $ipAddressResolver;

	public function __construct(IpAddressResolver $ipAddressResolver)
	{
		$this->ipAddressResolver = $ipAddressResolver;
	}

	/**
	 * @throws JsonException
	 */
	public function create(string $address): IpAddress
	{
		return new IpAddress($this->ipAddressResolver->resolve($address));
	}
}
