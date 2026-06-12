<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProductionUnit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class ProductionUnitCrudController extends AbstractCrudController
{
    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('producer');
        yield TextField::new('eicCode');
        yield TextField::new('name');
        yield TextField::new('location');

        $subUnitsField = CollectionField::new('subUnits');
        $valuesField = CollectionField::new('values');

        if (Crud::PAGE_DETAIL === $pageName) {
            $subUnitsField->setTemplatePath('admin/production_unit/sub_units.html.twig');
            $valuesField->setTemplatePath('admin/production_unit/values.html.twig');
        }

        yield $subUnitsField;
        yield $valuesField;

        yield Field::new('productionChart')
                   ->onlyOnDetail()
                   ->setTemplatePath('admin/production_unit/production_chart.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
                     ->disable(Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE)
                     ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
                     ->setEntityLabelInSingular('Unité de production')
                     ->setEntityLabelInPlural('Unités de production')
                     ->setSearchFields(['name', 'eicCode'])
                     ->setDefaultSort(['name' => 'ASC'])
                     ->setPaginatorPageSize(100)
                     ->showEntityActionsInlined();
    }

    public static function getEntityFqcn(): string
    {
        return ProductionUnit::class;
    }
}
