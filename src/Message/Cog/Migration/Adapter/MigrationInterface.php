<?php

namespace Message\Cog\Migration\Adapter;

interface MigrationInterface {

	public function run($command);
	public function up();
	public function down();
	public function getFile();

}