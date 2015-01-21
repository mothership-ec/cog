<?php

namespace Message\Cog\Serialization;

interface ArraySerializerInterface
{
	public function serialize(array $data);

	public function deserialize($data);
}