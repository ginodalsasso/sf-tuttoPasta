<?php

namespace App\Controller\Admin;

use App\Entity\ProjectImg;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class ProjectImgCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectImg::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('project')
                ->setFormTypeOptions([
                    'choice_label' => 'projectName', 
                ]),
            ImageField::new('image')
                ->setUploadDir('public/img/projects/')
                ->setBasePath('img/projects/')
                // ->setUploadedFileNamePattern('[year][month][day][slug][contenthash].webp')
                ,
            TextField::new('alt'),
        ];
    }
}
