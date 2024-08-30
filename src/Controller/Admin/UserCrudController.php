<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email')
                ->setLabel('E-mail'),
            TextField::new('username')
                ->setLabel('Nom d\'utilisateur'),
            AssociationField::new('appointments')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    
                ])
                ->setCrudController(AppointmentCrudController::class)
                ->setLabel('Rendez-vous'),

        ];
    }
}
