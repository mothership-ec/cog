<?php

namespace Message\Cog\Form;

use Symfony\Component\Form\Form as SymfonyForm;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper;
use \Message\Cog\Service\ContainerInterface;

/**
 * Class Form
 * @package Message\Cog\Form
 *
 * Adaptation of Symfony's form component, simplified and customised to work with Cog and Mothership.
 * Upon instanciation, it is a blank class that cannot be used, you must run the setup() method to
 * define the name and config setup. The reason for this is to allow it to be called via the service
 * container.
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Form extends SymfonyForm
{
	/**
	 * @var FormHelper
	 */
	protected $_helper;

	/**
	 * Assign the service container
	 *
	 * @var ContainerInterface
	 */
	protected $_container;

	/**
	 * @var ServiceProvider
	 */
	protected $_provider;

	/**
	 * Default options to assign to form config.
	 * @todo set some default options!
	 *
	 * @var array
	 */
	protected $_defaultOptions = array(
		'type' => '',
		'options' => '',
		'allow_add' => '',
		'allow_delete' => '',
		'block_name' => '',
		'read_only' => '',
		'translation_domain' => '',
		'max_length' => '',
		'pattern' => '',
		'label' => '',
		'attr' => '',
		'label_attr' => ''
	);

	/**
	 * @param ContainerInterface $serviceContainer
	 */
	public function __construct(ContainerInterface $serviceContainer)
	{
		$this->_container = $serviceContainer;
		$this->_provider = $this->_container['form.provider'];
	}

	/**
	 * @param string $name
	 * @param string $helperType
	 * @param array $options
	 *
	 * @return Form
	 */
	public function setup($name, $helperType = 'php', array $options = array())
	{
		$options = array_merge($this->_defaultOptions, $options);

		$config = new \Message\Cog\Form\ConfigBuilder(
			$name,
			'\Message\Cog\Form\Data',
			$this->_container['event.dispatcher'],
			$options
		);

//		$this->setHelper($this->_container['form.helper.' . $helperType]);

		$config->setCompound(true); // allow addition of subforms
		$config->setDataMapper($this->_container['form.data']);
		$config->setFormFactory($this->_container['form.factory']);
		$config->setType(new \Symfony\Component\Form\ResolvedFormType(
			new \Symfony\Component\Form\Extension\Core\Type\FormType()
		));

		parent::__construct($config);

		return $this;
	}

	/**
	 * @throws \Exception
	 *
	 * @return string
	 */
//	public function __toString()
//	{
//		try {
//			if (!$this->_helper) {
//				throw new \Exception('Helper not set');
//			}
//
//			if (!$this->getConfig()) {
//				throw new \Exception('Config not set, run setup() method');
//			}
//
//			$view = $this->createView();
//			$return = '';
//
//			$return .= $this->_formString($view);
//
//			var_dump($return); die();
//
//		}
//		catch (\Exception $e) {
//			return "<table>" . $e->xdebug_message . "</table>";
//
//		}
//	}

	protected function _formString(FormView $view)
	{
		$string = $this->_helper->block($view, $view->vars['id']);

		if (!empty($view->vars[$view->vars['name']])) {
			foreach($view->vars[$view->vars['name']]->children as $child) {
				$string .= $this->_formString($child);
			}
		}

		return $string;

	}

	/**
	 * @param ResolvedFormTypeInterface $type
	 *
	 * @return Form
	 */
	public function setConfigType(ResolvedFormTypeInterface $type)
	{
		$this->getConfig()->setType($type);

		return $this;
	}

	/**
	 * @param FormHelper $helper
	 *
	 * @return Form
	 */
	public function setHelper(FormHelper $helper) {
		$this->_helper = $helper;

		return $this;
	}

	/**
	 * @return ContainerInterface
	 */
	public function getContainer()
	{
		return $this->_container;
	}

	/**
	 * @param ContainerInterface $container
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	/**
	 * @return ServiceProvider
	 */
	public function getProvider()
	{
		return $this->_provider;
	}

	/**
	 * @param ServiceProvider $provider]
	 */
	public function setProvider(ServiceProvider $provider)
	{
		$this->_provider = $provider;
	}

}