<?php

declare(strict_types=1);

namespace Rixafy\IpAddress;

use Exception;
use Nette\Utils\Json;
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

			$country = null;
			$countryAlpha2Code = null;
			if ($ip2loc = @file_get_contents('https://ip2c.org/' . $address, false, $ctx)) {
				if (strlen($ip2loc) > 0) {
					if ($ip2loc[0] === '1') {
						$countryAlpha2Code= explode(';', $ip2loc)[1];
					}
				}
			}

			if ($countryAlpha2Code !== null) {
				try {
					$country = $this->countryFacade->getByCodeAlpha2($countryAlpha2Code);
				} catch (CountryNotFoundException $e) {
				}
			}

			if ($country === null) {
				if ($plainData = @file_get_contents('http://www.geoplugin.net/json.gp?ip=' . $address, false, $ctx)) {
					$json = Json::decode($plainData);
					try {
						$country = $this->countryFacade->getByCodeAlpha2((string)$json->geoplugin_countryCode);
					} catch (CountryNotFoundException $e) {
						$countryData = new CountryData();

						$countryData->name = (string)$json->geoplugin_countryName;
						$countryData->codeCurrency = (string)$json->geoplugin_currencyCode;
						$countryData->codeContinent = (string)$json->geoplugin_continentCode;
						$countryData->codeAlpha2 = (string)$json->geoplugin_countryCode;
						$countryData->codeLanguage = substr($this->countryToLocale($json->geoplugin_countryCode), 0, 2);

						$country = $this->countryFactory->create($countryData);
					}
				} else {
					throw new Exception('Http request failed, geoplugin.net is unreachable.');
				}
			}
		}

		$ipAddressData->ipAddress = $address;
		$ipAddressData->country = $country;

		return $ipAddressData;
	}
	
	private function countryToLocale(string $country): string
	{
		$locales = [
			'af-ZA',
			'am-ET',
			'ar-AE',
			'ar-BH',
			'ar-DZ',
			'ar-EG',
			'ar-IQ',
			'ar-JO',
			'ar-KW',
			'ar-LB',
			'ar-LY',
			'ar-MA',
			'arn-CL',
			'ar-OM',
			'ar-QA',
			'ar-SA',
			'ar-SY',
			'ar-TN',
			'ar-YE',
			'as-IN',
			'az-Cyrl-AZ',
			'az-Latn-AZ',
			'ba-RU',
			'be-BY',
			'bg-BG',
			'bn-BD',
			'bn-IN',
			'bo-CN',
			'br-FR',
			'bs-Cyrl-BA',
			'bs-Latn-BA',
			'ca-ES',
			'co-FR',
			'cs-CZ',
			'cy-GB',
			'da-DK',
			'de-AT',
			'de-CH',
			'de-DE',
			'de-LI',
			'de-LU',
			'dsb-DE',
			'dv-MV',
			'el-GR',
			'en-029',
			'en-AU',
			'en-BZ',
			'en-CA',
			'en-GB',
			'en-IE',
			'en-IN',
			'en-JM',
			'en-MY',
			'en-NZ',
			'en-PH',
			'en-SG',
			'en-TT',
			'en-US',
			'en-ZA',
			'en-ZW',
			'es-AR',
			'es-BO',
			'es-CL',
			'es-CO',
			'es-CR',
			'es-DO',
			'es-EC',
			'es-ES',
			'es-GT',
			'es-HN',
			'es-MX',
			'es-NI',
			'es-PA',
			'es-PE',
			'es-PR',
			'es-PY',
			'es-SV',
			'es-US',
			'es-UY',
			'es-VE',
			'et-EE',
			'eu-ES',
			'fa-IR',
			'fi-FI',
			'fil-PH',
			'fo-FO',
			'fr-BE',
			'fr-CA',
			'fr-CH',
			'fr-FR',
			'fr-LU',
			'fr-MC',
			'fy-NL',
			'ga-IE',
			'gd-GB',
			'gl-ES',
			'gsw-FR',
			'gu-IN',
			'ha-Latn-NG',
			'he-IL',
			'hi-IN',
			'hr-BA',
			'hr-HR',
			'hsb-DE',
			'hu-HU',
			'hy-AM',
			'id-ID',
			'ig-NG',
			'ii-CN',
			'is-IS',
			'it-CH',
			'it-IT',
			'iu-Cans-CA',
			'iu-Latn-CA',
			'ja-JP',
			'ka-GE',
			'kk-KZ',
			'kl-GL',
			'km-KH',
			'kn-IN',
			'kok-IN',
			'ko-KR',
			'ky-KG',
			'lb-LU',
			'lo-LA',
			'lt-LT',
			'lv-LV',
			'mi-NZ',
			'mk-MK',
			'ml-IN',
			'mn-MN',
			'mn-Mong-CN',
			'moh-CA',
			'mr-IN',
			'ms-BN',
			'ms-MY',
			'mt-MT',
			'nb-NO',
			'ne-NP',
			'nl-BE',
			'nl-NL',
			'nn-NO',
			'nso-ZA',
			'oc-FR',
			'or-IN',
			'pa-IN',
			'pl-PL',
			'prs-AF',
			'ps-AF',
			'pt-BR',
			'pt-PT',
			'qut-GT',
			'quz-BO',
			'quz-EC',
			'quz-PE',
			'rm-CH',
			'ro-RO',
			'ru-RU',
			'rw-RW',
			'sah-RU',
			'sa-IN',
			'se-FI',
			'se-NO',
			'se-SE',
			'si-LK',
			'sk-SK',
			'sl-SI',
			'sma-NO',
			'sma-SE',
			'smj-NO',
			'smj-SE',
			'smn-FI',
			'sms-FI',
			'sq-AL',
			'sr-Cyrl-BA',
			'sr-Cyrl-CS',
			'sr-Cyrl-ME',
			'sr-Cyrl-RS',
			'sr-Latn-BA',
			'sr-Latn-CS',
			'sr-Latn-ME',
			'sr-Latn-RS',
			'sv-FI',
			'sv-SE',
			'sw-KE',
			'syr-SY',
			'ta-IN',
			'te-IN',
			'tg-Cyrl-TJ',
			'th-TH',
			'tk-TM',
			'tn-ZA',
			'tr-TR',
			'tt-RU',
			'tzm-Latn-DZ',
			'ug-CN',
			'uk-UA',
			'ur-PK',
			'uz-Cyrl-UZ',
			'uz-Latn-UZ',
			'vi-VN',
			'wo-SN',
			'xh-ZA',
			'yo-NG',
			'zh-CN',
			'zh-HK',
			'zh-MO',
			'zh-SG',
			'zh-TW',
			'zu-ZA'
		];

		foreach ($locales as $locale) {
			$locale_region = locale_get_region($locale);
			$locale_language = locale_get_primary_language($locale);
			$locale_array = [
				'language' => $locale_language,
				'region' => $locale_region
			];

			if (strtoupper($country) === $locale_region) {
				return locale_compose($locale_array);
			}
		}
		
		return 'en-US';
	}
}