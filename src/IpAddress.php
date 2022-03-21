<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Rixafy\Country\Country;
use Rixafy\DoctrineTraits\UniqueTrait;

#[ORM\Entity]
#[ORM\Table(name: 'ip_address')]
#[ORM\Index(columns: ['ipv4_address'], name: 'search_ipv4_index')]
#[ORM\Index(columns: ['ipv6_address'], name: 'search_ipv6_index')]
class IpAddress
{
	use UniqueTrait;

	#[ORM\Column(name: 'ipv6_address', type: 'uuid_binary', nullable: true)]
	private ?UuidInterface $ipv6Address;
	
	#[ORM\Column(name: 'ipv4_address', nullable: true, options: ['unsigned' => true])]
	private ?int $ipv4Address;

	#[ORM\Column]
	private bool $isIpv6;

	#[ORM\Column]
	private string $domainHost;
	
	#[ORM\ManyToOne(targetEntity: Country::class, cascade: ['persist'])]
	private Country $country;

	#[ORM\Column]
	private int $pageLoads;

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
