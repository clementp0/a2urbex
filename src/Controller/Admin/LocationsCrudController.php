<?php

namespace App\Controller\Admin;

use App\Entity\Locations;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class LocationsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Locations::class;
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
