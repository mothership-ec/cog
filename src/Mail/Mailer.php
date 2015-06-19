<?php

namespace Message\Cog\Mail;

class Mailer
{
	protected $_swiftMailer;
	protected $_whitelist = array();
	protected $_whitelistFallback;
	protected $_whitelistEnabled = false;

	/**
	 * List of valid email characters that would need to be escaped to be part of a regex
	 *
	 * @var array
	 */
	private $_escapeCharacters = [
		'.',
		'-',
		'$',
		'&',
		'(',
		')',
		'*',
		'+',
	];

	public function __construct(\Swift_Mailer $swiftMailer)
	{
		$this->_swiftMailer = $swiftMailer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(MailableInterface $mailable, &$failedRecipients = null)
    {
    	$message = $mailable->getMessage();

    	if ($this->_whitelistEnabled) {
	    	// Get the original 'to' addresses
	    	$original = $message->getTo();

	    	// Filter these against the whitelist
	    	$addresses = $this->_whitelistFilter($original);

	    	// Set the new 'to' addresses
	    	$message->setTo($addresses);

	    	// Only attach the 'Original-To' header if this email is only sending to
			// the fallback, so we do not accidently share addresses with other
			// users.
			if (1 === count($addresses) and $this->_whitelistFallback === key($addresses)) {
				$message->getHeaders()->addMailboxHeader(
					'Original-To', $original
				);
			}
	    }

    	return $this->_swiftMailer->send($message, $failedRecipients);
    }

    /**
     * Enable the whitelist filtering on 'to' addresses
     *
     * @return void
     */
    public function enableToFiltering()
    {
    	$this->_whitelistEnabled = true;
    }

    /**
     * Disable the whitelist filtering on 'to' addresses
     *
     * @return void
     */
    public function disableToFiltering()
    {
    	$this->_whitelistEnabled = false;
    }

	/**
	 * Set the fallback email address that is used when an address does not
	 * match any whitelist options.
	 *
	 * @param  string $address
	 * @return void
	 */
	public function setWhitelistFallback($address)
	{
		$this->_whitelistFallback = $address;
	}

	/**
	 * Get the whitelist
	 *
	 * @return array
	 */
	public function getWhitelist()
	{
		return $this->_whitelist;
	}

	/**
	 * Set the whitelist
	 *
	 * @param  array $whitelist
	 * @return void
	 */
	public function setWhitelist($whitelist)
	{
		$this->_whitelist = $whitelist;
	}

	/**
	 * Add a regex test to the whitelist
	 *
	 * @param array | string $whitelist
	 */
	public function addToWhitelist($whitelist)
	{
		if (!$whitelist) {
			return;
		}

		if (!is_array($whitelist) && !is_string($whitelist)) {
			throw new \InvalidArgumentException('Whitelist must be either an array or string');
		}

		$regex = $this->_parseWhitelist((array) $whitelist);

		$this->_whitelist = array_merge($this->_whitelist, $regex);
	}

	/**
	 * Filter an array of addresses against the whitelist.
	 *
	 * @param  array $addresses
	 * @return array
	 */
	protected function _whitelistFilter(array $addresses)
	{
		$filtered = array();

		// Filter each address
		foreach ($addresses as $address => $name) {
			$matched = false;

			// Check against each whitelist regex test
			foreach ($this->_whitelist as $regex) {
				$matched = (bool) preg_match($regex, $address);
				if ($matched) break;
			}

			// If the address did not match any of the tests, replace it with
			// the fallback address.
			if (false === $matched) {
				$address = $this->_whitelistFallback;
			}

			$filtered[$address] = $name;
		}

		return $filtered;
	}

	/**
	 * Convert whitelist array into a list of regular expressions
	 *
	 * @param array $whitelist   Whitelist to convert to regular expressions
	 *
	 * @return array
	 */
	private function _parseWhitelist(array $whitelist)
	{
		array_walk($whitelist, function(&$item) {

			if (!is_string($item)) {
				throw new \LogicException('Whitelist array must be made up of strings');
			}

			// Check if first character is a forward slash, assume it is already a regex if so
			if (0 !== strpos($item, '/')) {
				// Escape characters for regex
				foreach ($this->_escapeCharacters as $char) {
					$item = str_replace($char, '\\' . $char, $item);
				}

				// Case to string for faux-strict typing.
				switch ((string) strpos($item, '@')) {
					// If @ is not present, add a wildcard to each end of the string
					case '':
						$item = '/.+' . $item . '.+/';
						break;
					// If @ is the first character, add a wildcard to the start of the string
					case '0':
						$item = '/.+' . $item . '/';
						break;
					// If @ is present but not the first character, assume it is a whole email address and add no wildcards
					default:
						$item = '/' . $item . '/';
						break;
				}
			}
		});

		return $whitelist;
	}
}