<?php

namespace Message\Cog\Filesystem;

use Message\Cog\ReferenceParserInterface;

/**
 * A basic stream wrapper class that handles mapping cog:// style URLs to real
 * paths within the file system. You can use cog module references as well as 
 * setting one-to-one mappings e.g resolving cog://tmp/* to /tmp/*
 *
 * @author  James Moss <james@message.co.uk>
 */
class StreamWrapper implements StreamWrapperInterface
{
	/**
	* Stream context resource. This must be public
	*
	* @var resource
	*/
	public $context;

	/**
	* A generic resource handle.
	*
	* @var resource
	*/
	public $handle = NULL;

	/**
	* Instance URI (stream).
	*
	* A stream is referenced as "scheme://target".
	*
	* @var string
	*/
	protected $uri;

	protected $_mapping = array();
	protected $_parser;

	/**
	 * Sets the reference parser to use for this wrapper.
	 *
	 * @param ReferenceParserInterface $parser
	 */
	public function setReferenceParser(ReferenceParserInterface $parser)
	{
		$this->_parser = $parser;
	}

	/**
	 * Mappings are a way of rewriting one path to another using regular expressions replacements.
	 * For example you could map cog://tmp/ to /www/data/example.org/tmp/
	 *
	 * An important note: When matching cog:// style paths the prefix is removed. This means regexs should
	 * match paths starting with /.
	 * 
	 * For example the path cog://tmp/hello.txt would be sent into the regex replacement as /tmp/hello.txt
	 *
	 * @param array $mapping An array where the key is the key is the regex and the value is the replacement value
	 */
	public function setMapping(array $mapping)
	{
		$this->_mapping = $mapping;
	}

	/**
	 * Turns a uri (such as cog://public/images/) into a path of a real file.
	 *
	 * Mappings are checked first, if there's no matching mapping then the reference parser is checked.
	 *
	 * @param  string $uri 		The uri to be parsed
	 *
	 * @return string|boolean   If a valid URI is passed in, returns the full 
	 *                          real path to the file, otherwise false.
	 */
	public function getLocalPath($uri)
	{
		// strip off the prefix and slashes
		$len  = strlen($this->prefix . '://');
		$path = substr($uri, $len);

		// Try and match a mapping regex
		foreach($this->_mapping as $regex => $mapping) {
			$result = preg_replace($regex, $mapping, '/'.$path, -1, $count);

			if($count > 0) {
				return $result;
			}
		}

		// Now check our reference parser
		if($this->_parser && $fullPath = $this->_parser->parse($path)->getFullPath()) {
			return $fullPath;
		}

		return false;
	}

	/**
	* Support for fopen(), file_get_contents(), file_put_contents() etc.
	*
	* @param string $uri 			A string containing the URI to the file to open.
	* @param int $mode 				The file mode ("r", "wb" etc.).
	* @param int $options 			A bit mask of STREAM_USE_PATH and STREAM_REPORT_ERRORS.
	* @param string $opened_path	A string containing the path actually opened.
	*
	* @return bool 	Returns true if file was opened successfully.
	*
	* @see http://php.net/manual/streamwrapper.stream-open.php
	*/
	public function stream_open($uri, $mode, $options, &$opened_path)
	{
		$this->uri = $uri;
		$path = $this->getLocalPath($uri);

		if(!$path) {
			return false;
		}

		$this->handle = ($options & STREAM_REPORT_ERRORS) ? fopen($path, $mode) : @fopen($path, $mode);

		if ((bool) $this->handle && $options & STREAM_USE_PATH) {
			$opened_path = $path;
		}

		return (bool) $this->handle;
	}

	/**
	* Support for flock().
	*
	* 
	* @param int $operation One of the following:
	*	                      - LOCK_SH to acquire a shared lock (reader).
	*						  - LOCK_EX to acquire an exclusive lock (writer).
	*						  - LOCK_UN to release a lock (shared or exclusive).
	*						  - LOCK_NB if you don't want flock() to block while locking (not
	*							supported on Windows).
	*
	* @return bool Returns true if file was locked.
	*
	* @see http://php.net/manual/streamwrapper.stream-lock.php
	*/
	public function stream_lock($operation)
	{
		if (in_array($operation, array(LOCK_SH, LOCK_EX, LOCK_UN, LOCK_NB))) {
			return flock($this->handle, $operation);
		}

		return false;
	}

