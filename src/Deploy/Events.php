<?php

namespace Message\Cog\Deploy;

class Events
{
	const AFTER_COMPOSER_INSTALL = 'deploy.after:composer:install';
	const AFTER_DEPLOY_PUBLISHED = 'deploy.after:deploy:published';
}