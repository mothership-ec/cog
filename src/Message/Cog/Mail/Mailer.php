<?php

namespace Message\Cog\Mail;

class Mailer
{
	protected $_swiftMailer;
	protected $_whitelist = array();
	protected $_whitelistFallback;
	protected $_whitelistEnabled = false;

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
	 * @param  array $walterWhite
	 * @return void
	 */
	public function setWhitelist($walterWhite)
	{
		$this->_whitelist = $walterWhite;
	}

	/**
	 * Add a regex test to the whitelist
	 *
	 * @param string $regex
	 */
	public function addToWhitelist($regex)
	{
		$regex = is_array($regex) ? $regex : array($regex);

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

		// If there are any whitelist tests
		if (count($this->_whitelist) > 0) {

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
		}
		else {
			$filtered = $addresses;
		}

		return $filtered;
	}
}