	/**
	* Support for fread(), file_get_contents() etc.
	*
	* @param int $count 	Maximum number of bytes to be read.
	*
	* @return string|bool 	The string that was read, or FALSE in case of an error.
	*
	* @see http://php.net/manual/streamwrapper.stream-read.php
	*/
	public function stream_read($count)
	{
		return fread($this->handle, $count);
	}

	/**
	* Support for fwrite(), file_put_contents() etc.
	*
	* @param string $data 	The string to be written.
	*
	* @return int 	The number of bytes written.
	*
	* @see http://php.net/manual/streamwrapper.stream-write.php
	*/
	public function stream_write($data)
	{
		return fwrite($this->handle, $data);
	}

	/**
	* Support for feof().
	*
	* @return bool 	true if end-of-file has been reached.
	*
	* @see http://php.net/manual/streamwrapper.stream-eof.php
	*/
	public function stream_eof()
	{
		return feof($this->handle);
	}

	/**
	* Support for fseek().
	*
	* @param int $offset 	The byte offset to got to.
	* @param int $whence 	SEEK_SET, SEEK_CUR, or SEEK_END.
	*
	* @return bool true on success.
	*
	* @see http://php.net/manual/streamwrapper.stream-seek.php
	*/
	public function stream_seek($offset, $whence)
	{
		// fseek returns 0 on success and -1 on a failure.
		// stream_seek	 1 on success and	0 on a failure.
		return !fseek($this->handle, $offset, $whence);
	}

	/**
	* Support for fflush().
	*
	* @return bool 	true if data was successfully stored (or there was no data to store).
	*
	* @see http://php.net/manual/streamwrapper.stream-flush.php
	*/
	public function stream_flush()
	{
		return fflush($this->handle);
	}

	/**
	* Support for ftell().
	*
	* @return bool 	The current offset in bytes from the beginning of file.
	*
	* @see http://php.net/manual/streamwrapper.stream-tell.php
	*/
	public function stream_tell()
	{
		return ftell($this->handle);
	}

	/**
	* Support for fstat().
	*
	* @return bool  	An array with file status, or FALSE in case of an error - see fstat()
	*	                for a description of this array.
	*
	* @see http://php.net/manual/streamwrapper.stream-stat.php
	*/
	public function stream_stat()
	{
		return fstat($this->handle);
	}

	/**
	* Support for fclose().
	*
	* @return bool 	true if stream was successfully closed.
	*
	* @see http://php.net/manual/streamwrapper.stream-close.php
	*/
	public function stream_close()
	{
		return fclose($this->handle);
	}

	/**
	* Gets the underlying stream resource for stream_select().
	*
	* @param int $cast_as 	Can be STREAM_CAST_FOR_SELECT or STREAM_CAST_AS_STREAM.
	*
	* @return resource|false 	The underlying stream resource or FALSE if stream_select() is not
	*	                        supported.
	*
	* @see http://php.net/manual/streamwrapper.stream-cast.php
	*/
	public function stream_cast($cast_as)
	{
		return false;
	}

	/**
	* Support for unlink().
	*
	* @param string $uri 	A string containing the URI to the resource to delete.
	*
	* @return bool 	true if resource was successfully deleted.
	*
	* @see http://php.net/manual/streamwrapper.unlink.php
	*/
	public function unlink($uri)
	{
		$this->uri = $uri;
		
		return unlink($this->getLocalPath($uri));
	}

	/**
	* Support for rename().
	*
	* @param string $from_uri 	The URI to the file to rename.
	* @param string $to_uri		The new URI for file.
	*
	* @return bool 	true if file was successfully renamed.
	*
	* @see http://php.net/manual/streamwrapper.rename.php
	*/
	public function rename($from_uri, $to_uri)
	{
		return rename($this->getLocalPath($from_uri), $this->getLocalPath($to_uri));
	}

