<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\JsonException;
use Ramsey\Uuid\UuidInterface;

class IpAddressFacade
{
	/** @var EntityManagerInterface */
	private $entityManager;

	/** @var IpAddressRepository */
	private $ipAddressRepository;

	/** @var IpAddressFactory */
	private $ipAddressFactory;

	/** @var IpAddressResolver */
	private $ipAddressResolver;

	public function __construct(
		EntityManagerInterface $entityManager,
		IpAddressRepository $ipAddressRepository,
		IpAddressFactory $ipAddressFactory,
		IpAddressResolver $ipAddressResolver
	) {
		$this->entityManager = $entityManager;
		$this->ipAddressRepository = $ipAddressRepository;
		$this->ipAddressFactory = $ipAddressFactory;
		$this->ipAddressResolver = $ipAddressResolver;
	}

	/**
	 * @throws JsonException
	 */
	public function create(string $address): IpAddress
	{
		$data = $this->ipAddressResolver->resolve($address);
		$ipAddress = $this->ipAddressFactory->create($data);

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
