<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

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
            TextField::new('lat', 'Latitude'),
            TextField::new('lon', 'Longitude'),
            BooleanField::new('done'),
            TextField::new('comments'),
            AssociationField::new('country'),
            AssociationField::new('type'),
        ];
    }
    
}
