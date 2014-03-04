<?php

namespace Message\Cog\Form\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Own entity choice list, as we don't use Doctrine.
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Doctrine/Form/Type/DoctrineType.php
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class EntityType extends AbstractType
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $_propertyAccessor;

    /**
     * @var array
     */
    protected $_choiceListCache = array();

    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->_propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * Adds the 'errors_with_fields'-option and sets a default for it.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choiceListCache =& $this->_choiceListCache;
        $propertyAccessor = $this->_propertyAccessor;

        $resolver->setRequired(['choices']);
        $resolver->setOptional(['property_path', 'value_path']);

        $choiceList = function(Options $options) use (&$choiceListCache, $propertyAccessor) {
            $choiceHashes = $options['choices'];

            // Support for recursive arrays
            if (is_array($choiceHashes)) {
                // A second parameter ($key) is passed, so we cannot use
                // spl_object_hash() directly (which strictly requires
                // one parameter)
                array_walk_recursive($choiceHashes, function (&$value) {
                    $value = spl_object_hash($value);
                });
            } elseif ($choiceHashes instanceof \Traversable) {
                $hashes = array();
                foreach ($choiceHashes as $value) {
                    $hashes[] = spl_object_hash($value);
                }

                $choiceHashes = $hashes;
            }

            $preferredChoiceHashes = $options['preferred_choices'];
            if (is_array($preferredChoiceHashes)) {
                array_walk_recursive($preferredChoiceHashes, function (&$value) {
                    $value = spl_object_hash($value);
                });
            }

            // Support for closures
            $propertyHash = is_object($options['property_path'])
                ? spl_object_hash($options['property_path'])
                : $options['property_path'];

            // Support for closures
            $valueHash = is_object($options['value_path'])
                ? spl_object_hash($options['value_path'])
                : $options['value_path'];

            // hash for choicelist
            $hash = md5(json_encode([
                $choiceHashes,
                $propertyHash,
                $preferredChoiceHashes,
                $valueHash
            ]));

            if (!isset($choiceListCache[$hash])) {
                $choiceListCache[$hash] = new ObjectChoiceList(
                    $options['choices'],
                    $options['property_path'],
                    $options['preferred_choices'],
                    null, // grouped by
                    $options['value_path'],
                    $propertyAccessor
                );
            }

            return $choiceListCache[$hash];
        };

        $resolver->setDefaults(array(
            'property_path' => null,        // property used for labelling or closure
            'choice_list'   => $choiceList,
            'value_path'    => 'id',        // unique identifier property or closure
        ));
    }


    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'entity';
    }
}
