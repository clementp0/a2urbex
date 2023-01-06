<?php

namespace App\Controller\Admin;

use App\Entity\ResumeEntity;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;


class ResumeEntityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ResumeEntity::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextField::new('description'),
            TextField::new('compagny', 'Company'),
            TextField::new('duration'),
            DateField::new('datefrom', 'From'),
            DateField::new('dateto', 'To'),
            AssociationField::new('Lang', 'Lang'),
        ];
    }
    
}
