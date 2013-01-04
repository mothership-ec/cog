# Service Component

This component is responsible for all things relating to application security.

## Hashing

The namespace `Hash` within this component holds classes for dealing with the encryption and matching of hashes using a variety of algorithms. The following algorithms are included within Cog:

* `Bcrypt` This should be considered the standard for securely hashing strings, it is the most secure of the default Cog hash algorithms.
* `SHA1` This is a super-simple SHA1 hashing algorithm. It should really be avoided unless your application is dealing with ported SHA1 hashes.
* `OSCommerce` This is an implementation of OSCommerce's custom hashing algorithm, useful for when you're dealing with data ported from OSCommerce.

### Creating & using a custom Hash algorithm

Any module can create & set the system to use a custom hashing algorithm class. The class must implement the `Message\Cog\Security\Hash\HashInterface` interface.

This should then be set as the `security.hash` service as early as possible. It makes sense to do this in the service bootstrap of the first module loaded (likely the installation configuration module) because other modules may well use the `security.hash` service.