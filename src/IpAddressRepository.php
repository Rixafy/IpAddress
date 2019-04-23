<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Rixafy\IpAddress\Exception\IpAddressNotFoundException;

class IpAddressRepository
{
	/** @var EntityManagerInterface */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * @return EntityRepository|ObjectRepository
	 */
	protected function getRepository()
	{
		return $this->entityManager->getRepository(IpAddress::class);
	}

	/**
	 * @throws IpAddressNotFoundException
	 */
	public function get(UuidInterface $id): IpAddress
	{
		/** @var IpAddress $ipAddress */
		$ipAddress = $this->getRepository()->findOneBy([
			'id' => $id
		]);

		if ($ipAddress === null) {
			throw IpAddressNotFoundException::byId($id);
		}

		return $ipAddress;
	}

	/**
	 * @throws IpAddressNotFoundException
	 */
	public function getByAddress(string $address): IpAddress
	{
		$isIpv6 = strlen($address) > 15;

		/** @var $ipAddress IpAddress */
		if ($isIpv6) {
			$ipAddress = $this->getRepository()->findOneBy([
				'ipv6_address' => Uuid::fromBytes(inet_pton($address))
			]);
		} else {
			$ipAddress = $this->getRepository()->findOneBy([
				'ipv4_address' => ip2long($address)
			]);
		}

		if ($ipAddress === null) {
			throw IpAddressNotFoundException::byAddress($address);
		}

		return $ipAddress;
	}

	/**
	 * @throws IpAddressNotFoundException
	 */
	public function getByDomainHost(string $domainHost): IpAddress
	{
		/** @var $ipAddress IpAddress */
		$ipAddress = $this->getRepository()->findOneBy([
			'domain_host' => $domainHost
		]);

		if ($ipAddress === null) {
			throw IpAddressNotFoundException::byDomainHost($domainHost);
		}

		return $ipAddress;
	}

	public function getQueryBuilderForAll(): QueryBuilder
	{
		return $this->getRepository()->createQueryBuilder('e');
	}
}
