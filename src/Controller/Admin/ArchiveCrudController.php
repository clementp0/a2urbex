<?php

namespace App\Controller\Admin;

use App\Entity\Archive;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ArchiveCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Archive::class;
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
