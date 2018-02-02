<?php
/**
 * Class managing modules published by www.design-to-use.de
 *
 * @author Tobias Krais
 */
class D2UMultiNewsletterModules {
	/**
	 * Get modules offered by D2U Helper addon.
	 * @return D2UModule[] Modules offered by D2U Helper addon
	 */
	public static function getD2UMultiNewsletterModules() {
		$d2u_multinewsletter_modules = [];
		$d2u_multinewsletter_modules[] = new D2UModule("80-1",
			"MultiNewsletter Anmeldung",
			1);
		$d2u_multinewsletter_modules[] = new D2UModule("80-2",
			"MultiNewsletter Abmeldung",
			2);
		return $d2u_multinewsletter_modules;
	}
}