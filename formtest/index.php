<?php
 
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Templating\TemplatingExtension;

use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;

use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\Loader\FilesystemLoader;

use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;


 
require __DIR__ . '/../vendor/autoload.php';

class SimpleTemplateNameParser implements TemplateNameParserInterface
{
    private $root;
 
    public function __construct($root)
    {
        $this->root = $root;
    }
 
    public function parse($name)
    {
        if (false !== strpos($name, ':')) {
            $path = str_replace(':', '/', $name);
        } else {
            $path = $this->root . '/' . $name;
        }
 
        return new TemplateReference($path, 'php');
    }
}
 
// Overwrite this with your own secret
$csrfSecret = 'c2ioeEU1n48QF2WsHGWd2HmiuUUT6dxr';
$engine = new PhpEngine(new SimpleTemplateNameParser(realpath(__DIR__ )), new FilesystemLoader(array()));

$engine->addHelpers(array(
    new \Message\Cog\Form\Template\FormHelper(new FormRenderer(new TemplatingRendererEngine($engine, array(
        // Will hopefully not be necessary anymore in 2.2
        realpath(__DIR__ . '/../src/Message/Cog/Form/Views/Php'),
    )), null))
));
 
// Set up the form factory with all desired extensions
$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfExtension(new DefaultCsrfProvider($csrfSecret)))
    ->getFormFactory();
 
// Create our first form!
$form = $formFactory->createBuilder()
    ->add('firstName', 'text', array(
    ))
    ->add('lastName', 'text', array(
    ))
    ->add('gender', 'choice', array(
        'choices' => array('m' => 'Male', 'f' => 'Female'),
    ))
    ->add('newsletter', 'checkbox', array(
        'required' => false,
    ))
    ->getForm();


    $_POST['form'] = array(
    	'firstName' => 'James',
    );
 
if (isset($_POST[$form->getName()])) {
    $form->bind($_POST[$form->getName()]);
 
    if ($form->isValid()) {
        var_dump('VALID', $form->getData());
        die;
    }
}
 
echo $engine->render('index.html.php', array(
    'form' => $form->createView(),
));