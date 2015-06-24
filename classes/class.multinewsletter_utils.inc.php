<?php
/**
 * Nützliche Werkzeuge rund um MultiNewsletter.
 */
class multinewsletter_utils {
	public static function appendToPageHeader($params) {
		global $REX;

		$insert = '<!-- BEGIN multinewsletter -->' . PHP_EOL;
		$insert .= '<link rel="stylesheet" type="text/css" href="../' . $REX['MEDIA_ADDON_DIR'] . '/multinewsletter/multinewsletter.css" />' . PHP_EOL;
		$insert .= '<link rel="stylesheet" type="text/css" href="../' . $REX['MEDIA_ADDON_DIR'] . '/multinewsletter/qtip.css" />' . PHP_EOL;
		$insert .= '<link rel="stylesheet" type="text/css" href="../' . $REX['MEDIA_ADDON_DIR'] . '/multinewsletter/jquery.tag-editor.css" />' . PHP_EOL;
		$insert .= '<link rel="stylesheet" type="text/css" href="../' . $REX['MEDIA_ADDON_DIR'] . '/multinewsletter/jquery.dropdown.css" />' . PHP_EOL;
		$insert .= '<script type="text/javascript" src="../' . $REX['MEDIA_ADDON_DIR'] . '/multinewsletter/multinewsletter.js"></script>' . PHP_EOL;
		$insert .= '<script type="text/javascript" src="../' . $REX['MEDIA_ADDON_DIR'] . '/multinewsletter/jquery.qtip.min.js"></script>' . PHP_EOL;
		$insert .= '<script type="text/javascript" src="../' . $REX['MEDIA_ADDON_DIR'] . '/multinewsletter/jquery.tag-editor.min.js"></script>' . PHP_EOL;
		$insert .= '<script type="text/javascript" src="../' . $REX['MEDIA_ADDON_DIR'] . '/multinewsletter/jquery.dropdown.min.js"></script>' . PHP_EOL;
		$insert .= '<!-- END multinewsletter -->';
	
		return $params['subject'] . PHP_EOL . $insert;
	}

	public static function createDynFile($file) {
		$fileHandle = fopen($file, 'w');

		fwrite($fileHandle, "<?php\r\n");
		fwrite($fileHandle, "// --- DYN\r\n");
		fwrite($fileHandle, "// --- /DYN\r\n");

		fclose($fileHandle);
	}

	public static function getSettingsFile() {
		global $REX;

		if (isset($REX['WEBSITE_MANAGER']) && $REX['WEBSITE_MANAGER']->getCurrentWebsiteId() != $REX['WEBSITE_MANAGER']->getMasterWebsiteId()) {
			return MULTINEWSLETTER_DATA_DIR . 'settings' . $REX['WEBSITE_MANAGER']->getCurrentWebsiteId() . '.inc.php';
		} else {
			return MULTINEWSLETTER_DATA_DIR . 'settings.inc.php';
		}
	}

	public static function includeSettingsFile() {
		global $REX; // important for include

		$settingsFile = self::getSettingsFile();

		if (!file_exists($settingsFile)) {
			self::updateSettingsFile(false);
		}

		require_once($settingsFile);
	}

	public static function updateSettingsFile($showSuccessMsg = true) {
		global $REX, $I18N;

		$settingsFile = self::getSettingsFile();
		$msg = self::checkDirForFile($settingsFile);

		if ($msg != '') {
			if ($REX['REDAXO']) {
				echo rex_warning($msg);
			}
		} else {
			if (!file_exists($settingsFile)) {
				self::createDynFile($settingsFile);
			}

			$content = "<?php\n\n";
		
			foreach ((array) $REX['ADDON']['multinewsletter']['settings'] as $key => $value) {
				$content .= "\$REX['ADDON']['multinewsletter']['settings']['$key'] = " . var_export($value, true) . ";\n";
			}

			if (rex_put_file_contents($settingsFile, $content)) {
				if ($REX['REDAXO'] && $showSuccessMsg) {
					echo rex_info($I18N->msg('multinewsletter_config_ok'));
				}
			} else {
				if ($REX['REDAXO']) {
					echo rex_warning($I18N->msg('multinewsletter_config_error'));
				}
			}
		}
	}

	public static function getLangSettingsMsg() {
		global $REX, $I18N;

		if (!isset($REX['ADDON']['multinewsletter']['settings']['lang']) || multinewsletter::getLangCount() != count($REX['ADDON']['multinewsletter']['settings']['lang'])) {
			$icon = '<span title="' . $I18N->msg('multinewsletter_setup_langcount_error') . '" class="multinewsletter-tooltip status exclamation">&nbsp;</span>';
		} else {
			$icon = '';
		}

		return '<span class="rex-form-read" id="lang_hint">' . $I18N->msg('multinewsletter_setup_lang_settings_hint') . '</span>' . $icon;
	}

	public static function checkDir($dir) {
		global $REX, $I18N;

		$path = $dir;

		if (!@is_dir($path)) {
			@mkdir($path, $REX['DIRPERM'], true);
		}

		if (!@is_dir($path)) {
			if ($REX['REDAXO']) {
				return $I18N->msg('multinewsletter_install_make_dir', $dir);
			}
		} elseif (!@is_writable($path . '/.')) {
			if ($REX['REDAXO']) {
				return $I18N->msg('multinewsletter_install_perm_dir', $dir);
			}
		}
		
		return '';
	}

	public static function checkDirForFile($fileWithPath) {
		$pathInfo = pathinfo($fileWithPath);

		return self::checkDir($pathInfo['dirname']);
	}
	
	public static function convertVarType($originalValue, $newValue) {
		switch (gettype($originalValue)) {
			case 'string':
				return trim($newValue);
				break;
			case 'integer':
				return intval($newValue);
				break;
			case 'boolean':
				return (bool) $newValue;
				break;
			case 'array':
				if ($newValue == '') {
					return array();
				} else {
					return explode(MULTINEWSLETTER_ARRAY_DELIMITER, $newValue);
				}
				break;
			default:
				return $newValue;
				
		}
	}
}