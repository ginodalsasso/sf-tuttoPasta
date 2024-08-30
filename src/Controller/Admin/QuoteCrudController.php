<?php

namespace App\Controller\Admin;

use App\Entity\Quote;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class QuoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quote::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('reference')
            ->setLabel('Référence'),
            DateTimeField::new('quoteDate')
            ->setLabel('Date du devis'),
            TextField::new('customerName')
            ->setLabel('Nom du client'),
            TextField::new('customerFirstName')
            ->setLabel('Prénom du client'),
            TextField::new('customerEmail')
            ->setLabel('E-mail du client'),        
            AssociationField::new('appointments')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->setCrudController(AppointmentCrudController::class)
                ->setLabel('Rendez-vous'),
        ];
    }
    
}
