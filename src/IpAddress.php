<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Rixafy\Country\Country;
use Rixafy\DoctrineTraits\UniqueTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="ip_address", indexes={
 *     @ORM\Index(name="search_ipv4_index", columns={"ipv4Address"}),
 *     @ORM\Index(name="search_ipv6_index", columns={"ipv6Address"})
 * })
 */
class IpAddress
{
	use UniqueTrait;

	/**
	 * @var UuidInterface
	 * @ORM\Column(type="uuid_binary", nullable=true)
	 */
	private $ipv6Address;

	/**
	 * @var int
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
	 */
	private $ipv4Address;

	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	private $isIpv6;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $domainHost;

	/**
	 * @var Country
	 * @ORM\ManyToOne(targetEntity="Rixafy\Country\Country", inversedBy="ip_address", cascade={"persist"})
	 */
	private $country;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	private $pageLoads;

	public function __construct(IpAddressData $data)
	{
		$this->isIpv6 = strlen($data->ipAddress) > 15;
		$this->ipv6Address = $this->isIpv6 ? Uuid::fromBytes(inet_pton($data->ipAddress)) : null;
		$this->ipv4Address = $this->isIpv6 ? null : ip2long($data->ipAddress);
		$this->edit($data);
	}

	public function edit(IpAddressData $data): void
	{
		$this->domainHost = gethostbyaddr($data->ipAddress);
		$this->country = $data->country;
		$this->pageLoads = $data->pageLoads;
	}

	public function getData(): IpAddressData
	{
		$data = new IpAddressData();

		$data->ipAddress = $this->getAddress();
		$data->country = $this->country;
		$data->pageLoads = $this->pageLoads;

		return $data;
	}

	public function getAddress(): string
	{
		return $this->isIpv6 ? inet_ntop($this->ipv6Address->getBytes()) : long2ip($this->ipv4Address);
	}

	public function getDomainHost(): string
	{
		return $this->domainHost;
	}

	public function isIpv6(): bool
	{
		return $this->isIpv6;
	}

	public function getCountry(): Country
	{
		return $this->country;
	}

	public function addPageLoad(): void
	{
		$this->pageLoads++;
	}
}
