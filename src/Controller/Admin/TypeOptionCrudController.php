<?php

namespace App\Controller\Admin;

use App\Entity\TypeOption;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TypeOptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeOption::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
