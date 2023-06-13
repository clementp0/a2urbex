<?php

namespace App\Controller\Admin;

use App\Entity\CategoryOption;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;


class CategoryOptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CategoryOption::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id'),
            TextField::new('name'),
            AssociationField::new('category'),
        ];
    }
    
}
