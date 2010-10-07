<?php
/**
 * GitPHP Resource
 *
 * Resource factory
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 */

require_once(GITPHP_BASEDIR . 'lib/php-gettext/streams.php');
require_once(GITPHP_BASEDIR . 'lib/php-gettext/gettext.php');

define('GITPHP_LOCALE_COOKIE', 'GitPHPLocale');

/**
 * Resource
 *
 * @package GitPHP
 * @subpackage Resource
 */
class GitPHP_Resource
{
	
	/**
	 * instance
	 *
	 * Stores the singleton instance of the resource provider
	 *
	 * @access protected
	 * @static
	 */
	protected static $instance = null;

	/**
	 * currentLocale
	 *
	 * Stores the currently instantiated locale identifier
	 *
	 * @access protected
	 * @static
	 */
	protected static $currentLocale = '';

	/**
	 * GetInstance
	 *
	 * Returns the singleton instance
	 *
	 * @access public
	 * @static
	 * @return mixed instance of resource class
	 */
	public static function GetInstance()
	{
		return self::$instance;
	}

	/**
	 * Instantiated
	 *
	 * Tests if the resource provider has been instantiated
	 *
	 * @access public
	 * @static
	 * @return boolean true if resource provider is instantiated
	 */
	public static function Instantiated()
	{
		return (self::$instance !== null);
	}

	/**
	 * GetLocale
	 *
	 * Gets the currently instantiated locale
	 *
	 * @access public
	 * @static
	 * @return string locale identifier
	 */
	public static function GetLocale()
	{
		return self::$currentLocale;
	}

	/**
	 * GetLocaleName
	 *
	 * Gets the current instantiated locale's name
	 *
	 * @access public
	 * @static
	 * @return string locale name
	 */
	public static function GetLocaleName()
	{
		return GitPHP_Resource::LocaleToName(self::$currentLocale);
	}

	/**
	 * LocaleToName
	 *
	 * Given a locale, returns a human readable name
	 *
	 * @access public
	 * @static
	 * @param string $locale locale
	 * return string name
	 */
	public static function LocaleToName($locale)
	{
		switch ($locale) {
			case 'en_US':
				return 'English';
			case 'fr_FR':
				return 'Français';
			case 'zz_Debug':
				return 'Gibberish';
		}
		return '';
	}

	/**
	 * SupportedLocales
	 *
	 * Gets the list of supported locales and their languages
	 *
	 * @access public
	 * @static
	 * @return array list of locales mapped to languages
	 */
	public static function SupportedLocales()
	{
		$locales = array();

		$locales['en_US'] = GitPHP_Resource::LocaleToName('en_US');

		if ($dh = opendir(GITPHP_LOCALEDIR)) {
			while (($file = readdir($dh)) !== false) {
				$fullPath = GITPHP_LOCALEDIR . '/' . $file;
				if ((strpos($file, '.') !== 0) && is_dir($fullPath) && is_file($fullPath . '/gitphp.mo')) {
					if ($file == 'zz_Debug') {
						$conf = GitPHP_Config::GetInstance();
						if ($conf) {
							if (!$conf->GetValue('debug', false)) {
								continue;
							}
						}
					}
					$locales[$file] = GitPHP_Resource::LocaleToName($file);
				}
			}
		}
		
		return $locales;
	}

	/**
	 * FindPreferredLocale
	 *
	 * Given a list of preferred locales, try to find a matching supported locale
	 *
	 * @access public
	 * @static
	 * @param string $httpAcceptLang HTTP Accept-Language string
	 * @return string matching locale if found
	 */
	public static function FindPreferredLocale($httpAcceptLang)
	{
		if (empty($httpAcceptLang))
			return '';

		$locales = explode(',', $httpAcceptLang);

		$localePref = array();

		foreach ($locales as $locale) {
			$quality = '1.0';
			$localeData = explode(';', trim($locale));
			if (count($localeData) > 1) {
				$q = trim($localeData[1]);
				if (substr($q, 0, 2) == 'q=') {
					$quality = substr($q, 2);
				}
			}
			$localePref[$quality][] = trim($localeData[0]);
		}
		krsort($localePref);

		$supportedLocales = GitPHP_Resource::SupportedLocales();

		foreach ($localePref as $quality => $qualityArray) {
			foreach ($qualityArray as $browserLocale) {
				$locale = str_replace('-', '_', $browserLocale);
				$loclen = strlen($locale);

				foreach ($supportedLocales as $l => $lang) {
					/* 
					 * using strncasecmp with length of the preferred
					 * locale means we can match both full
					 * language + country preference specifications
					 * (en_US) as well as just language specifications
					 * (en)
					 */
					if (strncasecmp($locale, $l, $loclen) === 0) {
						return $l;
					}
				}
			}
		}
		return '';
	}

	/**
	 * Instantiate
	 *
	 * Instantiates the singleton instance
	 *
	 * @access public
	 * @static
	 * @param string $locale locale to instantiate
	 * @return boolean true if resource provider was instantiated successfully
	 */
	public static function Instantiate($locale)
	{
		self::$instance = null;
		self::$currentLocale = '';

		$reader = null;
		if (!(($locale == 'en_US') || ($locale == 'en'))) {
			$reader = new FileReader(GITPHP_LOCALEDIR . $locale . '/gitphp.mo');
			if (!$reader)
				return false;
		}

		self::$instance = new gettext_reader($reader);
		self::$currentLocale = $locale;
		return true;
	}

}


/**
 * Wrappers... because PHP doesn't support aliases
 */
function __($str)
{
	if (GitPHP_Resource::Instantiated())
		return GitPHP_Resource::GetInstance()->translate($str);
	return $str;
}

function __n($singular, $plural, $count)
{
	if (GitPHP_Resource::Instantiated())
		return GitPHP_Resource::GetInstance()->ngettext($singular, $plural, $count);
	if ($count > 1)
		return $plural;
	return $singular;
}

