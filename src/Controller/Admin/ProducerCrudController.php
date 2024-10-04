<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Producer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class ProducerCrudController extends AbstractCrudController
{
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('eicCode');
        yield TextField::new('name');
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
                     ->setEntityLabelInSingular('Producteur')
                     ->setEntityLabelInPlural('Producteurs')
                     ->setSearchFields(['name', 'eicCode'])
                     ->setDefaultSort(['name' => 'ASC'])
                    ->setPaginatorPageSize(100)
                     ->showEntityActionsInlined();
    }

    public static function getEntityFqcn(): string
    {
        return Producer::class;
    }
}
