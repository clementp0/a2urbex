<?php

namespace App\Controller\Admin;

use App\Entity\Locations;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class LocationsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Locations::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('pid' , 'Pin ID'),
            TextEditorField::new('description'),
            TextField::new('url'),
            TextField::new('image'),
            TextField::new('lon', 'Longitude'),
            TextField::new('lat', 'Latitude'),
            AssociationField::new('Country'),
            AssociationField::new('Type'),
        ];
    }
    
}
