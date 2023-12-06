<?php

/**
 * @project       Alarmzone/Alarmzone
 * @file          AZ_ConfigurationForm.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */

declare(strict_types=1);

trait AZ_ConfigurationForm
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
     * @return void
     */
    public function ExpandExpansionPanels(bool $State): void
    {
        for ($i = 1; $i <= 9; $i++) {
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
     * Modifies a trigger list configuration button.
     *
     * @param string $Field
     * @param string $Condition
     * @return void
     */
    public function ModifyTriggerListButton(string $Field, string $Condition): void
    {
        $id = 0;
        $state = false;
        //Get variable id
        $primaryCondition = json_decode($Condition, true);
        if (array_key_exists(0, $primaryCondition)) {
            if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $state = true;
                }
            }
        }
        $this->UpdateFormField($Field, 'caption', 'ID ' . $id . ' Bearbeiten');
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $id);
    }

    public function ModifyActualVariableStatesConfigurationButton(string $Field, int $VariableID): void
    {
        $state = false;
        if ($VariableID > 1 && @IPS_ObjectExists($VariableID)) { //0 = main category, 1 = none
            $state = true;
        }
        $this->UpdateFormField($Field, 'caption', 'ID ' . $VariableID . ' Bearbeiten');
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $VariableID);
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
            'type'     => 'ExpansionPanel',
            'caption'  => 'Info',
            'name'     => 'Panel1',
            'expanded' => false,
            'items'    => [
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
            'type'     => 'ExpansionPanel',
            'caption'  => 'Bezeichnungen',
            'name'     => 'Panel2',
            'expanded' => false,
            'items'    => [
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'SystemName',
                    'caption' => 'Systembezeichnung (z.B. Alarmzone, Alarmanlage, Einbruchmeldeanlage)',
                    'width'   => '600px'
                ],
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'Location',
                    'caption' => 'Standortbezeichnung (z.B. Musterstraße 1)',
                    'width'   => '600px'
                ],
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'AlarmZoneName',
                    'caption' => 'Alarmzonenbezeichnung (z.B. Haus, Wohnung, Erdgeschoss, Obergeschoss)',
                    'width'   => '600px'
                ]
            ]
        ];

        //Operating modes
        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Betriebsarten',
            'name'     => 'Panel3',
            'expanded' => false,
            'items'    => [
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
                    'type'    => 'Button',
                    'caption' => 'Ablaufplan Scharfschaltung',
                    'onClick' => 'echo "https://github.com/ubittner/Alarmzone/blob/main/docs/Ablaufplan_Scharfschaltung.png";'
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'Label',
                            'caption' => 'Vollschutz',
                            'bold'    => true,
                            'italic'  => true
                        ]
                    ]
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
                    'type'    => 'CheckBox',
                    'name'    => 'CheckFullProtectionModeActivation',
                    'caption' => 'Aktivierungsprüfung'
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'NumberSpinner',
                            'name'    => 'FullProtectionModeActivationDelay',
                            'caption' => 'Einschaltverzögerung',
                            'suffix'  => 'Sekunden',
                            'minimum' => 0,
                            'maximum' => 60
                        ]
                    ]
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
                    'type'    => 'CheckBox',
                    'name'    => 'CheckHullProtectionModeActivation',
                    'caption' => 'Aktivierungsprüfung'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'HullProtectionModeActivationDelay',
                    'caption' => 'Einschaltverzögerung',
                    'suffix'  => 'Sekunden',
                    'minimum' => 0,
                    'maximum' => 60
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
                    'type'    => 'CheckBox',
                    'name'    => 'CheckPartialProtectionModeActivation',
                    'caption' => 'Aktivierungsprüfung'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'PartialProtectionModeActivationDelay',
                    'caption' => 'Einschaltverzögerung',
                    'suffix'  => 'Sekunden',
                    'minimum' => 0,
                    'maximum' => 60
                ]
            ]
        ];

        //Door window sensors
        $doorWindowSensorValues = [];
        $doorWindowSensors = json_decode($this->ReadPropertyString('DoorWindowSensors'), true);
        $amountDoorWindowSensors = count($doorWindowSensors);
        foreach ($doorWindowSensors as $doorWindowSensor) {
            $sensorID = 0;
            $sensorLocation = '';
            $rowColor = '#C0FFC0'; //light green
            $conditions = true;
            if ($doorWindowSensor['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($doorWindowSensor['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $sensorID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($sensorID <= 1 || @!IPS_ObjectExists($sensorID)) {
                            $conditions = false;
                        }
                    }
                }
            }
            if ($doorWindowSensor['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($doorWindowSensor['SecondaryCondition'], true);
                if (array_key_exists(0, $secondaryConditions)) {
                    if (array_key_exists('rules', $secondaryConditions[0])) {
                        $rules = $secondaryConditions[0]['rules']['variable'];
                        foreach ($rules as $rule) {
                            if (array_key_exists('variableID', $rule)) {
                                $id = $rule['variableID'];
                                if ($id <= 1 || @!IPS_ObjectExists($id)) {
                                    $conditions = false;
                                }
                            }
                        }
                    }
                }
            }
            //Alerting action
            if ($doorWindowSensor['UseAlertingAction']) {
                if ($doorWindowSensor['AlertingAction'] != '') {
                    $action = json_decode($doorWindowSensor['AlertingAction'], true);
                    if (array_key_exists('parameters', $action)) {
                        if (array_key_exists('TARGET', $action['parameters'])) {
                            $id = $action['parameters']['TARGET'];
                            if (@!IPS_ObjectExists($id)) {
                                $conditions = false;
                            }
                        }
                    }
                }
            }
            if ($conditions && isset($sensorID)) {
                $sensorLocation = IPS_GetLocation($sensorID);
                $blacklist = json_decode($this->ReadAttributeString('Blacklist'), true);
                if (is_array($blacklist)) {
                    foreach ($blacklist as $element) {
                        $blackListedSensor = json_decode($element, true);
                        if ($blackListedSensor['sensorID'] == $sensorID) {
                            $rowColor = '#DFDFDF'; //grey
                        }
                    }
                }
                if (!$doorWindowSensor['Use']) {
                    $rowColor = '#DFDFDF'; //grey
                }
            }
            if (!$conditions) {
                $rowColor = '#FFC0C0'; //red
            }
            $doorWindowSensorValues[] = ['ID' => $sensorID, 'VariableLocation' => $sensorLocation, 'rowColor' => $rowColor];
        }

        $form['elements'][] =
            [
                'type'     => 'ExpansionPanel',
                'caption'  => 'Tür- und Fenstersensoren',
                'name'     => 'Panel4',
                'expanded' => false,
                'items'    => [
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Aktueller Status',
                        'popup'   => [
                            'caption' => 'Aktueller Status',
                            'items'   => [
                                [
                                    'type'     => 'List',
                                    'name'     => 'ActualDoorWindowStateList',
                                    'caption'  => 'Tür- und Fenstersensoren',
                                    'add'      => false,
                                    'visible'  => false,
                                    'rowCount' => 1,
                                    'sort'     => [
                                        'column'    => 'ActualStatus',
                                        'direction' => 'ascending'
                                    ],
                                    'columns'  => [
                                        [
                                            'name'    => 'ActualStatus',
                                            'caption' => 'Aktueller Status',
                                            'width'   => '200px',
                                            'save'    => false
                                        ],
                                        [
                                            'name'    => 'SensorID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
                                            'onClick' => self::MODULE_PREFIX . '_ModifyActualVariableStatesConfigurationButton($id, "ActualDoorWindowStateConfigurationButton", $ActualDoorWindowStateList["SensorID"]);',
                                            'save'    => false
                                        ],
                                        [
                                            'name'    => 'Designation',
                                            'caption' => 'Name',
                                            'width'   => '400px',
                                            'save'    => false
                                        ],
                                        [
                                            'name'    => 'Comment',
                                            'caption' => 'Bemerkung',
                                            'width'   => '400px',
                                            'save'    => false
                                        ],
                                        [
                                            'name'    => 'LastUpdate',
                                            'caption' => 'Letzte Aktualisierung',
                                            'width'   => '200px',
                                            'save'    => false
                                        ]
                                    ]
                                ],
                                [
                                    'type'     => 'OpenObjectButton',
                                    'name'     => 'ActualDoorWindowStateConfigurationButton',
                                    'caption'  => 'Bearbeiten',
                                    'visible'  => false,
                                    'objectID' => 0
                                ]
                            ]
                        ],
                        'onClick' => self::MODULE_PREFIX . '_GetActualDoorWindowStates($id);'
                    ],
                    [
                        'type'     => 'List',
                        'name'     => 'DoorWindowSensors',
                        'caption'  => 'Tür- und Fenstersensoren',
                        'rowCount' => $amountDoorWindowSensors,
                        'add'      => true,
                        'delete'   => true,
                        'columns'  => [
                            [
                                'caption' => 'Aktiviert',
                                'name'    => 'Use',
                                'width'   => '100px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'name'    => 'ID',
                                'caption' => 'ID',
                                'width'   => '80px',
                                'add'     => '',
                                'save'    => false,
                                'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "DoorWindowSensorsConfigurationButton", $DoorWindowSensors["PrimaryCondition"]);',
                            ],
                            [
                                'caption' => 'Objektbaum',
                                'name'    => 'VariableLocation',
                                'width'   => '350px',
                                'add'     => '',
                                'save'    => false
                            ],
                            [
                                'caption' => 'Name',
                                'name'    => 'Designation',
                                'width'   => '400px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => 'Bemerkung',
                                'name'    => 'Comment',
                                'visible' => true,
                                'width'   => '300px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerPrimaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Bedingung:',
                                'name'    => 'LabelPrimaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Mehrfachauslösung',
                                'name'    => 'UseMultipleAlerts',
                                'width'   => '180px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'PrimaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'SelectCondition'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerSecondaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Weitere Bedingung(en):',
                                'name'    => 'LabelSecondaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SecondaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'edit'    => [
                                    'type'  => 'SelectCondition',
                                    'multi' => true
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerMode',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Modus:',
                                'name'    => 'LabelMode',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Vollschutz',
                                'name'    => 'FullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => true,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Hüllschutz',
                                'name'    => 'HullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Teilschutz',
                                'name'    => 'PartialProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerCheckActivation',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Aktivierungsprüfung:',
                                'name'    => 'LabelCheckActivation',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Vollschutz',
                                'name'    => 'CheckFullProtectionActivation',
                                'width'   => '300px',
                                'add'     => false,
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Hüllschutz',
                                'name'    => 'CheckHullProtectionActivation',
                                'width'   => '300px',
                                'add'     => false,
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Teilschutz',
                                'name'    => 'CheckPartialProtectionActivation',
                                'width'   => '300px',
                                'add'     => false,
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerAlarmProtocol',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Alarmprotokoll:',
                                'name'    => 'LabelAlarmProtocol',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Alarmprotokoll',
                                'name'    => 'UseAlarmProtocol',
                                'width'   => '200px',
                                'add'     => true,
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerAlarmActivation',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Alarmauslösung:',
                                'name'    => 'LabelAlarmActivation',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Erneute Überprüfung nach',
                                'name'    => 'OpenDoorWindowStatusVerificationDelay',
                                'width'   => '220px',
                                'add'     => 0,
                                'visible' => true,
                                'edit'    => [
                                    'type'    => 'NumberSpinner',
                                    'suffix'  => ' Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ]
                            ],
                            [
                                'caption' => 'Benachrichtigung',
                                'name'    => 'UseNotification',
                                'width'   => '200px',
                                'add'     => true,
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmsirene',
                                'name'    => 'UseAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmbeleuchtung',
                                'name'    => 'UseAlarmLight',
                                'width'   => '170px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmanruf',
                                'name'    => 'UseAlarmCall',
                                'width'   => '120px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktion',
                                'name'    => 'UseAlertingAction',
                                'width'   => '80px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'name'    => 'AlertingAction',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'SelectAction'
                                ]
                            ]
                        ],
                        'values' => $doorWindowSensorValues,
                    ],
                    [
                        'type'     => 'OpenObjectButton',
                        'name'     => 'DoorWindowSensorsConfigurationButton',
                        'caption'  => 'Bearbeiten',
                        'visible'  => false,
                        'objectID' => 0
                    ]
                ]
            ];

        //Motion detectors
        $motionDetectorsValues = [];
        $variables = json_decode($this->ReadPropertyString('MotionDetectors'), true);
        $amount = count($variables);
        foreach ($variables as $variable) {
            $sensorID = 0;
            $variableLocation = '';
            $rowColor = '#C0FFC0'; //light green
            if (!$variable['Use']) {
                $rowColor = '#DFDFDF'; //grey
            }
            //Primary condition
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $sensorID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($sensorID <= 1 || !@IPS_ObjectExists($sensorID)) {
                            $rowColor = '#FFC0C0'; //red
                        } else {
                            $variableLocation = IPS_GetLocation($sensorID);
                        }
                    }
                }
            }
            //Secondary condition, multi
            if ($variable['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($variable['SecondaryCondition'], true);
                if (array_key_exists(0, $secondaryConditions)) {
                    if (array_key_exists('rules', $secondaryConditions[0])) {
                        $rules = $secondaryConditions[0]['rules']['variable'];
                        foreach ($rules as $rule) {
                            if (array_key_exists('variableID', $rule)) {
                                $id = $rule['variableID'];
                                if ($id <= 1 || !@IPS_ObjectExists($id)) {
                                    $rowColor = '#FFC0C0'; //red
                                }
                            }
                        }
                    }
                }
            }
            //Alerting action
            if ($variable['UseAlertingAction']) {
                if ($variable['AlertingAction'] != '') {
                    $action = json_decode($variable['AlertingAction'], true);
                    if (array_key_exists('parameters', $action)) {
                        if (array_key_exists('TARGET', $action['parameters'])) {
                            $id = $action['parameters']['TARGET'];
                            if (!@IPS_ObjectExists($id)) {
                                $rowColor = '#FFC0C0'; //red
                            }
                        }
                    }
                }
            }
            $motionDetectorsValues[] = ['ID' => $sensorID, 'VariableLocation' => $variableLocation, 'rowColor' => $rowColor];
        }

        $form['elements'][] =
            [
                'type'     => 'ExpansionPanel',
                'caption'  => 'Bewegungsmelder',
                'name'     => 'Panel5',
                'expanded' => false,
                'items'    => [
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Aktueller Status',
                        'popup'   => [
                            'caption' => 'Aktueller Status',
                            'items'   => [
                                [
                                    'type'     => 'List',
                                    'name'     => 'ActualMotionDetectorStateList',
                                    'caption'  => 'Bewegungsmelder',
                                    'add'      => false,
                                    'visible'  => false,
                                    'rowCount' => 1,
                                    'sort'     => [
                                        'column'    => 'ActualStatus',
                                        'direction' => 'ascending'
                                    ],
                                    'columns'  => [
                                        [
                                            'name'    => 'ActualStatus',
                                            'caption' => 'Aktueller Status',
                                            'width'   => '200px',
                                            'save'    => false
                                        ],
                                        [
                                            'name'    => 'SensorID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
                                            'onClick' => self::MODULE_PREFIX . '_ModifyActualVariableStatesConfigurationButton($id, "ActualMotionDetectorStateConfigurationButton", $ActualMotionDetectorStateList["SensorID"]);',
                                            'save'    => false
                                        ],
                                        [
                                            'name'    => 'Designation',
                                            'caption' => 'Name',
                                            'width'   => '400px',
                                            'save'    => false
                                        ],
                                        [
                                            'name'    => 'Comment',
                                            'caption' => 'Bemerkung',
                                            'width'   => '400px',
                                            'save'    => false
                                        ],
                                        [
                                            'name'    => 'LastUpdate',
                                            'caption' => 'Letzte Aktualisierung',
                                            'width'   => '200px',
                                            'save'    => false
                                        ]
                                    ]
                                ],
                                [
                                    'type'     => 'OpenObjectButton',
                                    'name'     => 'ActualMotionDetectorStateConfigurationButton',
                                    'caption'  => 'Bearbeiten',
                                    'visible'  => false,
                                    'objectID' => 0
                                ]
                            ]
                        ],
                        'onClick' => self::MODULE_PREFIX . '_GetActualMotionDetectorStates($id);'
                    ],
                    [
                        'type'     => 'List',
                        'name'     => 'MotionDetectors',
                        'caption'  => 'Bewegungsmelder',
                        'rowCount' => $amount,
                        'add'      => true,
                        'delete'   => true,
                        'columns'  => [
                            [
                                'caption' => 'Aktiviert',
                                'name'    => 'Use',
                                'width'   => '100px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'name'    => 'ID',
                                'caption' => 'ID',
                                'width'   => '80px',
                                'add'     => '',
                                'save'    => false,
                                'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "MotionDetectorsConfigurationButton", $MotionDetectors["PrimaryCondition"]);',
                            ],
                            [
                                'caption' => 'Objektbaum',
                                'name'    => 'VariableLocation',
                                'save'    => false,
                                'width'   => '350px',
                                'add'     => ''
                            ],
                            [
                                'caption' => 'Name',
                                'name'    => 'Designation',
                                'width'   => '400px',
                                'add'     => '',
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => 'Bemerkung',
                                'name'    => 'Comment',
                                'visible' => true,
                                'width'   => '300px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerPrimaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Bedingung:',
                                'name'    => 'LabelPrimaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Mehrfachauslösung',
                                'name'    => 'UseMultipleAlerts',
                                'width'   => '180px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'PrimaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'SelectCondition'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerSecondaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Weitere Bedingung(en):',
                                'name'    => 'LabelSecondaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SecondaryCondition',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'edit'    => [
                                    'type'  => 'SelectCondition',
                                    'multi' => true
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerMode',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Modus:',
                                'name'    => 'LabelMode',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Vollschutz',
                                'name'    => 'FullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => true,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Hüllschutz',
                                'name'    => 'HullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Teilschutz',
                                'name'    => 'PartialProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerAlarmProtocol',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Alarmprotokoll:',
                                'name'    => 'LabelAlarmProtocol',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Alarmprotokoll',
                                'name'    => 'UseAlarmProtocol',
                                'width'   => '200px',
                                'add'     => true,
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => ' ',
                                'name'    => 'SpacerAlarmActivation',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label'
                                ]
                            ],
                            [
                                'caption' => 'Alarmauslösung:',
                                'name'    => 'LabelAlarmActivation',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'save'    => false,
                                'edit'    => [
                                    'type' => 'Label',
                                    'bold' => true
                                ]
                            ],
                            [
                                'caption' => 'Benachrichtigung',
                                'name'    => 'UseNotification',
                                'width'   => '200px',
                                'add'     => true,
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmsirene',
                                'name'    => 'UseAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmbeleuchtung',
                                'name'    => 'UseAlarmLight',
                                'width'   => '170px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmanruf',
                                'name'    => 'UseAlarmCall',
                                'width'   => '120px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktion',
                                'name'    => 'UseAlertingAction',
                                'width'   => '80px',
                                'add'     => false,
                                'visible' => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'name'    => 'AlertingAction',
                                'width'   => '200px',
                                'add'     => '',
                                'visible' => false,
                                'edit'    => [
                                    'type' => 'SelectAction'
                                ]
                            ]
                        ],
                        'values' => $motionDetectorsValues,
                    ],
                    [
                        'type'     => 'OpenObjectButton',
                        'name'     => 'MotionDetectorsConfigurationButton',
                        'caption'  => 'Bearbeiten',
                        'visible'  => false,
                        'objectID' => 0
                    ]
                ]
            ];

        //Alarm protocol
        $id = $this->ReadPropertyInteger('AlarmProtocol');
        $enableButton = false;
        if ($id > 1 && @IPS_ObjectExists($id)) {
            $enableButton = true;
        }
        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Alarmprotokoll',
            'name'     => 'Panel6',
            'expanded' => false,
            'items'    => [
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'SelectModule',
                            'name'     => 'AlarmProtocol',
                            'caption'  => 'Instanz',
                            'moduleID' => self::ALARMPROTOCOL_MODULE_GUID,
                            'width'    => '600px',
                            'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmProtocolConfigurationButton", "ID " . $AlarmProtocol . " konfigurieren", $AlarmProtocol);'
                        ],
                        [
                            'type'     => 'OpenObjectButton',
                            'caption'  => 'ID ' . $id . ' konfigurieren',
                            'name'     => 'AlarmProtocolConfigurationButton',
                            'visible'  => $enableButton,
                            'objectID' => $id
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateAlarmProtocolInstance($id);'
                        ]
                    ]
                ]
            ]
        ];

        //Notification
        $id = $this->ReadPropertyInteger('Notification');
        $enableButton = false;
        if ($id > 1 && @IPS_ObjectExists($id)) {
            $enableButton = true;
        }
        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Benachrichtigung',
            'name'     => 'Panel7',
            'expanded' => false,
            'items'    => [
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'SelectModule',
                            'name'     => 'Notification',
                            'caption'  => 'Instanz',
                            'moduleID' => self::NOTIFICATION_MODULE_GUID,
                            'width'    => '600px',
                            'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "NotificationConfigurationButton", "ID " . $Notification . " konfigurieren", $Notification);'
                        ],
                        [
                            'type'     => 'OpenObjectButton',
                            'caption'  => 'ID ' . $id . ' konfigurieren',
                            'name'     => 'NotificationConfigurationButton',
                            'visible'  => $enableButton,
                            'objectID' => $id
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateNotificationInstance($id);'
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                //Disarmed
                [
                    'type'    => 'Label',
                    'caption' => 'Unscharf',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'DeactivationNotification',
                    'caption'  => 'Aus (unscharf)',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                //Full protection
                [
                    'type'    => 'Label',
                    'caption' => 'Vollschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'FullProtectionAbortActivationNotification',
                    'caption'  => 'Aktivierungsprüfung',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'FullProtectionDelayedActivationNotification',
                    'caption'  => 'Einschaltverzögerung',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'FullProtectionActivationWithOpenDoorWindowNotification',
                    'caption'  => 'An + Tür/Fenster geöffnet (teilscharf)',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'FullProtectionActivationNotification',
                    'caption'  => 'An (scharf)',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                //Hull protection
                [
                    'type'    => 'Label',
                    'caption' => 'Hüllschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'HullProtectionAbortActivationNotification',
                    'caption'  => 'Aktivierungsprüfung',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'HullProtectionDelayedActivationNotification',
                    'caption'  => 'Einschaltverzögerung',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'HullProtectionActivationWithOpenDoorWindowNotification',
                    'caption'  => 'An + Tür/Fenster geöffnet (teilscharf)',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'HullProtectionActivationNotification',
                    'caption'  => 'An (scharf)',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                //Partial protection
                [
                    'type'    => 'Label',
                    'caption' => 'Teilschutz',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'PartialProtectionAbortActivationNotification',
                    'caption'  => 'Aktivierungsprüfung',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'PartialProtectionDelayedActivationNotification',
                    'caption'  => 'Einschaltverzögerung',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'PartialProtectionActivationWithOpenDoorWindowNotification',
                    'caption'  => 'An + Tür/Fenster geöffnet (teilscharf)',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'PartialProtectionActivationNotification',
                    'caption'  => 'An (scharf)',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Tür/Fenster geöffnet:',
                            'name'    => 'LabelOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung',
                            'name'    => 'UseOpenDoorWindowNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Tür/Fenster',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'OpenDoorWindowNotification',
                    'caption'  => 'Tür/Fenster geöffnet',
                    'rowCount' => 1,
                    'add'      => false,
                    'delete'   => false,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                //Alarm
                [
                    'type'    => 'Label',
                    'caption' => 'Alarm',
                    'bold'    => true,
                    'italic'  => true
                ],
                //Door and window sensors
                [
                    'type'     => 'List',
                    'name'     => 'DoorWindowAlarmNotification',
                    'caption'  => 'Tür- und Fenstersensoren',
                    'rowCount' => 1,

                    'delete'  => false,
                    'columns' => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ]
                ],
                //Motion detectors
                [
                    'type'     => 'List',
                    'name'     => 'MotionDetectorAlarmNotification',
                    'caption'  => 'Bewegungsmelder',
                    'rowCount' => 1,

                    'delete'  => false,
                    'columns' => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Meldungstext:',
                            'name'    => 'LabelMessageText',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '400px',
                            'visible' => true,
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '100px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht:',
                            'name'    => 'LabelWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht:',
                            'name'    => 'LabelWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'WebFront Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail:',
                            'name'    => 'LabelMail',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'SMS:',
                            'name'    => 'LabelSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Telegram:',
                            'name'    => 'LabelTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label',
                                'bold' => true
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        //Actions
        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Aktionen',
            'name'     => 'Panel8',
            'expanded' => false,
            'items'    => [
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
                ]
            ]
        ];

        //Visualisation
        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Visualisierung',
            'name'     => 'Panel9',
            'expanded' => false,
            'items'    => [
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
                    'caption' => 'Bezeichnungen',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableLocation',
                    'caption' => 'Standortbezeichnung'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmZoneName',
                    'caption' => 'Alarmzonenbezeichnung'
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
                    'name'    => 'EnableAlarmZoneState',
                    'caption' => 'Alarmzonenstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmZoneDetailedState',
                    'caption' => 'Detaillierter Alarmzonenstatus'
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
                'type'    => 'Label',
                'caption' => 'Tür- und Fenstersensoren'
            ];

        $form['actions'][] =
            [
                'type'  => 'RowLayout',
                'items' => [
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Tür- und Fenstersensoren ermitteln',
                        'popup'   => [
                            'caption' => 'Variablen wirklich automatisch ermitteln und hinzufügen?',
                            'items'   => [
                                [
                                    'type'    => 'Select',
                                    'name'    => 'DoorWindowDeterminationType',
                                    'caption' => 'Auswahl',
                                    'options' => [
                                        [
                                            'caption' => 'Profil auswählen',
                                            'value'   => 0
                                        ],
                                        [
                                            'caption' => 'Profil: ~Window',
                                            'value'   => 1
                                        ],
                                        [
                                            'caption' => 'Profil: ~Window.Reversed',
                                            'value'   => 2
                                        ],
                                        [
                                            'caption' => 'Profil: ~Window.HM',
                                            'value'   => 3
                                        ],
                                        [
                                            'caption' => 'Ident: STATE',
                                            'value'   => 4
                                        ],
                                        [
                                            'caption' => 'Ident: Benutzerdefiniert',
                                            'value'   => 5
                                        ]
                                    ],
                                    'value'    => 0,
                                    'onChange' => self::MODULE_PREFIX . '_CheckDoorWindowDeterminationValue($id, $DoorWindowDeterminationType);'
                                ],
                                [
                                    'type'    => 'SelectProfile',
                                    'name'    => 'DoorWindowSensorDeterminationProfileSelection',
                                    'caption' => 'Profil',
                                    'visible' => true
                                ],
                                [
                                    'type'    => 'ValidationTextBox',
                                    'name'    => 'DoorWindowDeterminationValue',
                                    'caption' => 'Identifikator',
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Ermitteln',
                                    'onClick' => self::MODULE_PREFIX . '_DetermineDoorWindowVariables($id, $DoorWindowDeterminationType, $DoorWindowDeterminationValue, $DoorWindowSensorDeterminationProfileSelection);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'DoorWindowSensorDeterminationProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'DoorWindowSensorDeterminationProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ],
                                [
                                    'type'     => 'List',
                                    'name'     => 'DeterminedDoorWindowVariableList',
                                    'caption'  => 'Variablen',
                                    'visible'  => false,
                                    'rowCount' => 15,
                                    'delete'   => true,
                                    'sort'     => [
                                        'column'    => 'ID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns'  => [
                                        [
                                            'caption' => 'Übernehmen',
                                            'name'    => 'Use',
                                            'width'   => '100px',
                                            'add'     => true,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'name'    => 'ID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
                                            'add'     => ''
                                        ],
                                        [
                                            'caption' => 'Objektbaum',
                                            'name'    => 'Location',
                                            'width'   => '800px',
                                            'add'     => ''
                                        ],
                                    ]
                                ],
                                [
                                    'type'    => 'CheckBox',
                                    'name'    => 'OverwriteDoorWindowVariableProfiles',
                                    'caption' => 'Variablenprofile überschreiben',
                                    'visible' => false,
                                    'value'   => true
                                ],
                                [
                                    'type'    => 'Button',
                                    'name'    => 'ApplyPreDoorWindowTriggerValues',
                                    'caption' => 'Übernehmen',
                                    'visible' => false,
                                    'onClick' => self::MODULE_PREFIX . '_ApplyDeterminedDoorWindowVariables($id, $DeterminedDoorWindowVariableList, $OverwriteDoorWindowVariableProfiles);'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'NumberSpinner',
                        'name'    => 'VerificationDelay',
                        'caption' => 'Erneute Überprüfung nach',
                        'suffix'  => 'Millisekunden',
                        'minimum' => 0,
                        'maximum' => 10000
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Erneute Überprüfung festlegen',
                        'popup'   => [
                            'caption' => 'Erneute Überprüfung wirklich automatisch festlegen?',
                            'items'   => [
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Festlegen',
                                    'onClick' => self::MODULE_PREFIX . '_ConfigureVerificationDelay($id, $VerificationDelay);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'VerificationDelayProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'VerificationDelayProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ],
                            ]
                        ]
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Variablenprofil zuweisen',
                        'popup'   => [
                            'caption' => 'Variablenprofil wirklich automatisch zuweisen?',
                            'items'   => [
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Zuweisen',
                                    'onClick' => self::MODULE_PREFIX . '_AssignDoorWindowVariableProfile($id);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'AssignDoorWindowVariableProfileProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'AssignDoorWindowVariableProfileProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => ' '
            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => 'Bewegungsmelder'
            ];

        $form['actions'][] =
            [
                'type'  => 'RowLayout',
                'items' => [
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Bewegungsmelder ermitteln',
                        'popup'   => [
                            'caption' => 'Variablen wirklich automatisch ermitteln und hinzufügen?',
                            'items'   => [
                                [
                                    'type'    => 'Select',
                                    'name'    => 'MotionDetectorDeterminationType',
                                    'caption' => 'Auswahl',
                                    'options' => [
                                        [
                                            'caption' => 'Profil auswählen',
                                            'value'   => 0
                                        ],
                                        [
                                            'caption' => 'Profil: ~Motion',
                                            'value'   => 1
                                        ],
                                        [
                                            'caption' => 'Profil: ~Motion.Reversed',
                                            'value'   => 2
                                        ],
                                        [
                                            'caption' => 'Profil: ~Motion.HM',
                                            'value'   => 3
                                        ],
                                        [
                                            'caption' => 'Ident: MOTION',
                                            'value'   => 4
                                        ],
                                        [
                                            'caption' => 'Ident: Benutzerdefiniert',
                                            'value'   => 5
                                        ],
                                    ],
                                    'value'    => 0,
                                    'onChange' => self::MODULE_PREFIX . '_CheckMotionDetectorDeterminationValue($id, $MotionDetectorDeterminationType);'
                                ],
                                [
                                    'type'    => 'SelectProfile',
                                    'name'    => 'MotionDetectorDeterminationProfileSelection',
                                    'caption' => 'Profil',
                                    'visible' => true
                                ],
                                [
                                    'type'    => 'ValidationTextBox',
                                    'name'    => 'MotionDetectorDeterminationValue',
                                    'caption' => 'Identifikator',
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Ermitteln',
                                    'onClick' => self::MODULE_PREFIX . '_DetermineMotionDetectorVariables($id, $MotionDetectorDeterminationType, $MotionDetectorDeterminationValue, $MotionDetectorDeterminationProfileSelection);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'MotionDetectorDeterminationProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'MotionDetectorDeterminationProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ],
                                [
                                    'type'     => 'List',
                                    'name'     => 'DeterminedMotionDetectorVariableList',
                                    'caption'  => 'Variablen',
                                    'visible'  => false,
                                    'rowCount' => 15,
                                    'delete'   => true,
                                    'sort'     => [
                                        'column'    => 'ID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns'  => [
                                        [
                                            'caption' => 'Übernehmen',
                                            'name'    => 'Use',
                                            'width'   => '100px',
                                            'add'     => true,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'name'    => 'ID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
                                            'add'     => ''
                                        ],
                                        [
                                            'caption' => 'Objektbaum',
                                            'name'    => 'Location',
                                            'width'   => '800px',
                                            'add'     => ''
                                        ],
                                    ]
                                ],
                                [
                                    'type'    => 'CheckBox',
                                    'name'    => 'OverwriteMotionDetectorVariableProfiles',
                                    'caption' => 'Variablenprofile überschreiben',
                                    'visible' => false,
                                    'value'   => true
                                ],
                                [
                                    'type'    => 'Button',
                                    'name'    => 'ApplyPreMotionDetectorTriggerValues',
                                    'caption' => 'Übernehmen',
                                    'visible' => false,
                                    'onClick' => self::MODULE_PREFIX . '_ApplyDeterminedMotionDetectorVariables($id, $DeterminedMotionDetectorVariableList, $OverwriteMotionDetectorVariableProfiles);'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Variablenprofil zuweisen',
                        'popup'   => [
                            'caption' => 'Variablenprofil wirklich automatisch zuweisen?',
                            'items'   => [
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Zuweisen',
                                    'onClick' => self::MODULE_PREFIX . '_AssignMotionDetectorVariableProfile($id);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'AssignMotionDetectorVariableProfileProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'AssignMotionDetectorVariableProfileProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ]
                            ]
                        ]
                    ],
                ]
            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => ' '
            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => 'Schaltelemente'
            ];

        $form['actions'][] =
            [
                'type' => 'TestCenter',
            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => ' '
            ];

        //Blacklist
        $blacklistedVariables = [];
        $blacklist = json_decode($this->ReadAttributeString('Blacklist'), true);
        $amountBlacklist = count($blacklist);
        if ($amountBlacklist == 0) {
            $amountBlacklist = 1;
        }
        if (is_array($blacklist)) {
            foreach ($blacklist as $element) {
                $variable = json_decode($element, true);
                $blacklistedVariables[] = [
                    'SensorID'    => $variable['sensorID'],
                    'Designation' => $variable['sensorDesignation']];
            }
        }

        //Registered references
        $registeredReferences = [];
        $references = $this->GetReferenceList();
        $amountReferences = count($references);
        if ($amountReferences == 0) {
            $amountReferences = 1;
        }
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
        $amountMessages = count($messages);
        if ($amountMessages == 0) {
            $amountMessages = 1;
        }
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
                    'type'    => 'Label',
                    'caption' => 'Sperrliste',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'Blacklist',
                    'rowCount' => $amountBlacklist,
                    'delete'   => true,
                    'onDelete' => self::MODULE_PREFIX . '_DeleteElementFromBlacklist($id, $Blacklist["SensorID"]);',
                    'sort'     => [
                        'column'    => 'SensorID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'SensorID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "BlacklistConfigurationButton", "ID " . $Blacklist["SensorID"] . " bearbeiten", $Blacklist["SensorID"]);'
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '300px'
                        ]
                    ],
                    'values' => $blacklistedVariables
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'PopupButton',
                            'caption' => 'Zurücksetzen',
                            'popup'   => [
                                'caption' => 'Sperrliste wirklich zurücksetzen?',
                                'items'   => [
                                    [
                                        'type'    => 'Button',
                                        'caption' => 'Zurücksetzen',
                                        'onClick' => self::MODULE_PREFIX . '_ResetBlackList($id);'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'BlacklistConfigurationButton',
                            'caption'  => 'Bearbeiten',
                            'visible'  => false,
                            'objectID' => 0
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Registrierte Referenzen',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredReferences',
                    'rowCount' => $amountReferences,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " aufrufen", $RegisteredReferences["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " aufrufen", $RegisteredReferences["ObjectID"]);'
                        ]
                    ],
                    'values' => $registeredReferences
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredReferencesConfigurationButton',
                    'caption'  => 'Aufrufen',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Registrierte Nachrichten',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredMessages',
                    'rowCount' => $amountMessages,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " aufrufen", $RegisteredMessages["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " aufrufen", $RegisteredMessages["ObjectID"]);'
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
                    'caption'  => 'Aufrufen',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        //Dummy info message
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