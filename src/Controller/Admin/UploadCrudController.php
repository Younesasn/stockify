<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Trait\DisableNewTrait;
use App\Entity\Upload;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UploadCrudController extends AbstractCrudController
{
    use DisableNewTrait;
    public static function getEntityFqcn(): string
    {
        return Upload::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ImageField::new('filename')->setBasePath('uploads/' . $this->getUser()->getDirectoryName())->setUploadDir('uploads/' . $this->getUser()->getDirectoryName()),
            TextField::new('originalFilename'),
            TextField::new('extension'),
            AssociationField::new('category'),
            NumberField::new('size')->hideOnForm(),
            DateField::new('date')->hideOnForm(),
            AssociationField::new('user')
        ];
    }
}
