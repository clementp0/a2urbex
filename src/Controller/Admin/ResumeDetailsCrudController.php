<?php

namespace App\Controller\Admin;

use App\Entity\ResumeDetails;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;


class ResumeDetailsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ResumeDetails::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('Title'),
            TextField::new('SubTitle'),
            TextField::new('Profile'),
            TextField::new('Skills_dev'),
            TextField::new('Skills_graphics'),
            TextField::new('Diplomas'),
            AssociationField::new('Lang', 'Lang'),
        ];
    }
    
}
