<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class ProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('projectName')
                ->setLabel('Nom du projet'),
            TextField::new('projectTitle')
                ->setLabel('Titre du projet'),
            TextEditorField::new('projectContent')
                ->setLabel('Description du projet'),
            DateTimeField::new('projectDate')->setFormat('dd/MM/Y à hh:mm')
                ->setLabel('Date de publication'),
            AssociationField::new('categories')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
            ]),
            AssociationField::new('images')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
            ]),
        
            TextField::new('slug'),
        ];
    }
    
}
