<?php

namespace App\Entity;

interface BaseEntity
{
    public function indexAction();

    public function viewAction();

    public function createAction();

    public function updateAction();

    public function deleteAction();
}
