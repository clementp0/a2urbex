<?php

namespace App\Controller\Admin;

use App\Entity\TypeOption;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;


class TypeOptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeOption::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            AssociationField::new('type'),
        ];
    }
    
}
