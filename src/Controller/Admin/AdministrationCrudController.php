<?php

namespace App\Controller\Admin;

use App\Entity\Administration;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class AdministrationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Administration::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextEditorField::new('title')
            ->setTrixEditorConfig([
                'blockAttributes' => [
                    'default' => ['tagName' => 'p'],
                    'heading1' => ['tagName' => 'h2'],
                    'code' => ['tagName' => 'h3'],
                    'quote' => ['tagName' => 'h4']
                ],
            ])
            ->setFormTypeOption('attr', ['class' => 'trix-content']),
            TextEditorField::new('textContent')
            ->setTrixEditorConfig([
                'blockAttributes' => [
                    'default' => ['tagName' => 'p'],
                ],
            ])
            ->setFormTypeOption('attr', ['class' => 'trix-content']),
            TextField::new('sectionLocate'),
        ];
    }
    
}
