<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AppointmentType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email')
                ->setLabel('E-mail'),
            TextField::new('username')
                ->setLabel('Nom d\'utilisateur'),
            CollectionField::new('appointments')
            ->setEntryType(AppointmentType::class)
            ->allowAdd(true) // Permet l'ajout de rendez-vous
            ->allowDelete(true) // Permet la suppression des rendez-vous
            ->setFormTypeOptions([
                'by_reference' => false, 
            ])
            ->setLabel('Rendez-vous'),
        ];
    }
}