	/**
	* Gets the name of the directory from a given path.
	*
	* @param string $uri 	A URI or path.
	*
	* @return string 	A string containing the directory name.
	*/
	public function dirname($uri = NULL)
	{
		list($scheme, $target) = explode('://', $uri, 2);
		$target	= $this->getTarget($uri);
		$dirname = dirname($target);

		if ($dirname == '.') {
			$dirname = '';
		}

		return $scheme . '://' . $dirname;
	}

	/**
	* Support for mkdir().
	*
	* @param string $uri 	A string containing the URI to the directory to create.
	* @param int $mode  	Permission flags - see mkdir().
	* @param int $options 	A bit mask of STREAM_REPORT_ERRORS and STREAM_MKDIR_RECURSIVE.
	*
	* @return bool 	true if directory was successfully created.
	*
	* @see http://php.net/manual/streamwrapper.mkdir.php
	*/
	public function mkdir($uri, $mode, $options)
	{
		$this->uri = $uri;
		$recursive = (bool) ($options & STREAM_MKDIR_RECURSIVE);
		$localpath = $this->getLocalPath($uri);

		if ($options & STREAM_REPORT_ERRORS) {
			return mkdir($localpath, $mode, $recursive);
		}
		
		return mkdir($localpath, $mode, $recursive);
	}

	/**
	* Support for rmdir().
	*
	* @param string $uri 	A string containing the URI to the directory to delete.
	* @param int $options 	A bit mask of STREAM_REPORT_ERRORS.
	*
	* @return bool 	true if directory was successfully removed.
	*
	* @see http://php.net/manual/streamwrapper.rmdir.php
	*/
	public function rmdir($uri, $options)
	{
		$this->uri = $uri;
		if ($options & STREAM_REPORT_ERRORS) {
			return rmdir($this->getLocalPath($uri));
		}
		
		return rmdir($this->getLocalPath($uri));
	}

	/**
	* Support for stat().
	*
	* @param string $uri 	A string containing the URI to get information about.
	* @param int $flags 	A bit mask of STREAM_URL_STAT_LINK and STREAM_URL_STAT_QUIET.
	*
	* @return array 	An array with file status, or FALSE in case of an error - see fstat()
	*	                for a description of this array.
	*
	* @see http://php.net/manual/streamwrapper.url-stat.php
	*/
	public function url_stat($uri, $flags)
	{
		$this->uri = $uri;
		$path = $this->getLocalPath($uri);

		// Suppress warnings if requested or if the file or directory does not
		// exist. This is consistent with PHP's plain filesystem stream wrapper.
		if ($flags & STREAM_URL_STAT_QUIET || !file_exists($path)) {
			return @stat($path);
		}
		
		return stat($path);
	}

	/**
	* Support for opendir().
	*
	* @param string $uri 	A string containing the URI to the directory to open.
	* @param int $options 	Unknown (parameter is not documented in PHP Manual).
	*
	* @return bool 	true on success.
	*
	* @see http://php.net/manual/streamwrapper.dir-opendir.php
	*/
	public function dir_opendir($uri, $options)
	{
		$this->uri = $uri;
		$this->handle = opendir($this->getLocalPath($uri));

		return (bool) $this->handle;
	}

	/**
	* Support for readdir().
	*
	* @return string 	The next filename, or FALSE if there are no more files in the directory.
	*
	* @see http://php.net/manual/streamwrapper.dir-readdir.php
	*/
	public function dir_readdir()
	{
		return readdir($this->handle);
	}

	/**
	* Support for rewinddir().
	*
	* @return bool 	true on success.
	*
	* @see http://php.net/manual/streamwrapper.dir-rewinddir.php
	*/
	public function dir_rewinddir()
	{
		rewinddir($this->handle);

		// We do not really have a way to signal a failure as rewinddir() does not
		// have a return value and there is no way to read a directory handler
		// without advancing to the next file.
		return true;
	}

	/**
	* Support for closedir().
	*
	* @return bool 	true on success.
	*
	* @see http://php.net/manual/streamwrapper.dir-closedir.php
	*/
	public function dir_closedir()
	{
		closedir($this->handle);

		// We do not really have a way to signal a failure as closedir() does not
		// have a return value.
		return true;
	}
}