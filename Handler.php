<?php
	/**
 * Copyright (C) Apis Networks, Inc - All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium, is
 * strictly prohibited without consent. Any dissemination of
 * material herein is prohibited.
 *
 * For licensing inquiries email <licensing@apisnetworks.com>
 *
 * Written by Matt Saladna <matt@apisnetworks.com>, August 2020
 */

	namespace Module\Support\Webapps\App\Type\Vanilla;

	use Module\Support\Webapps\App\Type\Unknown\Handler as Unknown;

	class Handler extends Unknown
	{
		const NAME = 'Vanilla';
		const ADMIN_PATH = '/entry/signin';
		const LINK = 'https://vanillaforums.com';

		const DEFAULT_FORTIFICATION = 'max';
		const FEAT_ALLOW_SSL = true;
		const FEAT_RECOVERY = true;

		const TRANSIENT_RECONFIGURABLES = [
			'debug', 'maintenance'
		];

		public function display(): bool
		{
			return version_compare($this->php_version(), '7', '>=');
		}
	}