<?php

namespace App\Controller\Admin;

use App\Entity\Quote;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class QuoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quote::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('reference')
            ->setLabel('Référence'),
            DateTimeField::new('quoteDate')
            ->setLabel('Date du devis'),
            NumberField::new('total_ttc')
            ->setLabel('Total TTC'),
            TextField::new('customerName')
            ->setLabel('Nom du client'),
            TextField::new('customerFirstName')
            ->setLabel('Prénom du client'),
            TextField::new('customerEmail')
            ->setLabel('E-mail du client'),        
            AssociationField::new('appointments')
                ->setFormTypeOptions([
                    'by_reference' => true,
                ])
                ->setCrudController(AppointmentCrudController::class)
                ->setLabel('Rendez-vous'),
            TextField::new('state')
            ->setLabel('Etat')
        ];
    }
    
}
