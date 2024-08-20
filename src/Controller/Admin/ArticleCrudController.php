<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Controller\Admin\CategoryCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('articleTitle')
                ->setLabel('Titre'),
            TextEditorField::new('articleContent')
                ->setLabel('Contenu')
                ->setTrixEditorConfig([
                    'blockAttributes' => [
                        'default' => ['tagName' => 'p'],
                        'heading1' => ['tagName' => 'h2'],
                        'code' => ['tagName' => 'h3']
                    ],
                ])
                ->setFormTypeOption('attr', ['class' => 'trix-content']),
            DateTimeField::new('articleDate')->setFormat('dd/MM/Y à hh:mm')
                ->setLabel('Date de publication'),
            TextField::new('slug'),
            AssociationField::new('categories')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'multiple' => true,
                ]),        
            AssociationField::new('tags')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'multiple' => true,
                ]),       
            AssociationField::new('comments')
                ->setLabel('Commentaires')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'multiple' => true,
                ]),
        ];
    }
    
}
