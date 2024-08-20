<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class AppointmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Appointment::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')
                ->setLabel('Nom'),
            EmailField::new('email')
                ->setLabel('E-mail'),
            TextareaField::new('message')
                ->setLabel('Message'),
            DateTimeField::new('startDate')->setFormat('dd/MM/Y à hh:mm')
                ->setLabel('Date de début'),
            DateTimeField::new('endDate')->setFormat('dd/MM/Y à hh:mm')
                ->setLabel('Date de fin'),
            DateTimeField::new('createdAt')->setFormat('dd/MM/Y à hh:mm')
                ->setLabel('Créé le'),
            AssociationField::new('user')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
            ]),
            AssociationField::new('services')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
            ]),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['startDate' => 'DESC']); // Tri par date de début, ordre décroissant
    }
}
