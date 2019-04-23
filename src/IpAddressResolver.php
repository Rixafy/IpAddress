<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

use Exception;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use peterkahl\locale\locale;
use Rixafy\Country\CountryData;
use Rixafy\Country\CountryFacade;
use Rixafy\Country\CountryFactory;
use Rixafy\Country\Exception\CountryNotFoundException;

class IpAddressResolver
{
	/** @var CountryFacade */
	private $countryFacade;

	/** @var CountryFacade */
	private $countryFactory;

	public function __construct(
		CountryFacade $countryFacade,
		CountryFactory $countryFactory
	) {
		$this->countryFacade = $countryFacade;
		$this->countryFactory = $countryFactory;
	}

	/**
	 * @throws JsonException
	 * @throws Exception
	 */
	public function resolve(string $address): IpAddressData
	{
		$ipAddressData = new IpAddressData();

		try {
			if (isset($_SERVER['HTTP_CF_IPCOUNTRY']) && !empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
				$country = $this->countryFacade->getByCodeAlpha2($_SERVER['HTTP_CF_IPCOUNTRY']);
			} else {
				throw new CountryNotFoundException('Header "HTTP_CF_IPCOUNTRY" not found in http request.');
			}
		} catch (CountryNotFoundException $e) {
			$ctx = stream_context_create([
				'http' => [
					'timeout' => 3
				]
			]);

			if ($plainData = @file_get_contents('http://www.geoplugin.net/json.gp?ip=' . $address, false, $ctx)) {
				$json = Json::decode($plainData);
				try {
					$country = $this->countryFacade->getByCodeAlpha2((string)$json->geoplugin_countryCode);
				} catch(CountryNotFoundException $e) {
					$countryData = new CountryData();

					$countryData->name = (string) $json->geoplugin_countryName;
					$countryData->codeCurrency = (string) $json->geoplugin_currencyCode;
					$countryData->codeContinent = (string) $json->geoplugin_continentCode;
					$countryData->codeAlpha2 = (string) $json->geoplugin_countryCode;
					$countryData->codeLanguage = substr(locale::country2locale($json->geoplugin_countryCode), 0, 2);

					$country = $this->countryFactory->create($countryData);
				}
			} else {
				throw new Exception('Http request failed, geoplugin.net is unreachable.');
			}
		}

		$ipAddressData->ipAddress = $address;
		$ipAddressData->country = $country;

		return $ipAddressData;
	}
}