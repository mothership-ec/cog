<?php

namespace Message\Cog\Migration\Adapter;

use Message\Cog\Filesystem\File;

interface LoaderInterface
{
	public function getAll();

	public function getFromReference($reference);

	public function getLastBatch();

	public function getLastBatchNumber();

	public function resolve(File $file, $reference);

	public function getFailures();

}