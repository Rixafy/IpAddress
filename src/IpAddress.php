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
 *     @ORM\Index(name="search_ipv4_index", columns={"ipv4_address"}),
 *     @ORM\Index(name="search_ipv6_index", columns={"ipv6_address"})
 * })
 */
class IpAddress
{
	use UniqueTrait;

	/**
	 * @var UuidInterface
	 * @ORM\Column(type="uuid_binary", nullable=true)
	 */
	private $ipv6_address;

	/**
	 * @var int
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
	 */
	private $ipv4_address;

	/**
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	private $is_ipv6;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $domain_host;

	/**
	 * @var Country
	 * @ORM\ManyToOne(targetEntity="Rixafy\Country\Country", inversedBy="ip_address", cascade={"persist"})
	 */
	private $country;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	private $page_loads;

	public function __construct(IpAddressData $data)
	{
		$this->is_ipv6 = strlen($data->ipAddress) > 15;
		$this->ipv6_address = $this->is_ipv6 ? Uuid::fromBytes(inet_pton($data->ipAddress)) : null;
		$this->ipv4_address = $this->is_ipv6 ? null : ip2long($data->ipAddress);
		$this->edit($data);
	}

	public function edit(IpAddressData $data): void
	{
		$this->domain_host = gethostbyaddr($data->ipAddress);
		$this->country = $data->country;
		$this->page_loads = $data->pageLoads;
	}

	public function getData(): IpAddressData
	{
		$data = new IpAddressData();

		$data->ipAddress = $this->getAddress();
		$data->country = $this->country;
		$data->pageLoads = $this->page_loads;

		return $data;
	}

	public function getAddress(): string
	{
		return $this->is_ipv6 ? (string) $this->ipv6_address : long2ip($this->ipv4_address);
	}

	public function getDomainHost(): string
	{
		return $this->domain_host;
	}

	public function isIpv6(): bool
	{
		return $this->is_ipv6;
	}

	public function getCountry(): Country
	{
		return $this->country;
	}

	public function addPageLoad(): void
	{
		$this->page_loads++;
	}
}
