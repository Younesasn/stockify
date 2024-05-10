<?php

namespace App\Controller\Admin\Trait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

trait DisableNewTrait
{
    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::NEW, Action::EDIT)->add(Crud::PAGE_INDEX, Action::DETAIL);
        return $actions;
    }

}
