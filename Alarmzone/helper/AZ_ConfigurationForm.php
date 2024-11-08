<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_ConfigurationForm.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */

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
        for ($i = 1; $i <= 14; $i++) {
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
     * Hides or shows an action element.
     *
     * @param string $ActionName
     * @param bool $State
     * @return void
     */
    public function HideAction(string $ActionName, bool $State): void
    {
        $this->UpdateFormField($ActionName, 'visible', $State);
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
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Unscharf',
                    'expanded' => false,
                    'items'    => [
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
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Vollschutz',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'UseFullProtectionMode',
                            'caption' => 'Aktiv'
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
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Hüllschutz',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'UseHullProtectionMode',
                            'caption' => 'Aktiv'
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
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Teilschutz',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'UsePartialProtectionMode',
                            'caption' => 'Aktiv'
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
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Alarm',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'    => 'Label',
                            'caption' => 'Aus',
                            'bold'    => true,
                            'italic'  => true
                        ],
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'AlarmSwitchDisarmAlarmZones',
                            'caption' => 'Alarmzonen unscharf schalten'
                        ],
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'AlarmSwitchAlarmSirenOff',
                            'caption' => 'Alarmsirenen ausschalten'
                        ],
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'AlarmSwitchAlarmLightOff',
                            'caption' => 'Alarmbeleuchtung ausschalten'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Panikalarm',
                            'bold'    => true,
                            'italic'  => true
                        ],
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'PanicAlarmUseAlarmSiren',
                            'caption' => 'Außensirene einschalten'
                        ],
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'PanicAlarmUseInternalAlarmSiren',
                            'caption' => 'Innensirene einschalten'
                        ],
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'PanicAlarmUseAlarmLight',
                            'caption' => 'Alarmbeleuchtung einschalten'
                        ],
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'PanicAlarmUseAlarmCall',
                            'caption' => 'Alarmanruf auslösen'
                        ]
                    ]
                ],
                [
                    'type'    => 'Button',
                    'caption' => 'Ablaufplan Scharfschaltung',
                    'onClick' => 'echo "https://github.com/ubittner/Alarmzone/blob/main/docs/Ablaufplan_Scharfschaltung.png";'
                ]
            ]
        ];

        //Door window sensors
        $doorWindowSensorValues = [];
        $doorWindowVariableProfileListValues = [];
        $doorWindowSensors = json_decode($this->ReadPropertyString('DoorWindowSensors'), true);
        $amountDoorWindowSensors = count($doorWindowSensors);
        $amountDoorWindowSensorRows = count($doorWindowSensors) + 1;
        if ($amountDoorWindowSensorRows == 1) {
            $amountDoorWindowSensorRows = 3;
        }
        foreach ($doorWindowSensors as $doorWindowSensor) {
            $sensorID = 0;
            $rowColor = '#C0FFC0'; //light green
            $error = false;
            if ($doorWindowSensor['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($doorWindowSensor['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $sensorID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($sensorID <= 1 || @!IPS_ObjectExists($sensorID)) {
                            $error = true;
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
                                    $error = true;
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
                                $error = true;
                            }
                        }
                    }
                }
            }
            if (!$error && isset($sensorID)) {
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
            if ($error) {
                $rowColor = '#FFC0C0'; //red
            }
            $doorWindowSensorValues[] = ['rowColor' => $rowColor];
            $doorWindowVariableProfileListValues[] = ['SensorID' => $sensorID, 'Designation' => $doorWindowSensor['Designation'], 'Comment' => $doorWindowSensor['Comment']];
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
                        'caption' => 'Tür- und Fenstersensoren ermitteln',
                        'popup'   => [
                            'caption' => 'Tür- und Fenstersensoren wirklich automatisch ermitteln und hinzufügen?',
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
                                            'caption' => 'Ident: STATE',
                                            'value'   => 1
                                        ],
                                        [
                                            'caption' => 'Ident: Benutzerdefiniert',
                                            'value'   => 2
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
                                    'columns' => [
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
                                        ]
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
                                    'rowCount' => 1,
                                    'sort'     => [
                                        'column'    => 'ActualStatus',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
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
                        'rowCount' => $amountDoorWindowSensorRows,
                        'add'      => true,
                        'delete'   => true,
                        'sort'     => [
                            'column'    => 'Designation',
                            'direction' => 'ascending'
                        ],
                        'columns' => [
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
                                'caption' => 'Name',
                                'name'    => 'Designation',
                                'width'   => '400px',
                                'add'     => '',
                                'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "DoorWindowSensorsConfigurationButton", $DoorWindowSensors["PrimaryCondition"]);',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => 'Bemerkung',
                                'name'    => 'Comment',
                                'width'   => '300px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
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
                                'caption' => 'Primäre Bedingung',
                                'name'    => 'PrimaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectCondition'
                                ]
                            ],
                            [
                                'caption' => 'Weitere Bedingungen',
                                'name'    => 'SecondaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type'  => 'SelectCondition',
                                    'multi' => true
                                ]
                            ],
                            [
                                'caption' => 'Vollschutz',
                                'name'    => 'FullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktivierungsprüfung Vollschutz',
                                'name'    => 'CheckFullProtectionActivation',
                                'width'   => '260px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Hüllschutz',
                                'name'    => 'HullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktivierungsprüfung Hüllschutz',
                                'name'    => 'CheckHullProtectionActivation',
                                'width'   => '260px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Teilschutz',
                                'name'    => 'PartialProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktivierungsprüfung Teilschutz',
                                'name'    => 'CheckPartialProtectionActivation',
                                'width'   => '260px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Erneute Überprüfung nach',
                                'name'    => 'OpenDoorWindowStatusVerificationDelay',
                                'width'   => '220px',
                                'add'     => 0,
                                'edit'    => [
                                    'type'    => 'NumberSpinner',
                                    'suffix'  => ' Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ]
                            ],
                            [
                                'caption' => 'Ereignisprotokoll',
                                'name'    => 'UseEventProtocol',
                                'width'   => '140px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmprotokoll',
                                'name'    => 'UseAlarmProtocol',
                                'width'   => '140px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Benachrichtigung',
                                'name'    => 'UseNotification',
                                'width'   => '160px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Außensirene',
                                'name'    => 'UseAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Innensirene',
                                'name'    => 'UseInternalAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmbeleuchtung',
                                'name'    => 'UseAlarmLight',
                                'width'   => '170px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmanruf',
                                'name'    => 'UseAlarmCall',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Panikalarm',
                                'name'    => 'UsePanicAlarm',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktion',
                                'name'    => 'UseAlertingAction',
                                'width'   => '80px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktionsausführung',
                                'name'    => 'AlertingAction',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectAction'
                                ]
                            ]
                        ],
                        'values' => $doorWindowSensorValues,
                    ],
                    [
                        'type'    => 'Label',
                        'caption' => 'Anzahl Auslöser: ' . $amountDoorWindowSensors
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Erneute Überprüfung festlegen',
                        'popup'   => [
                            'caption' => 'Erneute Überprüfung wirklich festlegen?',
                            'items'   => [
                                [
                                    'type'    => 'NumberSpinner',
                                    'name'    => 'VerificationDelay',
                                    'caption' => 'Erneute Überprüfung nach',
                                    'suffix'  => 'Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ],
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
                                    'type'     => 'List',
                                    'name'     => 'DoorWindowVariableProfileList',
                                    'caption'  => 'Variablen',
                                    'add'      => false,
                                    'rowCount' => $amountDoorWindowSensors,
                                    'sort'     => [
                                        'column'    => 'SensorID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
                                        [
                                            'caption' => 'Auswahl',
                                            'name'    => 'Use',
                                            'width'   => '100px',
                                            'add'     => true,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'caption' => 'Profil umkehren',
                                            'name'    => 'UseReversedProfile',
                                            'width'   => '150px',
                                            'add'     => false,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'name'    => 'SensorID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
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
                                        ]
                                    ],
                                    'values' => $doorWindowVariableProfileListValues,
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Zuweisen',
                                    'onClick' => self::MODULE_PREFIX . '_AssignDoorWindowVariableProfile($id, $DoorWindowVariableProfileList);'
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
        $motionDetectorVariableProfileListValues = [];
        $motionDetectors = json_decode($this->ReadPropertyString('MotionDetectors'), true);
        $amountMotionDetectors = count($motionDetectors);
        $amountMotionDetectorRows = count($motionDetectors) + 1;
        if ($amountMotionDetectorRows == 1) {
            $amountMotionDetectorRows = 3;
        }
        foreach ($motionDetectors as $motionDetector) {
            $sensorID = 0;
            $rowColor = '#C0FFC0'; //light green
            if (!$motionDetector['Use']) {
                $rowColor = '#DFDFDF'; //grey
            }
            //Primary condition
            if ($motionDetector['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($motionDetector['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $sensorID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($sensorID <= 1 || !@IPS_ObjectExists($sensorID)) {
                            $rowColor = '#FFC0C0'; //red
                        }
                    }
                }
            }
            //Secondary condition, multi
            if ($motionDetector['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($motionDetector['SecondaryCondition'], true);
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
            if ($motionDetector['UseAlertingAction']) {
                if ($motionDetector['AlertingAction'] != '') {
                    $action = json_decode($motionDetector['AlertingAction'], true);
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
            $motionDetectorsValues[] = ['rowColor' => $rowColor];
            $motionDetectorVariableProfileListValues[] = ['SensorID' => $sensorID, 'Designation' => $motionDetector['Designation'], 'Comment' => $motionDetector['Comment']];
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
                        'caption' => 'Bewegungsmelder ermitteln',
                        'popup'   => [
                            'caption' => 'Bewegungsmelder wirklich automatisch ermitteln und hinzufügen?',
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
                                            'caption' => 'Ident: MOTION',
                                            'value'   => 1
                                        ],
                                        [
                                            'caption' => 'Ident: Benutzerdefiniert',
                                            'value'   => 2
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
                                    'columns' => [
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
                                        ]
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
                        'caption' => 'Aktueller Status',
                        'popup'   => [
                            'caption' => 'Aktueller Status',
                            'items'   => [
                                [
                                    'type'     => 'List',
                                    'name'     => 'ActualMotionDetectorStateList',
                                    'caption'  => 'Bewegungsmelder',
                                    'add'      => false,
                                    'rowCount' => 1,
                                    'sort'     => [
                                        'column'    => 'ActualStatus',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
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
                        'rowCount' => $amountMotionDetectorRows,
                        'add'      => true,
                        'delete'   => true,
                        'sort'     => [
                            'column'    => 'Designation',
                            'direction' => 'ascending'
                        ],
                        'columns' => [
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
                                'caption' => 'Name',
                                'name'    => 'Designation',
                                'width'   => '400px',
                                'add'     => '',
                                'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "MotionDetectorsConfigurationButton", $MotionDetectors["PrimaryCondition"]);',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => 'Bemerkung',
                                'name'    => 'Comment',
                                'width'   => '300px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
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
                                'caption' => 'Primäre Bedingung',
                                'name'    => 'PrimaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectCondition'
                                ]
                            ],
                            [
                                'caption' => 'Weitere Bedingungen',
                                'name'    => 'SecondaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type'  => 'SelectCondition',
                                    'multi' => true
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
                                'caption' => 'Erneute Überprüfung nach',
                                'name'    => 'MotionDetectorStatusVerificationDelay',
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
                                'caption' => 'Ereignisprotokoll',
                                'name'    => 'UseEventProtocol',
                                'width'   => '140px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmprotokoll',
                                'name'    => 'UseAlarmProtocol',
                                'width'   => '140px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Benachrichtigung',
                                'name'    => 'UseNotification',
                                'width'   => '160px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Außensirene',
                                'name'    => 'UseAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Innensirene',
                                'name'    => 'UseInternalAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmbeleuchtung',
                                'name'    => 'UseAlarmLight',
                                'width'   => '170px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmanruf',
                                'name'    => 'UseAlarmCall',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Panikalarm',
                                'name'    => 'UsePanicAlarm',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktion',
                                'name'    => 'UseAlertingAction',
                                'width'   => '80px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktionsausführung',
                                'name'    => 'AlertingAction',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectAction'
                                ]
                            ]
                        ],
                        'values' => $motionDetectorsValues,
                    ],
                    [
                        'type'    => 'Label',
                        'caption' => 'Anzahl Auslöser: ' . $amountMotionDetectors
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Erneute Überprüfung festlegen',
                        'popup'   => [
                            'caption' => 'Erneute Überprüfung wirklich festlegen?',
                            'items'   => [
                                [
                                    'type'    => 'NumberSpinner',
                                    'name'    => 'MotionDetectorVerificationDelay',
                                    'caption' => 'Erneute Überprüfung nach',
                                    'suffix'  => 'Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Festlegen',
                                    'onClick' => self::MODULE_PREFIX . '_ConfigureMotionDetectorVerificationDelay($id, $MotionDetectorVerificationDelay);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'MotionDetectorVerificationDelayProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'MotionDetectorVerificationDelayProgressInfo',
                                    'caption' => '',
                                    'visible' => false
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
                                    'type'     => 'List',
                                    'name'     => 'MotionDetectorVariableProfileList',
                                    'caption'  => 'Variablen',
                                    'add'      => false,
                                    'rowCount' => $amountMotionDetectors,
                                    'sort'     => [
                                        'column'    => 'SensorID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
                                        [
                                            'caption' => 'Auswahl',
                                            'name'    => 'Use',
                                            'width'   => '100px',
                                            'add'     => true,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'caption' => 'Profil umkehren',
                                            'name'    => 'UseReversedProfile',
                                            'width'   => '150px',
                                            'add'     => false,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'name'    => 'SensorID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
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
                                        ]
                                    ],
                                    'values' => $motionDetectorVariableProfileListValues,
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Zuweisen',
                                    'onClick' => self::MODULE_PREFIX . '_AssignMotionDetectorVariableProfile($id, $MotionDetectorVariableProfileList);'
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
                    [
                        'type'     => 'OpenObjectButton',
                        'name'     => 'MotionDetectorsConfigurationButton',
                        'caption'  => 'Bearbeiten',
                        'visible'  => false,
                        'objectID' => 0
                    ]
                ]
            ];

        //Glass breakage sensors
        $glassBreakageDetectorValues = [];
        $glassBreakageDetectors = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
        $glassBreakageDetectorVariableProfileListValues = [];
        $amountGlassBreakageDetectors = count($glassBreakageDetectors);
        $amountGlassBreakageDetectorRows = count($glassBreakageDetectors) + 1;
        if ($amountGlassBreakageDetectorRows == 1) {
            $amountGlassBreakageDetectorRows = 3;
        }
        foreach ($glassBreakageDetectors as $glassBreakageDetector) {
            $sensorID = 0;
            $rowColor = '#C0FFC0'; //light green
            if (!$glassBreakageDetector['Use']) {
                $rowColor = '#DFDFDF'; //grey
            }
            //Primary condition
            if ($glassBreakageDetector['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($glassBreakageDetector['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $sensorID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($sensorID <= 1 || !@IPS_ObjectExists($sensorID)) {
                            $rowColor = '#FFC0C0'; //red
                        }
                    }
                }
            }
            //Secondary condition, multi
            if ($glassBreakageDetector['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($glassBreakageDetector['SecondaryCondition'], true);
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
            if ($glassBreakageDetector['UseAlertingAction']) {
                if ($glassBreakageDetector['AlertingAction'] != '') {
                    $action = json_decode($glassBreakageDetector['AlertingAction'], true);
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
            $glassBreakageDetectorValues[] = ['rowColor' => $rowColor];
            $glassBreakageDetectorVariableProfileListValues[] = ['SensorID' => $sensorID, 'Designation' => $glassBreakageDetector['Designation'], 'Comment' => $glassBreakageDetector['Comment']];
        }

        $form['elements'][] =
            [
                'type'     => 'ExpansionPanel',
                'caption'  => 'Glasbruchmelder',
                'name'     => 'Panel6',
                'expanded' => false,
                'items'    => [
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Glasbruchmelder ermitteln',
                        'popup'   => [
                            'caption' => 'Glasbruchmelder wirklich automatisch ermitteln und hinzufügen?',
                            'items'   => [
                                [
                                    'type'    => 'Select',
                                    'name'    => 'GlassBreakageDetectorDeterminationType',
                                    'caption' => 'Auswahl',
                                    'options' => [
                                        [
                                            'caption' => 'Profil auswählen',
                                            'value'   => 0
                                        ],
                                        [
                                            'caption' => 'Ident: STATE',
                                            'value'   => 1
                                        ],
                                        [
                                            'caption' => 'Ident: Benutzerdefiniert',
                                            'value'   => 2
                                        ]
                                    ],
                                    'value'    => 0,
                                    'onChange' => self::MODULE_PREFIX . '_CheckGlassBreakageDetectorDeterminationValue($id, $GlassBreakageDetectorDeterminationType);'
                                ],
                                [
                                    'type'    => 'SelectProfile',
                                    'name'    => 'GlassBreakageDetectorDeterminationProfileSelection',
                                    'caption' => 'Profil',
                                    'visible' => true
                                ],
                                [
                                    'type'    => 'ValidationTextBox',
                                    'name'    => 'GlassBreakageDetectorDeterminationValue',
                                    'caption' => 'Identifikator',
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Ermitteln',
                                    'onClick' => self::MODULE_PREFIX . '_DetermineGlassBreakageDetectorVariables($id, $GlassBreakageDetectorDeterminationType, $GlassBreakageDetectorDeterminationValue, $GlassBreakageDetectorDeterminationProfileSelection);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'GlassBreakageDetectorDeterminationProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'GlassBreakageDetectorDeterminationProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ],
                                [
                                    'type'     => 'List',
                                    'name'     => 'DeterminedGlassBreakageDetectorVariableList',
                                    'caption'  => 'Variablen',
                                    'visible'  => false,
                                    'rowCount' => 1,
                                    'delete'   => true,
                                    'sort'     => [
                                        'column'    => 'ID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
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
                                        ]
                                    ]
                                ],
                                [
                                    'type'    => 'CheckBox',
                                    'name'    => 'OverwriteGlassBreakageDetectorVariableProfiles',
                                    'caption' => 'Variablenprofile überschreiben',
                                    'visible' => false,
                                    'value'   => true
                                ],
                                [
                                    'type'    => 'Button',
                                    'name'    => 'ApplyPreGlassBreakageDetectorTriggerValues',
                                    'caption' => 'Übernehmen',
                                    'visible' => false,
                                    'onClick' => self::MODULE_PREFIX . '_ApplyDeterminedGlassBreakageDetectorVariables($id, $DeterminedGlassBreakageDetectorVariableList, $OverwriteGlassBreakageDetectorVariableProfiles);'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Aktueller Status',
                        'popup'   => [
                            'caption' => 'Aktueller Status',
                            'items'   => [
                                [
                                    'type'     => 'List',
                                    'name'     => 'ActualGlassBreakageDetectorStateList',
                                    'caption'  => 'Glasbruchmelder',
                                    'add'      => false,
                                    'rowCount' => 1,
                                    'sort'     => [
                                        'column'    => 'ActualStatus',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
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
                                            'onClick' => self::MODULE_PREFIX . '_ModifyActualVariableStatesConfigurationButton($id, "ActualGlassBreakageDetectorStateConfigurationButton", $ActualGlassBreakageDetectorStateList["SensorID"]);',
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
                                    'name'     => 'ActualGlassBreakageDetectorStateConfigurationButton',
                                    'caption'  => 'Bearbeiten',
                                    'visible'  => false,
                                    'objectID' => 0
                                ]
                            ]
                        ],
                        'onClick' => self::MODULE_PREFIX . '_GetActualGlassBreakageDetectorStates($id);'
                    ],
                    [
                        'type'     => 'List',
                        'name'     => 'GlassBreakageDetectors',
                        'caption'  => 'Glasbruchmelder',
                        'rowCount' => $amountGlassBreakageDetectorRows,
                        'add'      => true,
                        'delete'   => true,
                        'sort'     => [
                            'column'    => 'Designation',
                            'direction' => 'ascending'
                        ],
                        'columns' => [
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
                                'caption' => 'Name',
                                'name'    => 'Designation',
                                'width'   => '400px',
                                'add'     => '',
                                'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "GlassBreakageDetectorsConfigurationButton", $GlassBreakageDetectors["PrimaryCondition"]);',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => 'Bemerkung',
                                'name'    => 'Comment',
                                'width'   => '300px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
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
                                'caption' => 'Primäre Bedingung',
                                'name'    => 'PrimaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectCondition'
                                ]
                            ],
                            [
                                'caption' => 'Weitere Bedingungen',
                                'name'    => 'SecondaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type'  => 'SelectCondition',
                                    'multi' => true
                                ]
                            ],
                            [
                                'caption' => 'Permanente Überwachung',
                                'name'    => 'PermanentMonitoring',
                                'width'   => '230px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Vollschutz',
                                'name'    => 'FullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Hüllschutz',
                                'name'    => 'HullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Teilschutz',
                                'name'    => 'PartialProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Erneute Überprüfung nach',
                                'name'    => 'OpenGlassBreakageDetectorStatusVerificationDelay',
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
                                'caption' => 'Alarmprotokoll',
                                'name'    => 'UseAlarmProtocol',
                                'width'   => '140px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Benachrichtigung',
                                'name'    => 'UseNotification',
                                'width'   => '160px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Außensirene',
                                'name'    => 'UseAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Innensirene',
                                'name'    => 'UseInternalAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmbeleuchtung',
                                'name'    => 'UseAlarmLight',
                                'width'   => '170px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmanruf',
                                'name'    => 'UseAlarmCall',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Panikalarm',
                                'name'    => 'UsePanicAlarm',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktion',
                                'name'    => 'UseAlertingAction',
                                'width'   => '80px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktionsausführung',
                                'name'    => 'AlertingAction',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectAction'
                                ]
                            ]
                        ],
                        'values' => $glassBreakageDetectorValues,
                    ],
                    [
                        'type'    => 'Label',
                        'caption' => 'Anzahl Auslöser: ' . $amountGlassBreakageDetectors
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Erneute Überprüfung festlegen',
                        'popup'   => [
                            'caption' => 'Erneute Überprüfung wirklich festlegen?',
                            'items'   => [
                                [
                                    'type'    => 'NumberSpinner',
                                    'name'    => 'GlassBreakageDetectorVerificationDelay',
                                    'caption' => 'Erneute Überprüfung nach',
                                    'suffix'  => 'Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Festlegen',
                                    'onClick' => self::MODULE_PREFIX . '_ConfigureGlassBreakageDetectorVerificationDelay($id, $GlassBreakageDetectorVerificationDelay);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'GlassBreakageDetectorVerificationDelayProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'GlassBreakageDetectorVerificationDelayProgressInfo',
                                    'caption' => '',
                                    'visible' => false
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
                                    'type'     => 'List',
                                    'name'     => 'GlassBreakageDetectorVariableProfileList',
                                    'caption'  => 'Variablen',
                                    'add'      => false,
                                    'rowCount' => $amountGlassBreakageDetectors,
                                    'sort'     => [
                                        'column'    => 'SensorID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
                                        [
                                            'caption' => 'Auswahl',
                                            'name'    => 'Use',
                                            'width'   => '100px',
                                            'add'     => true,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'caption' => 'Profil umkehren',
                                            'name'    => 'UseReversedProfile',
                                            'width'   => '150px',
                                            'add'     => false,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'name'    => 'SensorID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
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
                                        ]
                                    ],
                                    'values' => $glassBreakageDetectorVariableProfileListValues,
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Zuweisen',
                                    'onClick' => self::MODULE_PREFIX . '_AssignGlassBreakageDetectorVariableProfile($id, $GlassBreakageDetectorVariableProfileList);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'AssignGlassBreakageDetectorVariableProfileProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'AssignGlassBreakageDetectorVariableProfileProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'     => 'OpenObjectButton',
                        'name'     => 'GlassBreakageDetectorsConfigurationButton',
                        'caption'  => 'Bearbeiten',
                        'visible'  => false,
                        'objectID' => 0
                    ]
                ]
            ];

        //Smoke detectors
        $smokeDetectorValues = [];
        $smokeDetectorVariableProfileListValues = [];
        $smokeDetectors = json_decode($this->ReadPropertyString('SmokeDetectors'), true);
        $amountSmokeDetectors = count($smokeDetectors);
        $amountSmokeDetectorRows = count($smokeDetectors) + 1;
        if ($amountSmokeDetectorRows == 1) {
            $amountSmokeDetectorRows = 3;
        }
        foreach ($smokeDetectors as $smokeDetector) {
            $sensorID = 0;
            $rowColor = '#C0FFC0'; //light green
            if (!$smokeDetector['Use']) {
                $rowColor = '#DFDFDF'; //grey
            }
            //Primary condition
            if ($smokeDetector['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($smokeDetector['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $sensorID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($sensorID <= 1 || !@IPS_ObjectExists($sensorID)) {
                            $rowColor = '#FFC0C0'; //red
                        }
                    }
                }
            }
            //Secondary condition, multi
            if ($smokeDetector['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($smokeDetector['SecondaryCondition'], true);
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
            if ($smokeDetector['UseAlertingAction']) {
                if ($smokeDetector['AlertingAction'] != '') {
                    $action = json_decode($smokeDetector['AlertingAction'], true);
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
            $smokeDetectorValues[] = ['rowColor' => $rowColor];
            $smokeDetectorVariableProfileListValues[] = ['SensorID' => $sensorID, 'Designation' => $smokeDetector['Designation'], 'Comment' => $smokeDetector['Comment']];
        }

        $form['elements'][] =
            [
                'type'     => 'ExpansionPanel',
                'caption'  => 'Rauchmelder',
                'name'     => 'Panel7',
                'expanded' => false,
                'items'    => [
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Rauchmelder ermitteln',
                        'popup'   => [
                            'caption' => 'Rauchmelder wirklich automatisch ermitteln und hinzufügen?',
                            'items'   => [
                                [
                                    'type'    => 'Select',
                                    'name'    => 'SmokeDetectorDeterminationType',
                                    'caption' => 'Auswahl',
                                    'options' => [
                                        [
                                            'caption' => 'Profil auswählen',
                                            'value'   => 0
                                        ],
                                        [
                                            'caption' => 'Ident: SMOKE_DETECTOR_ALARM_STATUS',
                                            'value'   => 1
                                        ],
                                        [
                                            'caption' => 'Ident: Benutzerdefiniert',
                                            'value'   => 2
                                        ]
                                    ],
                                    'value'    => 0,
                                    'onChange' => self::MODULE_PREFIX . '_CheckSmokeDetectorDeterminationValue($id, $SmokeDetectorDeterminationType);'
                                ],
                                [
                                    'type'    => 'SelectProfile',
                                    'name'    => 'SmokeDetectorDeterminationProfileSelection',
                                    'caption' => 'Profil',
                                    'visible' => true
                                ],
                                [
                                    'type'    => 'ValidationTextBox',
                                    'name'    => 'SmokeDetectorDeterminationValue',
                                    'caption' => 'Identifikator',
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Ermitteln',
                                    'onClick' => self::MODULE_PREFIX . '_DetermineSmokeDetectorVariables($id, $SmokeDetectorDeterminationType, $SmokeDetectorDeterminationValue, $SmokeDetectorDeterminationProfileSelection);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'SmokeDetectorDeterminationProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'SmokeDetectorDeterminationProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ],
                                [
                                    'type'     => 'List',
                                    'name'     => 'DeterminedSmokeDetectorVariableList',
                                    'caption'  => 'Variablen',
                                    'visible'  => false,
                                    'rowCount' => 1,
                                    'delete'   => true,
                                    'sort'     => [
                                        'column'    => 'ID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
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
                                        ]
                                    ]
                                ],
                                [
                                    'type'    => 'CheckBox',
                                    'name'    => 'OverwriteSmokeDetectorVariableProfiles',
                                    'caption' => 'Variablenprofile überschreiben',
                                    'visible' => false,
                                    'value'   => true
                                ],
                                [
                                    'type'    => 'Button',
                                    'name'    => 'ApplyPreSmokeDetectorTriggerValues',
                                    'caption' => 'Übernehmen',
                                    'visible' => false,
                                    'onClick' => self::MODULE_PREFIX . '_ApplyDeterminedSmokeDetectorVariables($id, $DeterminedSmokeDetectorVariableList, $OverwriteSmokeDetectorVariableProfiles);'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Aktueller Status',
                        'popup'   => [
                            'caption' => 'Aktueller Status',
                            'items'   => [
                                [
                                    'type'     => 'List',
                                    'name'     => 'ActualSmokeDetectorStateList',
                                    'caption'  => 'Rauchmelder',
                                    'add'      => false,
                                    'rowCount' => 1,
                                    'sort'     => [
                                        'column'    => 'ActualStatus',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
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
                                            'onClick' => self::MODULE_PREFIX . '_ModifyActualVariableStatesConfigurationButton($id, "ActualSmokeDetectorStateConfigurationButton", $ActualSmokeDetectorStateList["SensorID"]);',
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
                                    'name'     => 'ActualSmokeDetectorStateConfigurationButton',
                                    'caption'  => 'Bearbeiten',
                                    'visible'  => false,
                                    'objectID' => 0
                                ]
                            ]
                        ],
                        'onClick' => self::MODULE_PREFIX . '_GetActualSmokeDetectorStates($id);'
                    ],
                    [
                        'type'     => 'List',
                        'name'     => 'SmokeDetectors',
                        'caption'  => 'Rauchmelder',
                        'rowCount' => $amountSmokeDetectorRows,
                        'add'      => true,
                        'delete'   => true,
                        'sort'     => [
                            'column'    => 'Designation',
                            'direction' => 'ascending'
                        ],
                        'columns' => [
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
                                'caption' => 'Name',
                                'name'    => 'Designation',
                                'width'   => '400px',
                                'add'     => '',
                                'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "SmokeDetectorsConfigurationButton", $SmokeDetectors["PrimaryCondition"]);',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => 'Bemerkung',
                                'name'    => 'Comment',
                                'width'   => '300px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
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
                                'caption' => 'Primäre Bedingung',
                                'name'    => 'PrimaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectCondition'
                                ]
                            ],
                            [
                                'caption' => 'Weitere Bedingungen',
                                'name'    => 'SecondaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type'  => 'SelectCondition',
                                    'multi' => true
                                ]
                            ],
                            [
                                'caption' => 'Permanente Überwachung',
                                'name'    => 'PermanentMonitoring',
                                'width'   => '230px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Vollschutz',
                                'name'    => 'FullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Hüllschutz',
                                'name'    => 'HullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Teilschutz',
                                'name'    => 'PartialProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Erneute Überprüfung nach',
                                'name'    => 'AlertingSmokeDetectorStatusVerificationDelay',
                                'width'   => '220px',
                                'add'     => 0,
                                'edit'    => [
                                    'type'    => 'NumberSpinner',
                                    'suffix'  => ' Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ]
                            ],
                            [
                                'caption' => 'Alarmprotokoll',
                                'name'    => 'UseAlarmProtocol',
                                'width'   => '140px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Benachrichtigung',
                                'name'    => 'UseNotification',
                                'width'   => '160px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Außensirene',
                                'name'    => 'UseAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Innensirene',
                                'name'    => 'UseInternalAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmbeleuchtung',
                                'name'    => 'UseAlarmLight',
                                'width'   => '170px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmanruf',
                                'name'    => 'UseAlarmCall',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Panikalarm',
                                'name'    => 'UsePanicAlarm',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktion',
                                'name'    => 'UseAlertingAction',
                                'width'   => '80px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktionsausführung',
                                'name'    => 'AlertingAction',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectAction'
                                ]
                            ]
                        ],
                        'values' => $smokeDetectorValues,
                    ],
                    [
                        'type'    => 'Label',
                        'caption' => 'Anzahl Auslöser: ' . $amountSmokeDetectors
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Erneute Überprüfung festlegen',
                        'popup'   => [
                            'caption' => 'Erneute Überprüfung wirklich festlegen?',
                            'items'   => [
                                [
                                    'type'    => 'NumberSpinner',
                                    'name'    => 'SmokeDetectorVerificationDelay',
                                    'caption' => 'Erneute Überprüfung nach',
                                    'suffix'  => 'Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Festlegen',
                                    'onClick' => self::MODULE_PREFIX . '_ConfigureSmokeDetectorVerificationDelay($id, $SmokeDetectorVerificationDelay);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'SmokeDetectorVerificationDelayProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'SmokeDetectorVerificationDelayProgressInfo',
                                    'caption' => '',
                                    'visible' => false
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
                                    'type'     => 'List',
                                    'name'     => 'SmokeDetectorVariableProfileList',
                                    'caption'  => 'Variablen',
                                    'add'      => false,
                                    'rowCount' => $amountSmokeDetectors,
                                    'sort'     => [
                                        'column'    => 'SensorID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
                                        [
                                            'caption' => 'Auswahl',
                                            'name'    => 'Use',
                                            'width'   => '100px',
                                            'add'     => true,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'caption' => 'Profil umkehren',
                                            'name'    => 'UseReversedProfile',
                                            'width'   => '150px',
                                            'add'     => false,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'name'    => 'SensorID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
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
                                        ]
                                    ],
                                    'values' => $smokeDetectorVariableProfileListValues,
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Zuweisen',
                                    'onClick' => self::MODULE_PREFIX . '_AssignSmokeDetectorVariableProfile($id, $SmokeDetectorVariableProfileList);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'AssignSmokeDetectorVariableProfileProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'AssignSmokeDetectorVariableProfileProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'     => 'OpenObjectButton',
                        'name'     => 'SmokeDetectorsConfigurationButton',
                        'caption'  => 'Bearbeiten',
                        'visible'  => false,
                        'objectID' => 0
                    ]
                ]
            ];

        //Water detectors
        $waterDetectorValues = [];
        $waterDetectorVariableProfileListValues = [];
        $waterDetectors = json_decode($this->ReadPropertyString('WaterDetectors'), true);
        $amountWaterDetectors = count($waterDetectors);
        $amountWaterDetectorRows = count($waterDetectors) + 1;
        if ($amountWaterDetectorRows == 1) {
            $amountWaterDetectorRows = 3;
        }
        foreach ($waterDetectors as $waterDetector) {
            $sensorID = 0;
            $rowColor = '#C0FFC0'; //light green
            if (!$waterDetector['Use']) {
                $rowColor = '#DFDFDF'; //grey
            }
            //Primary condition
            if ($waterDetector['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($waterDetector['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $sensorID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($sensorID <= 1 || !@IPS_ObjectExists($sensorID)) {
                            $rowColor = '#FFC0C0'; //red
                        }
                    }
                }
            }
            //Secondary condition, multi
            if ($waterDetector['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($waterDetector['SecondaryCondition'], true);
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
            if ($waterDetector['UseAlertingAction']) {
                if ($waterDetector['AlertingAction'] != '') {
                    $action = json_decode($waterDetector['AlertingAction'], true);
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
            $waterDetectorValues[] = ['rowColor' => $rowColor];
            $waterDetectorVariableProfileListValues[] = ['SensorID' => $sensorID, 'Designation' => $waterDetector['Designation'], 'Comment' => $waterDetector['Comment']];
        }

        $form['elements'][] =
            [
                'type'     => 'ExpansionPanel',
                'caption'  => 'Wassermelder',
                'name'     => 'Panel8',
                'expanded' => false,
                'items'    => [
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Wassermelder ermitteln',
                        'popup'   => [
                            'caption' => 'Wassermelder wirklich automatisch ermitteln und hinzufügen?',
                            'items'   => [
                                [
                                    'type'    => 'Select',
                                    'name'    => 'WaterDetectorDeterminationType',
                                    'caption' => 'Auswahl',
                                    'options' => [
                                        [
                                            'caption' => 'Profil auswählen',
                                            'value'   => 0
                                        ],
                                        [
                                            'caption' => 'Ident: ALARMSTATE',
                                            'value'   => 1
                                        ],
                                        [
                                            'caption' => 'Ident: Benutzerdefiniert',
                                            'value'   => 2
                                        ]
                                    ],
                                    'value'    => 0,
                                    'onChange' => self::MODULE_PREFIX . '_CheckWaterDetectorDeterminationValue($id, $WaterDetectorDeterminationType);'
                                ],
                                [
                                    'type'    => 'SelectProfile',
                                    'name'    => 'WaterDetectorDeterminationProfileSelection',
                                    'caption' => 'Profil',
                                    'visible' => true
                                ],
                                [
                                    'type'    => 'ValidationTextBox',
                                    'name'    => 'WaterDetectorDeterminationValue',
                                    'caption' => 'Identifikator',
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Ermitteln',
                                    'onClick' => self::MODULE_PREFIX . '_DetermineWaterDetectorVariables($id, $WaterDetectorDeterminationType, $WaterDetectorDeterminationValue, $WaterDetectorDeterminationProfileSelection);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'WaterDetectorDeterminationProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'WaterDetectorDeterminationProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ],
                                [
                                    'type'     => 'List',
                                    'name'     => 'DeterminedWaterDetectorVariableList',
                                    'caption'  => 'Variablen',
                                    'visible'  => false,
                                    'rowCount' => 1,
                                    'delete'   => true,
                                    'sort'     => [
                                        'column'    => 'ID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
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
                                        ]
                                    ]
                                ],
                                [
                                    'type'    => 'CheckBox',
                                    'name'    => 'OverwriteWaterDetectorVariableProfiles',
                                    'caption' => 'Variablenprofile überschreiben',
                                    'visible' => false,
                                    'value'   => true
                                ],
                                [
                                    'type'    => 'Button',
                                    'name'    => 'ApplyPreWaterDetectorTriggerValues',
                                    'caption' => 'Übernehmen',
                                    'visible' => false,
                                    'onClick' => self::MODULE_PREFIX . '_ApplyDeterminedWaterDetectorVariables($id, $DeterminedWaterDetectorVariableList, $OverwriteWaterDetectorVariableProfiles);'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Aktueller Status',
                        'popup'   => [
                            'caption' => 'Aktueller Status',
                            'items'   => [
                                [
                                    'type'     => 'List',
                                    'name'     => 'ActualWaterDetectorStateList',
                                    'caption'  => 'Wassermelder',
                                    'add'      => false,
                                    'rowCount' => 1,
                                    'sort'     => [
                                        'column'    => 'ActualStatus',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
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
                                            'onClick' => self::MODULE_PREFIX . '_ModifyActualVariableStatesConfigurationButton($id, "ActualWaterDetectorStateConfigurationButton", $ActualWaterDetectorStateList["SensorID"]);',
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
                                    'name'     => 'ActualWaterDetectorStateConfigurationButton',
                                    'caption'  => 'Bearbeiten',
                                    'visible'  => false,
                                    'objectID' => 0
                                ]
                            ]
                        ],
                        'onClick' => self::MODULE_PREFIX . '_GetActualWaterDetectorStates($id);'
                    ],
                    [
                        'type'     => 'List',
                        'name'     => 'WaterDetectors',
                        'caption'  => 'Wassermelder',
                        'rowCount' => $amountWaterDetectorRows,
                        'add'      => true,
                        'delete'   => true,
                        'sort'     => [
                            'column'    => 'Designation',
                            'direction' => 'ascending'
                        ],
                        'columns' => [
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
                                'caption' => 'Name',
                                'name'    => 'Designation',
                                'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "WaterDetectorsConfigurationButton", $WaterDetectors["PrimaryCondition"]);',
                                'width'   => '400px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
                                ]
                            ],
                            [
                                'caption' => 'Bemerkung',
                                'name'    => 'Comment',
                                'width'   => '300px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'ValidationTextBox'
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
                                'caption' => 'Primäre Bedingung',
                                'name'    => 'PrimaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectCondition'
                                ]
                            ],
                            [
                                'caption' => 'Weitere Bedingungen',
                                'name'    => 'SecondaryCondition',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type'  => 'SelectCondition',
                                    'multi' => true
                                ]
                            ],
                            [
                                'caption' => 'Permanente Überwachung',
                                'name'    => 'PermanentMonitoring',
                                'width'   => '230px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Vollschutz',
                                'name'    => 'FullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Hüllschutz',
                                'name'    => 'HullProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Teilschutz',
                                'name'    => 'PartialProtectionModeActive',
                                'width'   => '110px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Erneute Überprüfung nach',
                                'name'    => 'AlertingWaterDetectorStatusVerificationDelay',
                                'width'   => '220px',
                                'add'     => 0,
                                'edit'    => [
                                    'type'    => 'NumberSpinner',
                                    'suffix'  => ' Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ]
                            ],
                            [
                                'caption' => 'Alarmprotokoll',
                                'name'    => 'UseAlarmProtocol',
                                'width'   => '140px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Benachrichtigung',
                                'name'    => 'UseNotification',
                                'width'   => '160px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Außensirene',
                                'name'    => 'UseAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Innensirene',
                                'name'    => 'UseInternalAlarmSiren',
                                'width'   => '120px',
                                'add'     => true,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmbeleuchtung',
                                'name'    => 'UseAlarmLight',
                                'width'   => '170px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Alarmanruf',
                                'name'    => 'UseAlarmCall',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Panikalarm',
                                'name'    => 'UsePanicAlarm',
                                'width'   => '120px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktion',
                                'name'    => 'UseAlertingAction',
                                'width'   => '80px',
                                'add'     => false,
                                'edit'    => [
                                    'type' => 'CheckBox'
                                ]
                            ],
                            [
                                'caption' => 'Aktionsausführung',
                                'name'    => 'AlertingAction',
                                'width'   => '1000px',
                                'add'     => '',
                                'edit'    => [
                                    'type' => 'SelectAction'
                                ]
                            ]
                        ],
                        'values' => $waterDetectorValues,
                    ],
                    [
                        'type'    => 'Label',
                        'caption' => 'Anzahl Auslöser: ' . $amountWaterDetectors
                    ],
                    [
                        'type'    => 'PopupButton',
                        'caption' => 'Erneute Überprüfung festlegen',
                        'popup'   => [
                            'caption' => 'Erneute Überprüfung wirklich festlegen?',
                            'items'   => [
                                [
                                    'type'    => 'NumberSpinner',
                                    'name'    => 'WaterDetectorVerificationDelay',
                                    'caption' => 'Erneute Überprüfung nach',
                                    'suffix'  => 'Millisekunden',
                                    'minimum' => 0,
                                    'maximum' => 10000
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Festlegen',
                                    'onClick' => self::MODULE_PREFIX . '_ConfigureWaterDetectorVerificationDelay($id, $WaterDetectorVerificationDelay);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'WaterDetectorVerificationDelayProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'WaterDetectorVerificationDelayProgressInfo',
                                    'caption' => '',
                                    'visible' => false
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
                                    'type'     => 'List',
                                    'name'     => 'WaterDetectorVariableProfileList',
                                    'caption'  => 'Variablen',
                                    'add'      => false,
                                    'rowCount' => $amountWaterDetectors,
                                    'sort'     => [
                                        'column'    => 'SensorID',
                                        'direction' => 'ascending'
                                    ],
                                    'columns' => [
                                        [
                                            'caption' => 'Auswahl',
                                            'name'    => 'Use',
                                            'width'   => '100px',
                                            'add'     => true,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'caption' => 'Profil umkehren',
                                            'name'    => 'UseReversedProfile',
                                            'width'   => '150px',
                                            'add'     => false,
                                            'edit'    => [
                                                'type' => 'CheckBox'
                                            ]
                                        ],
                                        [
                                            'name'    => 'SensorID',
                                            'caption' => 'ID',
                                            'width'   => '80px',
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
                                        ]
                                    ],
                                    'values' => $waterDetectorVariableProfileListValues,
                                ],
                                [
                                    'type'    => 'Button',
                                    'caption' => 'Zuweisen',
                                    'onClick' => self::MODULE_PREFIX . '_AssignWaterDetectorVariableProfile($id, $WaterDetectorVariableProfileList);'
                                ],
                                [
                                    'type'    => 'ProgressBar',
                                    'name'    => 'AssignWaterDetectorVariableProfileProgress',
                                    'caption' => 'Fortschritt',
                                    'minimum' => 0,
                                    'maximum' => 100,
                                    'visible' => false
                                ],
                                [
                                    'type'    => 'Label',
                                    'name'    => 'AssignWaterDetectorVariableProfileProgressInfo',
                                    'caption' => '',
                                    'visible' => false
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'     => 'OpenObjectButton',
                        'name'     => 'WaterDetectorsConfigurationButton',
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
            'name'     => 'Panel9',
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
                            'width'    => '1000px',
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
            'caption'  => 'Benachrichtigungen',
            'name'     => 'Panel10',
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
                            'width'    => '1000px',
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
                //Disarmed
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Unscharf',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'List',
                            'name'     => 'DeactivationNotification',
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                ],
                //Full protection
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Vollschutz',
                    'expanded' => false,
                    'items'    => [
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                            'caption'  => 'Teilscharf (Tür/Fenster geöffnet)',
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                            'caption'  => 'Scharf',
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                        ]
                    ]
                ],
                //Hull protection
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Hüllschutz',
                    'expanded' => false,
                    'items'    => [
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                            'caption'  => 'Teilscharf (Tür/Fenster geöffnet)',
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                            'caption'  => 'Scharf',
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                        ]
                    ]
                ],
                //Partial protection
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Teilschutz',
                    'expanded' => false,
                    'items'    => [
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                            'caption'  => 'Teilscharf (Tür/Fenster geöffnet)',
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                            'caption'  => 'Scharf',
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Tür/Fenster geöffnet:',
                                    'name'    => 'LabelOpenDoorWindowNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Tür/Fenster',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'List',
                            'name'     => 'OpenDoorWindowNotification',
                            'caption'  => 'Geöffnet',
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                ],
                //Alarm
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Alarm',
                    'expanded' => false,
                    'items'    => [
                        //Door and window sensors
                        [
                            'type'     => 'List',
                            'name'     => 'DoorWindowAlarmNotification',
                            'caption'  => 'Tür- und Fenstersensoren',
                            'rowCount' => 1,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                        //Glass breakage detector
                        [
                            'type'     => 'List',
                            'name'     => 'GlassBreakageDetectorAlarmNotification',
                            'caption'  => 'Glasbruchmelder',
                            'rowCount' => 1,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                        //Smoke detector
                        [
                            'type'     => 'List',
                            'name'     => 'SmokeDetectorAlarmNotification',
                            'caption'  => 'Rauchmelder',
                            'rowCount' => 1,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                        //Water detector
                        [
                            'type'     => 'List',
                            'name'     => 'WaterDetectorAlarmNotification',
                            'caption'  => 'Wassermelder',
                            'rowCount' => 1,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                        //Off
                        [
                            'type'     => 'List',
                            'name'     => 'AlarmOffNotification',
                            'caption'  => 'Alarm Aus',
                            'rowCount' => 1,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                        //Panic alarm
                        [
                            'type'     => 'List',
                            'name'     => 'PanicAlarmNotification',
                            'caption'  => 'Panikalarm',
                            'rowCount' => 1,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Meldungstext:',
                                    'name'    => 'LabelMessageText',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Nachricht:',
                                    'name'    => 'LabelWebFrontNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Nachricht',
                                    'name'    => 'UseWebFrontNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Push-Nachricht:',
                                    'name'    => 'LabelWebFrontPushNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'WebFront Push-Nachricht',
                                    'name'    => 'UseWebFrontPushNotification',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'WebFrontPushNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Post-Nachricht:',
                                    'name'    => 'LabelTileVisualisationNotification',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Kachel Visualisierung',
                                    'name'    => 'UseTileVisualisationNotification',
                                    'width'   => '220px',
                                    'visible' => true,
                                    'edit'    => [
                                        'type' => 'CheckBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                                    'name'    => 'TileVisualisationNotificationTitle',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'ValidationTextBox'
                                    ]
                                ],
                                [
                                    'caption' => 'Icon',
                                    'name'    => 'TileVisualisationNotificationIcon',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectIcon'
                                    ]
                                ],
                                [
                                    'caption' => 'Sound',
                                    'name'    => 'TileVisualisationNotificationSound',
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
                                    'caption' => 'Ziel ID',
                                    'name'    => 'TileVisualisationNotificationTargetID',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'edit'    => [
                                        'type' => 'SelectObject'
                                    ]
                                ],
                                [
                                    'caption' => ' ',
                                    'name'    => 'SpacerMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail:',
                                    'name'    => 'LabelMail',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'E-Mail',
                                    'name'    => 'UseMailer',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'SMS:',
                                    'name'    => 'LabelSMS',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'SMS',
                                    'name'    => 'UseSMS',
                                    'width'   => '220px',
                                    'visible' => true,
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
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label'
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram:',
                                    'name'    => 'LabelTelegram',
                                    'width'   => '200px',
                                    'visible' => false,
                                    'save'    => false,
                                    'edit'    => [
                                        'type' => 'Label',
                                        'bold' => true
                                    ]
                                ],
                                [
                                    'caption' => 'Telegram',
                                    'name'    => 'UseTelegram',
                                    'width'   => '220px',
                                    'visible' => true,
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
                ]
            ]
        ];

        //Status indicator
        $statusIndicatorDisarmedActionVisible = $this->ReadPropertyBoolean('UseStatusIndicatorDisarmedAction');
        $statusIndicatorArmedActionVisible = $this->ReadPropertyBoolean('UseStatusIndicatorArmedAction');

        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Statusanzeige',
            'name'     => 'Panel11',
            'expanded' => false,
            'items'    => [
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Unscharf',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseStatusIndicatorDisarmedAction',
                            'caption'  => 'Aktiv',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "StatusIndicatorDisarmedAction", $UseStatusIndicatorDisarmedAction);'
                        ],
                        [
                            'type'    => 'SelectAction',
                            'name'    => 'StatusIndicatorDisarmedAction',
                            'visible' => $statusIndicatorDisarmedActionVisible
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Scharf',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseStatusIndicatorArmedAction',
                            'caption'  => 'Aktiv',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "StatusIndicatorArmedAction", $UseStatusIndicatorArmedAction);'
                        ],
                        [
                            'type'    => 'SelectAction',
                            'name'    => 'StatusIndicatorArmedAction',
                            'visible' => $statusIndicatorArmedActionVisible
                        ]
                    ]
                ]
            ]
        ];

        //Acknowledgement tone
        $acknowledgementToneDisarmedActionVisible = $this->ReadPropertyBoolean('UseAcknowledgementToneDisarmedAction');
        $acknowledgementToneArmedActionVisible = $this->ReadPropertyBoolean('UseAcknowledgementToneArmedAction');

        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Quittungston',
            'name'     => 'Panel12',
            'expanded' => false,
            'items'    => [
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Unscharf',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseAcknowledgementToneDisarmedAction',
                            'caption'  => 'Aktiv',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "AcknowledgementToneDisarmedAction", $UseAcknowledgementToneDisarmedAction);'
                        ],
                        [
                            'type'    => 'SelectAction',
                            'name'    => 'AcknowledgementToneDisarmedAction',
                            'visible' => $acknowledgementToneDisarmedActionVisible
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Scharf',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseAcknowledgementToneArmedAction',
                            'caption'  => 'Aktiv',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "AcknowledgementToneArmedAction", $UseAcknowledgementToneArmedAction);'
                        ],
                        [
                            'type'    => 'SelectAction',
                            'name'    => 'AcknowledgementToneArmedAction',
                            'visible' => $acknowledgementToneArmedActionVisible
                        ]
                    ]
                ]
            ]
        ];

        //Actions
        $disarmedActionVisible = $this->ReadPropertyBoolean('UseDisarmedAction');
        $fullProtectionActionVisible = $this->ReadPropertyBoolean('UseFullProtectionAction');
        $hullProtectionActionVisible = $this->ReadPropertyBoolean('UseHullProtectionAction');
        $partialProtectionActionVisible = $this->ReadPropertyBoolean('UsePartialProtectionAction');

        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Aktionen',
            'name'     => 'Panel13',
            'expanded' => false,
            'items'    => [
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Unscharf',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseDisarmedAction',
                            'caption'  => 'Aktiv',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "DisarmedAction", $UseDisarmedAction);'
                        ],
                        [
                            'type'    => 'SelectAction',
                            'name'    => 'DisarmedAction',
                            'visible' => $disarmedActionVisible
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Vollschutz',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseFullProtectionAction',
                            'caption'  => 'Aktiv',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "FullProtectionAction", $UseFullProtectionAction);'
                        ],
                        [
                            'type'    => 'SelectAction',
                            'name'    => 'FullProtectionAction',
                            'visible' => $fullProtectionActionVisible
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Hüllschutz',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseHullProtectionAction',
                            'caption'  => 'Aktiv',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "HullProtectionAction", $UseHullProtectionAction);'
                        ],
                        [
                            'type'    => 'SelectAction',
                            'name'    => 'HullProtectionAction',
                            'visible' => $hullProtectionActionVisible
                        ]
                    ]
                ],
                [
                    'type'     => 'ExpansionPanel',
                    'caption'  => 'Teilschutz',
                    'expanded' => false,
                    'items'    => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UsePartialProtectionAction',
                            'caption'  => 'Aktiv',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "PartialProtectionAction", $UsePartialProtectionAction);'
                        ],
                        [
                            'type'    => 'SelectAction',
                            'name'    => 'PartialProtectionAction',
                            'visible' => $partialProtectionActionVisible
                        ]
                    ]
                ]
            ]
        ];

        //Visualisation
        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Visualisierung',
            'name'     => 'Panel14',
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
                    'caption' => 'Alarm',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmSwitch',
                    'caption' => 'Alarm'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlertingSensor',
                    'caption' => 'Auslösender Alarmmelder'
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
                    'name'    => 'EnableGlassBreakageDetectorControlSwitch',
                    'caption' => 'Glasbruchmelder'
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
                    'name'    => 'EnableGlassBreakageDetectorState',
                    'caption' => 'Glasbruchmelderstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableSmokeDetectorState',
                    'caption' => 'Rauchmelderstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableWaterDetectorState',
                    'caption' => 'Wassermelderstatus'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmState',
                    'caption' => 'Alarmstatus'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Alarmauslösung',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmSirenState',
                    'caption' => 'Außensirene'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableInternalAlarmSirenState',
                    'caption' => 'Innensirene'
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
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnablePanicAlarmState',
                    'caption' => 'Panikalarm'
                ]
            ]
        ];

        ########## Actions

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

        //Registered references
        $registeredReferences = [];
        $references = $this->GetReferenceList();
        $amountReferences = count($references);
        if ($amountReferences == 0) {
            $amountReferences = 3;
        }
        foreach ($references as $reference) {
            $name = 'Objekt #' . $reference . ' existiert nicht';
            $location = '';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($reference)) {
                $name = IPS_GetName($reference);
                $location = IPS_GetLocation($reference);
                $rowColor = '#C0FFC0'; //light green
            }
            $registeredReferences[] = [
                'ObjectID'         => $reference,
                'Name'             => $name,
                'VariableLocation' => $location,
                'rowColor'         => $rowColor];
        }

        //Registered messages
        $registeredMessages = [];
        $messages = $this->GetMessageList();
        $amountMessages = count($messages);
        if ($amountMessages == 0) {
            $amountMessages = 3;
        }
        foreach ($messages as $id => $messageID) {
            $name = 'Objekt #' . $id . ' existiert nicht';
            $location = '';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($id)) {
                $name = IPS_GetName($id);
                $location = IPS_GetLocation($id);
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
                'VariableLocation'   => $location,
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
                    'caption' => 'Variablen',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'PopupButton',
                    'caption' => 'Neu anordnen',
                    'popup'   => [
                        'caption' => 'Variablen wirklich neu anordnen?',
                        'items'   => [
                            [
                                'type'    => 'Button',
                                'caption' => 'Neu anordnen',
                                'onClick' => self::MODULE_PREFIX . '_ReorderVariables($id);' . self::MODULE_PREFIX . '_UIShowMessage($id, "Variablen wurden neu angeordnet!");'
                            ]
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Benachrichtigungen',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'    => 'Button',
                            'caption' => 'Ziel IDs anzeigen',
                            'onClick' => self::MODULE_PREFIX . '_ShowNotificationTargetIDs($id);'
                        ],
                        [
                            'type'    => 'PopupButton',
                            'caption' => 'Ziel IDs zurücksetzen',
                            'popup'   => [
                                'caption' => 'Ziel IDs wirklich zurücksetzen?',
                                'items'   => [
                                    [
                                        'type'    => 'Button',
                                        'caption' => 'Zurücksetzen',
                                        'onClick' => self::MODULE_PREFIX . '_ResetNotificationTargetIDs($id);'
                                    ]
                                ]
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
                    'caption' => 'Sperrliste Tür- und Fenstersensoren',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'PopupButton',
                    'caption' => 'Aktueller Status',
                    'popup'   => [
                        'caption' => 'Aktueller Status',
                        'items'   => [
                            [
                                'type'     => 'List',
                                'name'     => 'Blacklist',
                                'caption'  => 'Sperrliste Tür- und Fenstersensoren',
                                'add'      => false,
                                'rowCount' => 1,
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
                                    ],
                                    [
                                        'caption' => 'Objektbaum',
                                        'name'    => 'VariableLocation',
                                        'width'   => '700px'
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
                    'onClick' => self::MODULE_PREFIX . '_GetBlackListedVariables($id);'
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
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " bearbeiten", $RegisteredReferences["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px'
                        ],
                        [
                            'caption' => 'Objektbaum',
                            'name'    => 'VariableLocation',
                            'width'   => '700px'
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
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " bearbeiten", $RegisteredMessages["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px'
                        ],
                        [
                            'caption' => 'Objektbaum',
                            'name'    => 'VariableLocation',
                            'width'   => '700px'
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