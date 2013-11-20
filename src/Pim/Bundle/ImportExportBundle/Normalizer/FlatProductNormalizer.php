<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Media;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * A normalizer to transform a product entity into a flat array
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatProductNormalizer implements NormalizerInterface
{
    /** @staticvar string */
    const FIELD_FAMILY = 'family';

    /** @staticvar string */
    const FIELD_GROUPS = 'groups';

    /** @staticvar string */
    const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    const ITEM_SEPARATOR = ',';

    /** @var Pim\Bundle\CatalogBundle\Manager\MediaManager */
    protected $mediaManager;

    /** @var array */
    protected $supportedFormats = array('csv');

    /** @var array */
    protected $results;

    /**
     * Fields to export if needed
     * @var array
     */
    protected $fields = array();

    /**
     * Constructor
     *
     * @param Pim\Bundle\CatalogBundle\Manager\MediaManager $mediaManager
     */
    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $scopeCode = null;
        if (isset($context['scopeCode'])) {
            $scopeCode = $context['scopeCode'];
        }

        $this->results = $this->normalizeValue($object->getIdentifier());

        $this->normalizeFamily($object->getFamily());

        $this->normalizeGroups($object->getGroupCodes());

        $this->normalizeCategories($object->getCategoryCodes());

        $normalizedValues = $this->normalizeValues($object, $scopeCode);

        $this->results = array_merge($this->results, $normalizedValues);

        return $this->results;
    }

    /**
     * Normalize values
     *
     * @param ProductInterface $product
     * @param string           $scopeCode
     *
     * @return array
     */
    protected function normalizeValues(ProductInterface $product, $scopeCode)
    {
        $identifier = $product->getIdentifier();

        $filteredValues = $product->getValues()->filter(
            function ($value) use ($identifier, $scopeCode) {
                return (
                    ($value !== $identifier) &&
                    (
                        ($scopeCode == null) ||
                        (!$value->getAttribute()->getScopable()) ||
                        ($value->getAttribute()->getScopable() && $value->getScope() == $scopeCode)
                    )
                );
            }
        );

        $normalizedValues = array();
        foreach ($filteredValues as $value) {
            $normalizedValues = array_merge(
                $normalizedValues,
                $this->normalizeValue($value)
            );
        }
        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalizes a value
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function normalizeValue($value)
    {
        $data = $value->getData();
        if (empty($this->fields) || isset($this->fields[$this->getFieldValue($value)])) {
            if (is_bool($data)) {
                $data = ($data) ? 1 : 0;
            } elseif ($data instanceof \DateTime) {
                $data = $data->format('m/d/Y');
            } elseif ($data instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption) {
                $data = $data->getCode();
            } elseif ($data instanceof \Doctrine\Common\Collections\Collection) {
                $data = $this->normalizeCollectionData($data);
            } elseif ($data instanceof Media) {
                $data = $this->mediaManager->getExportPath($data);
            }
        }

        return array($this->getFieldValue($value) => (string) $data);
    }

    /**
     * Normalize the field name for values
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    protected function getFieldValue($value)
    {
        $suffix = '';

        if ($value->getAttribute()->getTranslatable()) {
            $suffix = sprintf('-%s', $value->getLocale());
        }
        if ($value->getAttribute()->getScopable()) {
            $suffix .= sprintf('-%s', $value->getScope());
        }

        return $value->getAttribute()->getCode() . $suffix;
    }

    /**
     * Normalize the value collection data
     *
     * @param array $data
     *
     * @return string
     */
    protected function normalizeCollectionData($data)
    {
        $result = array();
        foreach ($data as $item) {
            if ($item instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption) {
                $result[] = $item->getCode();
            } else {
                $result[] = (string) $item;
            }
        }
        $data = join(self::ITEM_SEPARATOR, $result);

        return $data;
    }

    /**
     * Normalizes a family
     *
     * @param Family $family
     */
    protected function normalizeFamily(Family $family = null)
    {
        if (empty($this->fields) || isset($this->fields[self::FIELD_FAMILY])) {
            $this->results[self::FIELD_FAMILY] = $family ? $family->getCode() : '';
        }
    }

    /**
     * Normalizes groups
     *
     * @param Group[] $groups
     */
    protected function normalizeGroups($groups = null)
    {
        if (empty($this->fields) || isset($this->fields[self::FIELD_GROUPS])) {
            $this->results[self::FIELD_GROUPS] = $groups;
        }
    }

    /**
     * Normalizes categories
     *
     * @param string $categories
     */
    protected function normalizeCategories($categories = '')
    {
        if (empty($this->fields) || isset($this->fields[self::FIELD_CATEGORY])) {
            $this->results[self::FIELD_CATEGORY] = $categories;
        }
    }
}
