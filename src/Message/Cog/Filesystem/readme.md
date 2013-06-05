# Filesystem component

This component provides four features for Cog:

1. A `File` object which extends `SplFileInfo` and allows us to add our own custom methods and features.
2. A wrapper around Symfony's Finder component which makes finding files in the filesystem easier and ensures that instances of `File` are always returned.
3. A wrapper around Symfony's Filesystem class which makes filesystem based operations testable.
4. A StreamWrapper which allows us to map file paths such as `cog://images/bob.jpg` into real paths on a filesystem (such as `/var/www/example.org/public/images/bob.jpg`).

## `File` object

This is currently a simple class which extends SplFileInfo.  The only additional method is `getChecksum()` which returns the MD5 hash of the file.

## Finder wrapper

This works exactly like Symfony's Finder except you need to initialise an instance of `Message\Cog\Filesystem\Finder`.

    $finder = new Message\Cog\Filesystem\Finder();
    $finder->name('photos*')->size('< 100K')->date('since 1 hour ago');
    foreach ($finder->in('cog://images') as $file) {
    	print $file->getFilename()."\n";
    }

For more details on usage see http://symfony.com/doc/master/components/finder.html

## Filesystem wrapper

    $fs = new Message\Cog\Filesystem\Filesystem;
    $fs->mkdir('/tmp/photos', 0777);

Again, this works just like Symfony's Filesystem component except you need to create an instance of `Message\Cog\Filesystem\Filesystem`

For more details see http://symfony.com/doc/master/components/filesystem.html

## StreamWrapper manager

StreamWrappers map custom URIs (like `cog://images/bob.jpg`) into real paths (like `/var/www/example.org/public/images/bob.jpg`). The benefit of this is that the location of the file is abtracted from it's true location on disk. In the future if you needed to move the image files to a different directory you can just change where `cog://images/` maps to rather than having to update paths in strings all over your app. The mapping is extremely flexible as it uses regular expressions; for example (if you're crazy) you might want to serve files ending in .jpg from a different server.

StreamWrappers also support using Cog's `ReferenceParser` class so modules can easily work with files inside their respective folders without having to worry where they are on the underlying filesystem.

Here's a complete example using `bob://` as the stream prefix:

    $manager = new StreamWrapperManager();
    
    $manager->register('bob', function() {
    	$wrapper = new StreamWrapper;
    	$wrapper->setMapping(array(
    		"/^\/tmp\/(.*)/us" => /tmp/$1',
    	));
    	$wrapper->setReferenceParser(new ReferenceParser);
    
    	return $wrapper;
    });
    
    // Once registered you can now do all of the following
    $contents = file_get_contents('bob://tmp/hello.txt');
    file_put_contents('bob://tmp/poem.txt', 'A short poem');
    unlink('bob://tmp/poem.txt');
    $rawPdf = file_get_contents('bob://UniformWares:CustomModuleName::assets/example.pdf');

## Todo

- Move StreamWrapper based classes into their own namespace/directory, rather than just living in `Filesystem`.
- Write more tests for the StreamWrapper.
- Add more helper methods to `File`