<?php

namespace Message\Cog\HTTP;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Interface ResponseInterface
 * @package Message\Cog\HTTP
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Interface for classes that extend Symfony\Component\HttpFoundation\Request for use when type
 * checking the Cog classes that extend it.
 */
interface ResponseInterface
{
	/**
	 * @see \Symfony\Component\HttpFoundation\Request::create()
	 */
	public static function create($content = '', $status = 200, $headers = []);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::prepare()
	 */
	public function prepare(BaseRequest $request);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::sendHeaders()
	 */
	public function sendHeaders();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::sendContent()
	 */
	public function sendContent();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::send()
	 */
	public function send();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setContent()
	 */
	public function setContent($content);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::create()
	 */
	public function getContent();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setProtocolVersion()
	 */
	public function setProtocolVersion($version);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getProtocolVersion()
	 */
	public function getProtocolVersion();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setStatusCode()
	 */
	public function setStatusCode($code, $text = null);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getStatusCode()
	 */
	public function getStatusCode();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setCharset()
	 */
	public function setCharset($charset);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getCharset()
	 */
	public function getCharset();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isCacheable()
	 */
	public function isCacheable();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isFresh()
	 */
	public function isFresh();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isValidateable()
	 */
	public function isValidateable();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setPrivate()
	 */
	public function setPrivate();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setPublic()
	 */
	public function setPublic();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::mustRevalidate()
	 */
	public function mustRevalidate();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getDate()
	 */
	public function getDate();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setDate()
	 */
	public function setDate(\DateTime $date);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getAge()
	 */
	public function getAge();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::expire()
	 */
	public function expire();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getExpires()
	 */
	public function getExpires();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setExpires()
	 */
	public function setExpires(\DateTime $date = null);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setMaxAge()
	 */
	public function getMaxAge();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getMaxAge()
	 */
	public function setMaxAge($value);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setSharedMaxAge()
	 */
	public function setSharedMaxAge($value);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setProtocolVersion()
	 */
	public function getTtl();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setTtl()
	 */
	public function setTtl($seconds);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setClientTtl()
	 */
	public function setClientTtl($seconds);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getLastModified()
	 */
	public function getLastModified();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setLastModified()
	 */
	public function setLastModified(\DateTime $date = null);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getEtag()
	 */
	public function getEtag();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setEtag()
	 */
	public function setEtag($etag = null, $weak = false);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setCache()
	 */
	public function setCache(array $options);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setNotModified()
	 */
	public function setNotModified();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::hasVary()
	 */
	public function hasVary();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::getVary()
	 */
	public function getVary();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::setVary()
	 */
	public function setVary($headers, $replace = true);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isNotModified()
	 */
	public function isNotModified(BaseRequest $request);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isInvalid()
	 */
	public function isInvalid();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isInformational()
	 */
	public function isInformational();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isSuccessful()
	 */
	public function isSuccessful();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isRedirection()
	 */
	public function isRedirection();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isClientError()
	 */
	public function isClientError();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isServerError()
	 */
	public function isServerError();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isOk()
	 */
	public function isOk();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isForbidden()
	 */
	public function isForbidden();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isNotFound()
	 */
	public function isNotFound();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isRedirect()
	 */
	public function isRedirect($location = null);

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::isEmpty()
	 */
	public function isEmpty();

	/**
	 * @see \Symfony\Component\HttpFoundation\Request::closeOutputBuffers()
	 */
	public static function closeOutputBuffers($targetLevel, $flush);
}