<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung
 * @file          AZST_Config.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZST_Config
{
    /**
     * Reloads the configuration form.
     *
     * @return void
     */
    public function ReloadConfig(): void
    {
        $this->ReloadForm();
    }

    /**
     * Expands or collapses the expansion panels.
     *
     * @param bool $State
     * false =  collapse,
     * true =   expand
     *
     * @return void
     */
    public function ExpandExpansionPanels(bool $State): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->UpdateFormField('Panel' . $i, 'expanded', $State);
        }
    }

    /**
     * Modifies a configuration button.
     *
     * @param string $Field
     * @param string $Caption
     * @param int $ObjectID
     * @return void
     */
    public function ModifyButton(string $Field, string $Caption, int $ObjectID): void
    {
        $state = false;
        if ($ObjectID > 1 && @IPS_ObjectExists($ObjectID)) {
            $state = true;
        }
        $this->UpdateFormField($Field, 'caption', $Caption);
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $ObjectID);
    }

    /**
     * Gets the configuration form.
     *
     * @return false|string
     * @throws Exception
     */
    public function GetConfigurationForm()
    {
        $form = [];

        ########## Elements

        //Configuration buttons
        $form['elements'][0] =
            [
                'type'  => 'RowLayout',
                'items' => [
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration ausklappen',
                        'onClick' => self::MODULE_PREFIX . '_ExpandExpansionPanels($id, true);'
                    ],
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration einklappen',
                        'onClick' => self::MODULE_PREFIX . '_ExpandExpansionPanels($id, false);'
                    ],
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration neu laden',
                        'onClick' => self::MODULE_PREFIX . '_ReloadConfig($id);'
                    ]
                ]
            ];

        //Info
        $library = IPS_GetLibrary(self::LIBRARY_GUID);
        $module = IPS_GetModule(self::MODULE_GUID);
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel1',
            'caption' => 'Info',
            'items'   => [
                [
                    'type'    => 'Label',
                    'caption' => "ID:\t\t\t" . $this->InstanceID
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Modul:\t\t" . $module['ModuleName']
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Präfix:\t\t" . $module['Prefix']
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Version:\t\t" . $library['Version'] . '-' . $library['Build'] . ', ' . date('d.m.Y', $library['Date'])
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Entwickler:\t" . $library['Author']
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'Note',
                    'caption' => 'Notiz',
                    'width'   => '600px'
                ]
            ]
        ];

        //Designations
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel2',
            'caption' => 'Bezeichnung',
            'items'   => [
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'Location',
                    'caption' => 'Standortbezeichnung (z.B. Musterstraße 1)',
                    'width'   => '600px'
                ]
            ]
        ];

        //Operating modes
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel3',
            'caption' => 'Betriebsarten',
            'items'   => [
                [
                    'type'    => 'Label',
                    'caption' => 'Unscharf',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'SelectIcon',
                            'name'    => 'DisarmedIcon',
                            'caption' => 'Icon'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'ValidationTextBox',
                            'name'    => 'DisarmedName',
                            'caption' => 'Bezeichnung'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'SelectColor',
                            'name'    => 'DisarmedColor',
                            'caption' => 'Farbe'
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Vollschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'SelectIcon',
                            'name'    => 'FullProtectionIcon',
                            'caption' => 'Icon'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'ValidationTextBox',
                            'name'    => 'FullProtectionName',
                            'caption' => 'Bezeichnung'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'SelectColor',
                            'name'    => 'FullProtectionColor',
                            'caption' => 'Farbe'
                        ]
                    ]
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseFullProtectionMode',
                    'caption' => 'Aktiv'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Hüllschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'SelectIcon',
                            'name'    => 'HullProtectionIcon',
                            'caption' => 'Icon'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'ValidationTextBox',
                            'name'    => 'HullProtectionName',
                            'caption' => 'Hüllschutz'

                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'SelectColor',
                            'name'    => 'HullProtectionColor',
                            'caption' => 'Farbe'
                        ]
                    ]
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseHullProtectionMode',
                    'caption' => 'Aktiv'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Teilschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'SelectIcon',
                            'name'    => 'PartialProtectionIcon',
                            'caption' => 'Icon'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'ValidationTextBox',
                            'name'    => 'PartialProtectionName',
                            'caption' => 'Teilschutz'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'SelectColor',
                            'name'    => 'PartialProtectionColor',
                            'caption' => 'Farbe'
                        ]
                    ]
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UsePartialProtectionMode',
                    'caption' => 'Aktiv'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Individualschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'SelectIcon',
                            'name'    => 'IndividualProtectionIcon',
                            'caption' => 'Icon'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'ValidationTextBox',
                            'name'    => 'IndividualProtectionName',
                            'caption' => 'Individualschutz'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'SelectColor',
                            'name'    => 'IndividualProtectionColor',
                            'caption' => 'Farbe'
                        ]
                    ]
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseIndividualProtectionMode',
                    'caption' => 'Aktiv'
                ]
            ]
        ];

        //Alarm zones
        $alarmZoneValues = [];
        $alarmZones = json_decode($this->ReadPropertyString('AlarmZones'), true);
        foreach ($alarmZones as $alarmZone) {
            if (!$alarmZone['Use']) {
                continue;
            }
            $rowColor = '#FFC0C0'; //red
            $id = $alarmZone['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#C0FFC0'; //light green
            }
            $alarmZoneValues[] = ['rowColor' => $rowColor];
        }

        //Protection mode
        $protectionModeValues = [];
        $variables = json_decode($this->ReadPropertyString('ProtectionMode'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $protectionModeValues[] = ['rowColor' => $rowColor];
        }

        //System state
        $systemStateValues = [];
        $variables = json_decode($this->ReadPropertyString('SystemState'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $systemStateValues[] = ['rowColor' => $rowColor];
        }

        //System detailed state
        $systemDetailedStateValues = [];
        $variables = json_decode($this->ReadPropertyString('SystemDetailedState'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $systemDetailedStateValues[] = ['rowColor' => $rowColor];
        }

        //Alarm state
        $alarmStateValues = [];
        $variables = json_decode($this->ReadPropertyString('AlarmState'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alarmStateValues[] = ['rowColor' => $rowColor];
        }

        //Alerting sensors
        $alertingSensorValues = [];
        $variables = json_decode($this->ReadPropertyString('AlertingSensor'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alertingSensorValues[] = ['rowColor' => $rowColor];
        }

        //Door and window states
        $doorWindowStateValues = [];
        $variables = json_decode($this->ReadPropertyString('DoorWindowState'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $doorWindowStateValues[] = ['rowColor' => $rowColor];
        }

        //Motion detector states
        $motionDetectorStateValues = [];
        $variables = json_decode($this->ReadPropertyString('MotionDetectorState'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $motionDetectorStateValues[] = ['rowColor' => $rowColor];
        }

        //Alarm sirens
        $alarmSirenValues = [];
        $variables = json_decode($this->ReadPropertyString('AlarmSiren'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alarmSirenValues[] = ['rowColor' => $rowColor];
        }

        //Alarm lights
        $alarmLightValues = [];
        $variables = json_decode($this->ReadPropertyString('AlarmLight'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alarmLightValues[] = ['rowColor' => $rowColor];
        }

        //Alarm calls
        $alarmCallValues = [];
        $variables = json_decode($this->ReadPropertyString('AlarmCall'), true);
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alarmCallValues[] = ['rowColor' => $rowColor];
        }

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel4',
            'caption' => 'Alarmzonen',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'AlarmZones',
                    'caption'  => 'Alarmzonen',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Alarmzone',
                            'name'    => 'ID',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmZoneConfigurationButton", "ID " . $AlarmZones["ID"] . " konfigurieren", $AlarmZones["ID"]);',
                            'edit'    => [
                                'type'     => 'SelectModule',
                                'moduleID' => self::ALARMZONE_MODULE_GUID
                            ]
                        ],
                        [
                            'name'    => 'Designation',
                            'caption' => 'Bezeichnung',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmZoneConfigurationButton", "ID " . $AlarmZones["ID"] . " konfigurieren", $AlarmZones["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'name'    => 'IndividualProtectionMode',
                            'caption' => 'Individualschutz',
                            'width'   => '200px',
                            'add'     => 1,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Unscharf',
                                        'value'   => 0
                                    ],
                                    [
                                        'caption' => 'Vollschutz',
                                        'value'   => 1
                                    ],
                                    [
                                        'caption' => 'Hüllschutz',
                                        'value'   => 2
                                    ],
                                    [
                                        'caption' => 'Teilschutz',
                                        'value'   => 3
                                    ],
                                    [
                                        'caption' => 'Keine Funktion',
                                        'value'   => 4
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'values' => $alarmZoneValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'konfigurieren',
                    'name'     => 'AlarmZoneConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'ProtectionMode',
                    'caption'  => 'Modus',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "ProtectionModeConfigurationButton", "ID " . $ProtectionMode["ID"] . " bearbeiten", $ProtectionMode["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "ProtectionModeConfigurationButton", "ID " . $ProtectionMode["ID"] . " bearbeiten", $ProtectionMode["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $protectionModeValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'ProtectionModeConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'SystemState',
                    'caption'  => 'Systemstatus',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "SystemStateConfigurationButton", "ID " . $SystemState["ID"] . " bearbeiten", $SystemState["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "SystemStateConfigurationButton", "ID " . $SystemState["ID"] . " bearbeiten", $SystemState["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $systemStateValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'SystemStateConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'SystemDetailedState',
                    'caption'  => 'Detaillierter Systemstatus',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "SystemDetailedStateConfigurationButton", "ID " . $SystemDetailedState["ID"] . " bearbeiten", $SystemDetailedState["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "SystemDetailedStateConfigurationButton", "ID " . $SystemDetailedState["ID"] . " bearbeiten", $SystemDetailedState["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $systemDetailedStateValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'SystemDetailedStateConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'AlarmState',
                    'caption'  => 'Alarmstatus',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmStateConfigurationButton", "ID " . $AlarmState["ID"] . " bearbeiten", $AlarmState["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmStateConfigurationButton", "ID " . $AlarmState["ID"] . " bearbeiten", $AlarmState["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $alarmStateValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'AlarmStateConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'AlertingSensor',
                    'caption'  => 'Auslösender Alarmsensor',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlertingSensorConfigurationButton", "ID " . $AlertingSensor["ID"] . " bearbeiten", $AlertingSensor["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlertingSensorConfigurationButton", "ID " . $AlertingSensor["ID"] . " bearbeiten", $AlertingSensor["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $alertingSensorValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'AlertingSensorConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'DoorWindowState',
                    'caption'  => 'Tür- und Fensterstatus',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "DoorWindowStateConfigurationButton", "ID " . $DoorWindowState["ID"] . " bearbeiten", $DoorWindowState["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "DoorWindowStateConfigurationButton", "ID " . $DoorWindowState["ID"] . " bearbeiten", $DoorWindowState["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $doorWindowStateValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'DoorWindowStateConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'MotionDetectorState',
                    'caption'  => 'Bewegungsmelderstatus',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "MotionDetectorStateConfigurationButton", "ID " . $MotionDetectorState["ID"] . " bearbeiten", $MotionDetectorState["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "MotionDetectorStateConfigurationButton", "ID " . $MotionDetectorState["ID"] . " bearbeiten", $MotionDetectorState["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $motionDetectorStateValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'MotionDetectorStateConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'AlarmSiren',
                    'caption'  => 'Alarmsirene',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmSirenConfigurationButton", "ID " . $AlarmSiren["ID"] . " bearbeiten", $AlarmSiren["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmSirenConfigurationButton", "ID " . $AlarmSiren["ID"] . " bearbeiten", $AlarmSiren["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $alarmSirenValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'AlarmSirenConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'AlarmLight',
                    'caption'  => 'Alarmbeleuchtung',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmLightConfigurationButton", "ID " . $AlarmLight["ID"] . " bearbeiten", $AlarmLight["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmLightConfigurationButton", "ID " . $AlarmLight["ID"] . " bearbeiten", $AlarmLight["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $alarmLightValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'AlarmLightConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'AlarmCall',
                    'caption'  => 'Alarmanruf',
                    'rowCount' => 6,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '450px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmCallConfigurationButton", "ID " . $AlarmCall["ID"] . " bearbeiten", $AlarmCall["ID"]);',
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmCallConfigurationButton", "ID " . $AlarmCall["ID"] . " bearbeiten", $AlarmCall["ID"]);',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $alarmCallValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'AlarmCallConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        //Actions
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel5',
            'caption' => 'Aktionen',
            'items'   => [
                [
                    'type'    => 'Label',
                    'caption' => 'Unscharf',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseDisarmedAction',
                    'caption' => 'Aktiv'
                ],
                [
                    'type' => 'SelectAction',
                    'name' => 'DisarmedAction'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Vollschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseFullProtectionAction',
                    'caption' => 'Aktiv'
                ],
                [
                    'type' => 'SelectAction',
                    'name' => 'FullProtectionAction'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Hüllschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseHullProtectionAction',
                    'caption' => 'Aktiv'
                ],
                [
                    'type' => 'SelectAction',
                    'name' => 'HullProtectionAction'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Teilschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UsePartialProtectionAction',
                    'caption' => 'Aktiv'
                ],
                [
                    'type' => 'SelectAction',
                    'name' => 'PartialProtectionAction'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Individualschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseIndividualProtectionAction',
                    'caption' => 'Aktiv'
                ],
                [
                    'type' => 'SelectAction',
                    'name' => 'IndividualProtectionAction'
                ]
            ]
        ];

        //Visualisation
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel6',
            'caption' => 'Visualisierung',
            'items'   => [
                [
                    'type'    => 'Label',
                    'caption' => 'Aktiv',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableActive',
                    'caption' => 'Aktiv'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Bezeichnung',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableLocation',
                    'caption' => 'Standortbezeichnung'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Kontrollschalter',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableFullProtectionControlSwitch',
                    'caption' => 'Vollschutz'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableHullProtectionControlSwitch',
                    'caption' => 'Hüllschutz'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnablePartialProtectionControlSwitch',
                    'caption' => 'Teilschutz'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableIndividualProtectionControlSwitch',
                    'caption' => 'Individualschutz'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Modus',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableMode',
                    'caption' => 'Modus'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Status',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableSystemState',
                    'caption' => 'Systemstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableSystemDetailedState',
                    'caption' => 'Detaillierter Systemstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableDoorWindowState',
                    'caption' => 'Tür- und Fensterstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableMotionDetectorState',
                    'caption' => 'Bewegungsmelderstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmState',
                    'caption' => 'Alarmstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlertingSensor',
                    'caption' => 'Auslösender Alarmsensor'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmSirenState',
                    'caption' => 'Alarmsirene'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmLightState',
                    'caption' => 'Alarmbeleuchtung'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmCallState',
                    'caption' => 'Alarmanruf'
                ]
            ]
        ];

        ########## Actions

        $form['actions'][] =
            [
                'type'  => 'RowLayout',
                'items' => [
                    [

                        'type'    => 'PopupButton',
                        'caption' => 'Alarmzonen ermitteln',
                        'popup'   => [
                            'caption' => 'Alarmzonen wirklich automatisch ermitteln?',
                            'items'   => [
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Ermitteln',
                                    'onClick' => self::MODULE_PREFIX . '_DetermineAlarmZoneVariables($id);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'DetermineAlarmZoneVariablesProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'DetermineAlarmZoneVariablesProgressInfo',
                                    'caption' => 'Alarmzone',
                                    'visible' => false
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'Button',
                        'caption' => 'Neue Alarmzone erstellen',
                        'onClick' => self::MODULE_PREFIX . '_CreateAlarmZoneInstance($id);'
                    ]
                ]
            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => ' '
            ];

        //Test center
        $form['actions'][] =
            [
                'type' => 'TestCenter',

            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => ' '
            ];

        //Registered references
        $registeredReferences = [];
        $references = $this->GetReferenceList();
        foreach ($references as $reference) {
            $name = 'Objekt #' . $reference . ' existiert nicht';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($reference)) {
                $name = IPS_GetName($reference);
                $rowColor = '#C0FFC0'; //light green
            }
            $registeredReferences[] = [
                'ObjectID' => $reference,
                'Name'     => $name,
                'rowColor' => $rowColor];
        }

        //Registered messages
        $registeredMessages = [];
        $messages = $this->GetMessageList();
        foreach ($messages as $id => $messageID) {
            $name = 'Objekt #' . $id . ' existiert nicht';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($id)) {
                $name = IPS_GetName($id);
                $rowColor = '#C0FFC0'; //light green
            }
            switch ($messageID) {
                case [10001]:
                    $messageDescription = 'IPS_KERNELSTARTED';
                    break;

                case [10603]:
                    $messageDescription = 'VM_UPDATE';
                    break;

                default:
                    $messageDescription = 'keine Bezeichnung';
            }
            $registeredMessages[] = [
                'ObjectID'           => $id,
                'Name'               => $name,
                'MessageID'          => $messageID,
                'MessageDescription' => $messageDescription,
                'rowColor'           => $rowColor];
        }

        $form['actions'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Entwicklerbereich',
            'expanded' => false,
            'items'    => [
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredReferences',
                    'caption'  => 'Registrierte Referenzen',
                    'rowCount' => 10,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " bearbeiten", $RegisteredReferences["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " bearbeiten", $RegisteredReferences["ObjectID"]);'
                        ]
                    ],
                    'values' => $registeredReferences
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredReferencesConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredMessages',
                    'caption'  => 'Registrierte Nachrichten',
                    'rowCount' => 10,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " bearbeiten", $RegisteredMessages["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " bearbeiten", $RegisteredMessages["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Nachrichten ID',
                            'name'    => 'MessageID',
                            'width'   => '150px'
                        ],
                        [
                            'caption' => 'Nachrichten Bezeichnung',
                            'name'    => 'MessageDescription',
                            'width'   => '250px'
                        ]
                    ],
                    'values' => $registeredMessages
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredMessagesConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        $form['actions'][] =
            [
                'type'    => 'PopupAlert',
                'name'    => 'InfoMessage',
                'visible' => false,
                'popup'   => [
                    'closeCaption' => 'OK',
                    'items'        => [
                        [
                            'type'    => 'Label',
                            'name'    => 'InfoMessageLabel',
                            'caption' => '',
                            'visible' => true
                        ]
                    ]
                ]
            ];

        ########## Status

        $form['status'][] = [
            'code'    => 101,
            'icon'    => 'active',
            'caption' => $module['ModuleName'] . ' wird erstellt',
        ];
        $form['status'][] = [
            'code'    => 102,
            'icon'    => 'active',
            'caption' => $module['ModuleName'] . ' ist aktiv',
        ];
        $form['status'][] = [
            'code'    => 103,
            'icon'    => 'active',
            'caption' => $module['ModuleName'] . ' wird gelöscht',
        ];
        $form['status'][] = [
            'code'    => 104,
            'icon'    => 'inactive',
            'caption' => $module['ModuleName'] . ' ist inaktiv',
        ];
        $form['status'][] = [
            'code'    => 200,
            'icon'    => 'inactive',
            'caption' => 'Es ist Fehler aufgetreten, weitere Informationen unter Meldungen, im Log oder Debug!',
        ];

        return json_encode($form);
    }
}