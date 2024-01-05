<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/helper/
 * @file          AZST_ConfigurationForm.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZST_ConfigurationForm
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
        for ($i = 1; $i <= 7; $i++) {
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
                    'caption' => 'Alarm Aus',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseDisarmAlarmZonesWhenAlarmSwitchIsOff',
                    'caption' => 'Alarmzonen unscharf'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Alarm An',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'AlertingSensorNameWhenAlarmSwitchIsOn',
                    'caption' => 'Alarmbezeichnung',
                    'width'   => '600px'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseAlarmSirenWhenAlarmSwitchIsOn',
                    'caption' => 'Alarmsirene'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseAlarmLightWhenAlarmSwitchIsOn',
                    'caption' => 'Alarmbeleuchtung'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseAlarmCallWhenAlarmSwitchIsOn',
                    'caption' => 'Alarmanruf'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UsePanicAlarmWhenAlarmSwitchIsOn',
                    'caption' => 'Panikalarm'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
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
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type' => 'CheckBox',
                            'name' => 'UseFullProtectionMode'
                        ],
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
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type' => 'CheckBox',
                            'name' => 'UseHullProtectionMode'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Hüllschutz',
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
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type' => 'CheckBox',
                            'name' => 'UsePartialProtectionMode'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Teilschutz',
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
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type' => 'CheckBox',
                            'name' => 'UseIndividualProtectionMode'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Individualschutz',
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
                ]
            ]
        ];

        //Alarm zones
        $alarmZoneValues = [];
        $alarmZones = json_decode($this->ReadPropertyString('AlarmZones'), true);
        $amountAlarmZones = count($alarmZones) + 1;
        if ($amountAlarmZones == 1) {
            $amountAlarmZones = 3;
        }
        foreach ($alarmZones as $alarmZone) {
            if (!$alarmZone['Use']) {
                continue;
            }
            $rowColor = '#FFC0C0'; //red
            $id = $alarmZone['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#C0FFC0'; //light green
            }
            $alarmZoneValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Glass breakage detector control switch
        $glassBreakageDetectorControlSwitchValues = [];
        $glassBreakageDetectorControlSwitches = json_decode($this->ReadPropertyString('GlassBreakageDetectorControl'), true);
        $amountGlassBreakageDetectorControl = count($glassBreakageDetectorControlSwitches) + 1;
        if ($amountGlassBreakageDetectorControl == 1) {
            $amountGlassBreakageDetectorControl = 3;
        }
        foreach ($glassBreakageDetectorControlSwitches as $glassBreakageDetectorControlSwitch) {
            if (!$glassBreakageDetectorControlSwitch['Use']) {
                continue;
            }
            $rowColor = '#FFC0C0'; //red
            $id = $glassBreakageDetectorControlSwitch['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#C0FFC0'; //light green
            }
            $glassBreakageDetectorControlSwitchValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Protection mode
        $protectionModeValues = [];
        $variables = json_decode($this->ReadPropertyString('ProtectionMode'), true);
        $amountProtectionMode = count($variables) + 1;
        if ($amountProtectionMode == 1) {
            $amountProtectionMode = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $protectionModeValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //System state
        $systemStateValues = [];
        $variables = json_decode($this->ReadPropertyString('SystemState'), true);
        $amountSystemState = count($variables) + 1;
        if ($amountSystemState == 1) {
            $amountSystemState = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $systemStateValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //System detailed state
        $systemDetailedStateValues = [];
        $variables = json_decode($this->ReadPropertyString('SystemDetailedState'), true);
        $amountDetailedSystemState = count($variables) + 1;
        if ($amountDetailedSystemState == 1) {
            $amountDetailedSystemState = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $systemDetailedStateValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Alarm state
        $alarmStateValues = [];
        $variables = json_decode($this->ReadPropertyString('AlarmState'), true);
        $amountAlarmState = count($variables) + 1;
        if ($amountAlarmState == 1) {
            $amountAlarmState = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alarmStateValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Alerting sensors
        $alertingSensorValues = [];
        $variables = json_decode($this->ReadPropertyString('AlertingSensor'), true);
        $amountAlertingSensors = count($variables) + 1;
        if ($amountAlertingSensors == 1) {
            $amountAlertingSensors = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alertingSensorValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Door and window states
        $doorWindowStateValues = [];
        $variables = json_decode($this->ReadPropertyString('DoorWindowState'), true);
        $amountDoorWindowSensors = count($variables) + 1;
        if ($amountDoorWindowSensors == 1) {
            $amountDoorWindowSensors = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $doorWindowStateValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Motion detector states
        $motionDetectorStateValues = [];
        $variables = json_decode($this->ReadPropertyString('MotionDetectorState'), true);
        $amountMotionDetectors = count($variables) + 1;
        if ($amountMotionDetectors == 1) {
            $amountMotionDetectors = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $motionDetectorStateValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Glass breakage detector states
        $glassBreakageDetectorStateValues = [];
        $glassBreakageDetectors = json_decode($this->ReadPropertyString('GlassBreakageDetectorState'), true);
        $amountGlassBreakageDetectors = count($glassBreakageDetectors) + 1;
        if ($amountGlassBreakageDetectors == 1) {
            $amountGlassBreakageDetectors = 3;
        }
        foreach ($glassBreakageDetectors as $glassBreakageDetector) {
            $rowColor = '#FFC0C0'; //red
            $id = $glassBreakageDetector['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($glassBreakageDetector['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $glassBreakageDetectorStateValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Smoke detector states
        $smokeDetectorStateValues = [];
        $smokeDetectors = json_decode($this->ReadPropertyString('SmokeDetectorState'), true);
        $amountSmokeDetectors = count($smokeDetectors) + 1;
        if ($amountSmokeDetectors == 1) {
            $amountSmokeDetectors = 3;
        }
        foreach ($smokeDetectors as $smokeDetector) {
            $rowColor = '#FFC0C0'; //red
            $id = $smokeDetector['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($smokeDetector['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $smokeDetectorStateValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Water detector states
        $waterDetectorStateValues = [];
        $waterDetectors = json_decode($this->ReadPropertyString('WaterDetectorState'), true);
        $amountWaterDetectors = count($waterDetectors) + 1;
        if ($amountWaterDetectors == 1) {
            $amountWaterDetectors = 3;
        }
        foreach ($waterDetectors as $waterDetector) {
            $rowColor = '#FFC0C0'; //red
            $id = $waterDetector['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($waterDetector['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $waterDetectorStateValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Alarm sirens
        $alarmSirenValues = [];
        $variables = json_decode($this->ReadPropertyString('AlarmSiren'), true);
        $amountAlarmSirens = count($variables) + 1;
        if ($amountAlarmSirens == 1) {
            $amountAlarmSirens = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alarmSirenValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Alarm lights
        $alarmLightValues = [];
        $variables = json_decode($this->ReadPropertyString('AlarmLight'), true);
        $amountAlarmLights = count($variables) + 1;
        if ($amountAlarmLights == 1) {
            $amountAlarmLights = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alarmLightValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Alarm calls
        $alarmCallValues = [];
        $variables = json_decode($this->ReadPropertyString('AlarmCall'), true);
        $amountAlarmCalls = count($variables) + 1;
        if ($amountAlarmCalls == 1) {
            $amountAlarmCalls = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $alarmCallValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        //Panic alarm
        $panicAlarmValues = [];
        $variables = json_decode($this->ReadPropertyString('PanicAlarm'), true);
        $amountPanicAlarms = count($variables) + 1;
        if ($amountPanicAlarms == 1) {
            $amountPanicAlarms = 3;
        }
        foreach ($variables as $variable) {
            $rowColor = '#FFC0C0'; //red
            $id = $variable['ID'];
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $rowColor = '#DFDFDF'; # grey
                if ($variable['Use']) {
                    $rowColor = '#C0FFC0'; //light green
                }
            }
            $panicAlarmValues[] = ['VariableID' => $id, 'rowColor' => $rowColor];
        }

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel4',
            'caption' => 'Alarmzonen',
            'items'   => [
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
                                    ],
                                    [
                                        'type'     => 'List',
                                        'name'     => 'DeterminedAlarmZoneList',
                                        'caption'  => 'Alarmzonen',
                                        'visible'  => false,
                                        'rowCount' => 5,
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
                                        'type'    => 'Button',
                                        'name'    => 'ApplyPreAlarmZoneTriggerValues',
                                        'caption' => 'Übernehmen',
                                        'visible' => false,
                                        'onClick' => self::MODULE_PREFIX . '_ApplyDeterminedAlarmZoneVariables($id, $DeterminedAlarmZoneList);'
                                    ],
                                    [
                                        'type'    => 'ProgressBar',
                                        'name'    => 'ApplyNewConfigurationProgress',
                                        'caption' => 'Fortschritt',
                                        'minimum' => 0,
                                        'maximum' => 100,
                                        'visible' => false
                                    ],
                                    [
                                        'type'    => 'Label',
                                        'name'    => 'ApplyNewConfigurationProgressInfo',
                                        'caption' => 'Konfiguration',
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
                ],
                [
                    'type'     => 'List',
                    'name'     => 'AlarmZones',
                    'caption'  => 'Alarmzonen',
                    'rowCount' => $amountAlarmZones,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmZoneConfigurationButton", "ID " . $AlarmZones["ID"] . " konfigurieren", $AlarmZones["ID"]);',
                        ],
                        [
                            'caption' => 'Alarmzone',
                            'name'    => 'ID',
                            'width'   => '450px',
                            'add'     => 0,
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
                    'name'     => 'AlertingSensor',
                    'caption'  => 'Auslösender Alarmmelder',
                    'rowCount' => $amountAlertingSensors,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlertingSensorConfigurationButton", "ID " . $AlertingSensor["ID"] . " bearbeiten", $AlertingSensor["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'name'     => 'ProtectionMode',
                    'caption'  => 'Modus',
                    'rowCount' => $amountProtectionMode,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "ProtectionModeConfigurationButton", "ID " . $ProtectionMode["ID"] . " bearbeiten", $ProtectionMode["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'name'     => 'GlassBreakageDetectorControl',
                    'caption'  => 'Glasbruchmelder',
                    'rowCount' => $amountGlassBreakageDetectorControl,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "GlassBreakageDetectorControlSwitchConfigurationButton", "ID " . $GlassBreakageDetectorControl["ID"] . " bearbeiten", $GlassBreakageDetectorControl["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $glassBreakageDetectorControlSwitchValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'GlassBreakageDetectorControlSwitchConfigurationButton',
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
                    'rowCount' => $amountSystemState,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "SystemStateConfigurationButton", "ID " . $SystemState["ID"] . " bearbeiten", $SystemState["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'rowCount' => $amountDetailedSystemState,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "SystemDetailedStateConfigurationButton", "ID " . $SystemDetailedState["ID"] . " bearbeiten", $SystemDetailedState["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'rowCount' => $amountAlarmState,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmStateConfigurationButton", "ID " . $AlarmState["ID"] . " bearbeiten", $AlarmState["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'name'     => 'DoorWindowState',
                    'caption'  => 'Tür- und Fensterstatus',
                    'rowCount' => $amountDoorWindowSensors,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "DoorWindowStateConfigurationButton", "ID " . $DoorWindowState["ID"] . " bearbeiten", $DoorWindowState["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'rowCount' => $amountMotionDetectors,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "MotionDetectorStateConfigurationButton", "ID " . $MotionDetectorState["ID"] . " bearbeiten", $MotionDetectorState["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'name'     => 'GlassBreakageDetectorState',
                    'caption'  => 'Glasbruchmelderstatus',
                    'rowCount' => $amountGlassBreakageDetectors,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "GlassBreakageDetectorStateConfigurationButton", "ID " . $GlassBreakageDetectorState["ID"] . " bearbeiten", $GlassBreakageDetectorState["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $glassBreakageDetectorStateValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'GlassBreakageDetectorStateConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'SmokeDetectorState',
                    'caption'  => 'Rauchmelderstatus',
                    'rowCount' => $amountSmokeDetectors,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "SmokeDetectorStateConfigurationButton", "ID " . $SmokeDetectorState["ID"] . " bearbeiten", $SmokeDetectorState["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $smokeDetectorStateValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'SmokeDetectorStateConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'WaterDetectorState',
                    'caption'  => 'Wassermelderstatus',
                    'rowCount' => $amountWaterDetectors,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "WaterDetectorStateConfigurationButton", "ID " . $WaterDetectorState["ID"] . " bearbeiten", $WaterDetectorState["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $waterDetectorStateValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'WaterDetectorStateConfigurationButton',
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
                    'rowCount' => $amountAlarmSirens,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmSirenConfigurationButton", "ID " . $AlarmSiren["ID"] . " bearbeiten", $AlarmSiren["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'rowCount' => $amountAlarmLights,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmLightConfigurationButton", "ID " . $AlarmLight["ID"] . " bearbeiten", $AlarmLight["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                    'rowCount' => $amountAlarmCalls,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmCallConfigurationButton", "ID " . $AlarmCall["ID"] . " bearbeiten", $AlarmCall["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
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
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'PanicAlarm',
                    'caption'  => 'Panikalarm',
                    'rowCount' => $amountPanicAlarms,
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
                            'caption' => 'ID',
                            'name'    => 'VariableID',
                            'width'   => '100px',
                            'add'     => 0,
                            'save'    => false,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "PanicAlarmConfigurationButton", "ID " . $PanicAlarm["ID"] . " bearbeiten", $PanicAlarm["ID"]);',
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Variable',
                            'width'   => '650px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'width'   => '400px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $panicAlarmValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'Bearbeiten',
                    'name'     => 'PanicAlarmConfigurationButton',
                    'visible'  => false,
                    'objectID' => 0
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
            'name'     => 'Panel5',
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

        $disarmedActionVisible = $this->ReadPropertyBoolean('UseDisarmedAction');
        $fullProtectionActionVisible = $this->ReadPropertyBoolean('UseFullProtectionAction');
        $hullProtectionActionVisible = $this->ReadPropertyBoolean('UseHullProtectionAction');
        $partialProtectionActionVisible = $this->ReadPropertyBoolean('UsePartialProtectionAction');
        $individualProtectionActionVisible = $this->ReadPropertyBoolean('UseIndividualProtectionAction');

        //Actions
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel6',
            'caption' => 'Aktionen',
            'items'   => [
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseDisarmedAction',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "DisarmedAction", $UseDisarmedAction);'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Unscharf',
                            'bold'    => true,
                            'italic'  => true
                        ]
                    ]
                ],
                [
                    'type'    => 'SelectAction',
                    'name'    => 'DisarmedAction',
                    'visible' => $disarmedActionVisible
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseFullProtectionAction',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "FullProtectionAction", $UseFullProtectionAction);'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Vollschutz',
                            'bold'    => true,
                            'italic'  => true
                        ]
                    ]
                ],
                [
                    'type'    => 'SelectAction',
                    'name'    => 'FullProtectionAction',
                    'visible' => $fullProtectionActionVisible
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseHullProtectionAction',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "HullProtectionAction", $UseHullProtectionAction);'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Hüllschutz',
                            'bold'    => true,
                            'italic'  => true
                        ]
                    ]
                ],
                [
                    'type'    => 'SelectAction',
                    'name'    => 'HullProtectionAction',
                    'visible' => $hullProtectionActionVisible
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UsePartialProtectionAction',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "PartialProtectionAction", $UsePartialProtectionAction);'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Teilschutz',
                            'bold'    => true,
                            'italic'  => true
                        ]
                    ]
                ],
                [
                    'type'    => 'SelectAction',
                    'name'    => 'PartialProtectionAction',
                    'visible' => $partialProtectionActionVisible
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'CheckBox',
                            'name'     => 'UseIndividualProtectionAction',
                            'onChange' => self::MODULE_PREFIX . '_HideAction($id, "IndividualProtectionAction", $UseIndividualProtectionAction);'
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => 'Individualschutz',
                            'bold'    => true,
                            'italic'  => true
                        ]
                    ]
                ],
                [
                    'type'    => 'SelectAction',
                    'name'    => 'IndividualProtectionAction',
                    'visible' => $individualProtectionActionVisible
                ]
            ]
        ];

        //Visualisation
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel7',
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
                    'name'    => 'EnableIndividualProtectionControlSwitch',
                    'caption' => 'Individualschutz'
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
                    'type'    => 'PopupButton',
                    'caption' => 'Variablen neu anordnen',
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