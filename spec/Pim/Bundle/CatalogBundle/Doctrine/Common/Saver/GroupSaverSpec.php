<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;

class GroupSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        BulkSaverInterface $productSaver,
        ProductTemplateApplierInterface $templateApplier,
        SavingOptionsResolverInterface $optionsResolver,
        VersionContext $versionContext
    ) {
        $this->beConstructedWith(
            $objectManager,
            $productSaver,
            $templateApplier,
            $versionContext,
            $optionsResolver,
            'Pim\Bundle\CatalogBundle\Model'
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_saves_a_group_and_flushes_by_default(
        $objectManager,
        $optionsResolver,
        GroupInterface $group,
        GroupType $type
    ) {
        $optionsResolver->resolveSaveOptions([])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => false,
                'add_products'            => [],
                'remove_products'         => [],
            ]
        );

        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->save($group);
    }

    function it_saves_a_group_and_added_products(
        $optionsResolver,
        $objectManager,
        $productSaver,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $addedProduct
    ) {
        $optionsResolver->resolveSaveOptions(['add_products' => [$addedProduct]])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => false,
                'add_products'            => [$addedProduct],
                'remove_products'         => [],
            ]
        );

        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver
            ->saveAll([$addedProduct], ['recalculate' => false, 'schedule' => false])
            ->shouldBeCalled();

        $this->save($group, ['add_products' => [$addedProduct]]);
    }

    function it_saves_a_group_and_removed_products(
        $optionsResolver,
        $objectManager,
        $productSaver,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $removedProduct
    ) {
        $optionsResolver->resolveSaveOptions(['remove_products' => [$removedProduct]])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => false,
                'add_products'            => [],
                'remove_products'         => [$removedProduct],
            ]
        );

        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver
            ->saveAll([$removedProduct], ['recalculate' => false, 'schedule' => false])
            ->shouldBeCalled();

        $this->save($group, ['remove_products' => [$removedProduct]]);
    }

    function it_saves_a_variant_group_and_copies_values_to_products(
        $optionsResolver,
        $objectManager,
        $templateApplier,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $product,
        ProductTemplateInterface $template,
        ArrayCollection $products
    ) {
        $optionsResolver->resolveSaveOptions(['copy_values_to_products' => true])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => true,
                'add_products'            => [],
                'remove_products'         => [],
            ]
        );

        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $type->isVariant()->willReturn(true);
        $group->getProductTemplate()->willReturn($template);
        $group->getProducts()->willReturn($products);
        $products->toArray()->willReturn([$product]);

        $templateApplier
            ->apply($template, [$product])
            ->shouldBeCalled();

        $this->save($group, ['copy_values_to_products' => true]);
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Model\GroupInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
