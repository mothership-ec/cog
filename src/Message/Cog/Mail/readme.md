# Mail

The Mail wrapper allows us to send mail within Cog using Swiftmailer.

See Swiftmailer docs here:

    [http://swiftmailer.org/docs/introduction.html](http://swiftmailer.org/docs/introduction.html)

# Building an email to send

    // New instance of a message
    $message = $this->get('mail.message');

    // You can use anything within SwiftMailer here.
    $message->setTo('joe@message.co.uk', 'Joe Holdcroft');
    $message->setFrom('test@message.co.uk');

    // Set View has been added to Cog so that we can parse Views for the content.
    $message->setView('UniformWares:CMS::modules/mail', $params = array());

    // Get the dispatch method
    $dispatcher = $this->get('mail.dispatcher');

    // Send the message
    $result = $dispatcher->send($message);

# Views

    You can set a view the same way in Controllers.

        $message->setView('UniformWares:CMS::modules/mail', $params = array());

    You can also define multiple views for the same template, by having different extensions.

    Example: Use .html and .txt templates to differentiate between HTML and plain text content.

# Transports

    You can set up different transports within the services bootstrap.

    Currently Mail and SMTP are available

    // Mail

        $serviceContainer['mail.transport'] = $serviceContainer->share(function($c) {
            return new \Message\Cog\Mail\Transport\Mail();
        });

    // SMTP

        $serviceContainer['mail.transport'] = $serviceContainer->share(function($c) {
            $transport = new \Message\Cog\Mail\Transport\SMTP('mail.message.co.uk', 25);

            $transport->setUsername('test');
            $transport->setPassword('testpw');

            return $transport;
        });



