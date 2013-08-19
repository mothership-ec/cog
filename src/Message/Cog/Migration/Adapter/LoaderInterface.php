<?php

namespace Message\Cog\Migration\Adapter;

interface LoaderInterface {

	public function __construct($connector, $filesystem);
	public function getAll();
	public function getFromPath($path);
	public function getLastBatch();
	public function getLastBatchNumber();
	public function resolve($file);

}