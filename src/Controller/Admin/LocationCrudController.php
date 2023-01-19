<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class LocationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Location::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('pid' , 'Pin ID'),
            TextField::new('name'),
            TextField::new('image'),
            TextField::new('lon', 'Longitude'),
            TextField::new('lat', 'Latitude'),
            AssociationField::new('country'),
            AssociationField::new('type'),
        ];
    }
    
}
