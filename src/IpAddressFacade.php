<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;

class IpAddressFacade
{
	/** @var EntityManagerInterface */
	private $entityManager;

	/** @var IpAddressRepository */
	private $ipAddressRepository;

	/** @var IpAddressFactory */
	private $ipAddressFactory;

	public function __construct(
		EntityManagerInterface $entityManager,
		IpAddressRepository $ipAddressRepository,
		IpAddressFactory $ipAddressFactory
	) {
		$this->entityManager = $entityManager;
		$this->ipAddressRepository = $ipAddressRepository;
		$this->ipAddressFactory = $ipAddressFactory;
	}

	public function create(IpAddressData $ipAddressData): IpAddress
	{
		$ipAddress = $this->ipAddressFactory->create($ipAddressData);

		$this->entityManager->persist($ipAddress);
		$this->entityManager->flush();

		return $ipAddress;
	}

	/**
	 * @throws Exception\IpAddressNotFoundException
	 */
	public function edit(UuidInterface $id, IpAddressData $ipAddressData): IpAddress
	{
		$ipAddress = $this->ipAddressRepository->get($id);
		$ipAddress->edit($ipAddressData);

		$this->entityManager->flush();

		return $ipAddress;
	}

	/**
	 * @throws Exception\IpAddressNotFoundException
	 */
	public function get(UuidInterface $id): IpAddress
	{
		return $this->ipAddressRepository->get($id);
	}

	/**
	 * @throws Exception\IpAddressNotFoundException
	 */
	public function getByAddress(string $address): IpAddress
	{
		return $this->ipAddressRepository->getByAddress($address);
	}

	/**
	 * @throws Exception\IpAddressNotFoundException
	 */
	public function getByDomainHost(string $domainHost): IpAddress
	{
		return $this->ipAddressRepository->getByDomainHost($domainHost);
	}
}
