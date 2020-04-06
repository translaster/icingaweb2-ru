<?php

namespace Icinga\Module\Dashboards\Form;

use Icinga\Module\Dashboards\Common\Database;
use Icinga\Web\Notification;
use ipl\Sql\Select;
use ipl\Web\Compat\CompatForm;

class DashletForm extends CompatForm
{
    use Database;

    public function fetchDashboards()
    {
        $dashboards = [];

        $select = (new Select())
            ->columns('*')
            ->from('dashboard');

        $data = $this->getDb()->select($select);

        foreach ($data as $dashboard) {
            $dashboards[$dashboard->id] = $dashboard->name;
        }

        return $dashboards;
    }

    public function createDashboard($name)
    {
        $data = [
            'name' => $name
        ];

        $db = $this->getDb();
        $db->insert('dashboard', $data);

        $id = $db->lastInsertId();

        return $id;
    }


    public function newAction()
    {
        $this->setAction('dashboards/dashlets/new');

        $this->addElement('textarea', 'url', [
            'label' => 'Url',
            'placeholder' => 'Enter Dashlet Url',
            'required' => true,
            'rows' => '3'
        ]);

        $this->addElement('text', 'name', [
            'label' => 'Dashlet Name',
            'placeholder' => 'Enter Dashlet Name',
            'required' => true
        ]);

        $this->addElement('checkbox', 'new-dashboard', [
            'label' => 'Dashboard',
            'class' => 'autosubmit',
            'value' => 'new-dashboard'
        ]);

        if ($this->getElement('new-dashboard')->getValue() === 'new-dashboard') {
            $this->addElement('text', 'new_dashboard', [
                'label' => 'New Dashboard',
                'placeholder' => 'New Dashboard Name '
            ]);
        } else {
            $this->addElement('select', 'dashboard', [
                'label' => 'Dashboard',
                'required' => true,
                'options' => $this->fetchDashboards()
            ]);
        }

        $this->addElement('submit', 'submit', [
            'label' => 'Add To Dashboard'
        ]);
    }

    protected function assemble()
    {
        $this->add($this->newAction());
    }

    public function onSuccess()
    {
        if ($this->getValue('new-dashboard') !== null) {
            if ($this->getValue('new_dashboard') !== null) {
                $values = [
                    'dashboard_id' => $this->createDashboard($this->getValue('new_dashboard')),
                    'name' => $this->getValue('name'),
                    'url' => $this->getValue('url')
                ];

                $this->getDb()->insert('dashlet', $values);
                Notification::success('Dashlet created');
            } else {
                Notification::error('Dashboard Name failed!');
            }

        } else {
            $data = [
                'dashboard_id' => $this->getValue('dashboard'),
                'name' => $this->getValue('name'),
                'url' => $this->getValue('url'),
            ];

            $this->getDb()->insert('dashlet', $data);
            Notification::success('Dashlet created');
        }
    }
}
