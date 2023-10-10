<?php

/**
 * @project       Alarmzone/Alarmzone
 * @file          AZ_MotionDetectors.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZ_MotionDetectors
{
    /**
     * Determines automatically the variables of all existing motion detectors.
     *
     * @param int $DeterminationType
     * @param string $DeterminationValue
     * @param string $ProfileSelection
     * @return void
     * @throws Exception
     */
    public function DetermineMotionDetectorVariables(int $DeterminationType, string $DeterminationValue, string $ProfileSelection = ''): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Auswahl: ' . $DeterminationType, 0);
        $this->SendDebug(__FUNCTION__, 'Identifikator: ' . $DeterminationValue, 0);

        $this->UpdateFormField('MotionDetectorProgress', 'minimum', 0);
        $maximumVariables = count(IPS_GetVariableList());
        $this->UpdateFormField('MotionDetectorProgress', 'maximum', $maximumVariables);

        $determineIdent = false;
        $determineProfile = false;

        //Determine variables first
        $determinedVariables = [];
        $passedVariables = 0;
        foreach (@IPS_GetVariableList() as $variable) {
            switch ($DeterminationType) {
                case 0: //Profile: select profile
                    if ($ProfileSelection == '') {
                        $infoText = 'Abbruch, es wurde kein Profil ausgewählt!';
                        $this->UpdateFormField('InfoMessage', 'visible', true);
                        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
                        return;
                    } else {
                        $determineProfile = true;
                    }
                    break;
                    
                case 1: //Profile: ~Motion
                case 2: //Profile: ~Motion.Reversed
                case 3: //Profile: ~Motion.HM
                    $determineProfile = true;
                    break;

                case 4: //Custom Profile
                    if ($DeterminationValue == '') {
                        $infoText = 'Abbruch, es wurde kein Profilname angegeben!';
                        $this->UpdateFormField('InfoMessage', 'visible', true);
                        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
                        return;
                    } else {
                        $determineProfile = true;
                    }
                    break;

                case 5: //Ident: MOTION
                    $determineIdent = true;
                    break;

                case 6: //Custom Ident
                    if ($DeterminationValue == '') {
                        $infoText = 'Abbruch, es wurde kein Identifikator angegeben!';
                        $this->UpdateFormField('InfoMessage', 'visible', true);
                        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
                        return;
                    } else {
                        $determineIdent = true;
                    }
                    break;

            }

            $passedVariables++;
            $this->UpdateFormField('MotionDetectorProgress', 'visible', true);
            $this->UpdateFormField('MotionDetectorProgress', 'current', $passedVariables);
            $this->UpdateFormField('MotionDetectorProgressInfo', 'visible', true);
            $this->UpdateFormField('MotionDetectorProgressInfo', 'caption', $passedVariables . '/' . $maximumVariables);
            IPS_Sleep(25);

            ##### Profile

            //Determine via profile
            if ($determineProfile && !$determineIdent) {
                switch ($DeterminationType) {
                    case 0: //Select profile
                        $profileNames = $ProfileSelection;
                        break;

                    case 1:
                        $profileNames = '~Motion';
                        break;

                    case 2:
                        $profileNames = '~Motion.Reversed';
                        break;

                    case 3:
                        $profileNames = '~Motion.HM';
                        break;

                    case 4: //Custom profile
                        $profileNames = $DeterminationValue;
                        break;

                }
                if (isset($profileNames)) {
                    $profileNames = str_replace(' ', '', $profileNames);
                    $profileNames = explode(',', $profileNames);
                    foreach ($profileNames as $profileName) {
                        $variableData = IPS_GetVariable($variable);
                        if ($variableData['VariableCustomProfile'] == $profileName || $variableData['VariableProfile'] == $profileName) {
                            $name = @IPS_GetName($variable);
                            $address = '';
                            $parent = @IPS_GetParent($variable);
                            if ($parent > 1 && @IPS_ObjectExists($parent)) {
                                $parentObject = @IPS_GetObject($parent);
                                if ($parentObject['ObjectType'] == 1) { //1 = instance
                                    $name = strstr(@IPS_GetName($parent), ':', true);
                                    if (!$name) {
                                        $name = @IPS_GetName($parent);
                                    }
                                    $address = @IPS_GetProperty($parent, 'Address');
                                    if (!$address) {
                                        $address = '';
                                    }
                                }
                            }
                            $value = true;
                            if (IPS_GetVariable($variable)['VariableType'] == 1) {
                                $value = 1;
                            }
                            $primaryCondition[0] = [
                                'id'        => 0,
                                'parentID'  => 0,
                                'operation' => 0,
                                'rules'     => [
                                    'variable' => [
                                        '0' => [
                                            'id'         => 0,
                                            'variableID' => $variable,
                                            'comparison' => 0,
                                            'value'      => $value,
                                            'type'       => 0
                                        ]
                                    ],
                                    'date'         => [],
                                    'time'         => [],
                                    'dayOfTheWeek' => []
                                ]
                            ];
                            $determinedVariables[] = [
                                'Use'                         => true,
                                'Designation'                 => $name,
                                'Comment'                     => $address,
                                'UseMultipleAlerts'           => false,
                                'PrimaryCondition'            => json_encode($primaryCondition),
                                'SecondaryCondition'          => '[]',
                                'FullProtectionModeActive'    => true,
                                'HullProtectionModeActive'    => false,
                                'PartialProtectionModeActive' => false,
                                'UseAlarmProtocol'            => true,
                                'UseNotification'             => true,
                                'UseAlarmSiren'               => true,
                                'UseAlarmLight'               => false,
                                'UseAlarmCall'                => false,
                                'UseAlertingAction'           => false,
                                'AlertingAction'              => '[]'];
                        }
                    }
                }
            }

            ##### Ident

            //Determine via ident
            if ($determineIdent && !$determineProfile) {
                switch ($DeterminationType) {
                    case 5:
                        $objectIdents = 'MOTION';
                        break;

                    case 6: //Custom ident
                        $objectIdents = $DeterminationValue;
                        break;

                }
                if (isset($objectIdents)) {
                    $objectIdents = str_replace(' ', '', $objectIdents);
                    $objectIdents = explode(',', $objectIdents);
                    foreach ($objectIdents as $objectIdent) {
                        $object = @IPS_GetObject($variable);
                        if ($object['ObjectIdent'] == $objectIdent) {
                            $name = @IPS_GetName($variable);
                            $address = '';
                            $parent = @IPS_GetParent($variable);
                            if ($parent > 1 && @IPS_ObjectExists($parent)) {
                                $parentObject = @IPS_GetObject($parent);
                                if ($parentObject['ObjectType'] == 1) { //1 = instance
                                    $name = strstr(@IPS_GetName($parent), ':', true);
                                    if (!$name) {
                                        $name = @IPS_GetName($parent);
                                    }
                                    $address = @IPS_GetProperty($parent, 'Address');
                                    if (!$address) {
                                        $address = '';
                                    }
                                }
                            }
                            $value = true;
                            if (IPS_GetVariable($variable)['VariableType'] == 1) {
                                $value = 1;
                            }
                            $primaryCondition[0] = [
                                'id'        => 0,
                                'parentID'  => 0,
                                'operation' => 0,
                                'rules'     => [
                                    'variable' => [
                                        '0' => [
                                            'id'         => 0,
                                            'variableID' => $variable,
                                            'comparison' => 0,
                                            'value'      => $value,
                                            'type'       => 0
                                        ]
                                    ],
                                    'date'         => [],
                                    'time'         => [],
                                    'dayOfTheWeek' => []
                                ]
                            ];
                            $determinedVariables[] = [
                                'Use'                         => true,
                                'Designation'                 => $name,
                                'Comment'                     => $address,
                                'UseMultipleAlerts'           => false,
                                'PrimaryCondition'            => json_encode($primaryCondition),
                                'SecondaryCondition'          => '[]',
                                'FullProtectionModeActive'    => true,
                                'HullProtectionModeActive'    => false,
                                'PartialProtectionModeActive' => false,
                                'UseAlarmProtocol'            => true,
                                'UseNotification'             => true,
                                'UseAlarmSiren'               => true,
                                'UseAlarmLight'               => false,
                                'UseAlarmCall'                => false,
                                'UseAlertingAction'           => false,
                                'AlertingAction'              => '[]'];
                        }
                    }
                }
            }
        }

        //Get already listed variables
        $listedVariables = json_decode($this->ReadPropertyString('MotionDetectors'), true);
        foreach ($determinedVariables as $determinedVariable) {
            if (array_key_exists('PrimaryCondition', $determinedVariable)) {
                $primaryCondition = json_decode($determinedVariable['PrimaryCondition'], true);
                if ($primaryCondition != '') {
                    if (array_key_exists(0, $primaryCondition)) {
                        if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                            $determinedVariableID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                            if ($determinedVariableID > 1 && @IPS_ObjectExists($determinedVariableID)) {
                                //Check variable id with already listed variable ids
                                $add = true;
                                foreach ($listedVariables as $listedVariable) {
                                    if (array_key_exists('PrimaryCondition', $listedVariable)) {
                                        $primaryCondition = json_decode($listedVariable['PrimaryCondition'], true);
                                        if ($primaryCondition != '') {
                                            if (array_key_exists(0, $primaryCondition)) {
                                                if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                                                    $listedVariableID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                                                    if ($listedVariableID > 1 && @IPS_ObjectExists($determinedVariableID)) {
                                                        if ($determinedVariableID == $listedVariableID) {
                                                            $add = false;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                //Add new variable to already listed variables
                                if ($add) {
                                    $listedVariables[] = $determinedVariable;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (empty($determinedVariables)) {
            $this->UpdateFormField('MotionDetectorProgress', 'visible', false);
            $this->UpdateFormField('MotionDetectorProgressInfo', 'visible', false);
            $infoText = 'Es wurden keinen Variablen gefunden!';
            $this->UpdateFormField('InfoMessage', 'visible', true);
            $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
            return;
        }
        //Sort variables by name
        array_multisort(array_column($listedVariables, 'Designation'), SORT_ASC, $listedVariables);
        @IPS_SetProperty($this->InstanceID, 'MotionDetectors', json_encode(array_values($listedVariables)));
        if (@IPS_HasChanges($this->InstanceID)) {
            @IPS_ApplyChanges($this->InstanceID);
        }
    }

    public function CheckMotionDetectorDeterminationValue(int $MotionDetectorDeterminationType): void
    {
        $profileSelection = false;
        $determinationValue = false;

        //Profile selection
        if ($MotionDetectorDeterminationType == 0) {
            $profileSelection = true;
        }

        //Custom profile
        if ($MotionDetectorDeterminationType == 4) {
            $this->UpdateFormfield('MotionDetectorDeterminationValue', 'caption', 'Profilname');
            $determinationValue = true;
        }
        //Custom ident
        if ($MotionDetectorDeterminationType == 6) {
            $this->UpdateFormfield('MotionDetectorDeterminationValue', 'caption', 'Identifikator');
            $determinationValue = true;
        }

        $this->UpdateFormfield('MotionDetectorDeterminationProfileSelection', 'visible', $profileSelection);
        $this->UpdateFormfield('MotionDetectorDeterminationValue', 'visible', $determinationValue);
    }

    public function AssignMotionDetectorVariableProfile(): void
    {
        //Only assign a standard profile, a reversed profile must be assigned manually by the user!
        $listedVariables = json_decode($this->ReadPropertyString('MotionDetectors'), true);
        $maximumVariables = count($listedVariables);
        $this->UpdateFormField('AssignMotionDetectorVariableProfileProgress', 'minimum', 0);
        $this->UpdateFormField('AssignMotionDetectorVariableProfileProgress', 'maximum', $maximumVariables);
        $passedVariables = 0;
        foreach ($listedVariables as $variable) {
            $passedVariables++;
            $this->UpdateFormField('AssignMotionDetectorVariableProfileProgress', 'visible', true);
            $this->UpdateFormField('AssignMotionDetectorVariableProfileProgress', 'current', $passedVariables);
            $this->UpdateFormField('AssignMotionDetectorVariableProfileProgressInfo', 'visible', true);
            $this->UpdateFormField('AssignMotionDetectorVariableProfileProgressInfo', 'caption', $passedVariables . '/' . $maximumVariables);
            IPS_Sleep(250);
            $id = 0;
            //Primary condition
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                    }
                }
            }
            if ($id > 1 && @IPS_ObjectExists($id)) {
                $object = IPS_GetObject($id)['ObjectType'];
                //0: Category, 1: Instance, 2: Variable, 3: Script, 4: Event, 5: Media, 6: Link)
                if ($object == 2) {
                    $variable = IPS_GetVariable($id)['VariableType'];
                    switch ($variable) {
                        //0: Boolean, 1: Integer, 2: Float, 3: String
                        case 0:
                            $profileName = 'MotionDetector.Bool';
                            break;

                        case 1:
                            $profileName = 'MotionDetector.Integer';
                            break;

                        default:
                            $profileName = '';
                    }
                    if (!empty($profileName)) {
                        //Assign profile
                        IPS_SetVariableCustomProfile($id, $profileName);
                        //Deactivate standard action
                        IPS_SetVariableCustomAction($id, 1);
                    }
                }
            }
        }
        if ($maximumVariables == 0) {
            $infoText = 'Es sind keine Variablen vorhanden!';
        } else {
            $this->UpdateFormField('AssignMotionDetectorVariableProfileProgress', 'visible', false);
            $this->UpdateFormField('AssignMotionDetectorVariableProfileProgressInfo', 'visible', false);
            $infoText = 'Variablenprofil wurde erfolgreich zugewiesen!';
        }
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
    }

    /**
     * Checks the alerting of a motion detector.
     *
     * @param int $SenderID
     *
     * @param bool $ValueChanged
     * false =  same value,
     * true =   new value
     *
     * @return bool
     * false =  no alert,
     * true =   alert
     *
     * @throws Exception
     */
    public function CheckMotionDetectorAlerting(int $SenderID, bool $ValueChanged): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        $valueChangedText = 'nicht ';
        if ($ValueChanged) {
            $valueChangedText = '';
        }
        $this->SendDebug(__FUNCTION__, 'Der Wert hat sich ' . $valueChangedText . 'geändert', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $this->CheckMotionDetectorState();
        $variables = json_decode($this->ReadPropertyString('MotionDetectors'), true);
        if (empty($variables)) {
            return false;
        }
        $result = false;
        foreach ($variables as $variable) {
            if (array_key_exists('PrimaryCondition', $variable)) {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if ($primaryCondition != '') {
                    if (array_key_exists(0, $primaryCondition)) {
                        if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                            $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                            if ($id == $SenderID) {
                                if (!$variable['Use']) {
                                    $this->SendDebug(__FUNCTION__, 'Abbruch, der Bewegungsmelder ist nicht aktiviert!', 0);
                                    return false;
                                }
                                if (!$variable['UseMultipleAlerts'] && !$ValueChanged) {
                                    $this->SendDebug(__FUNCTION__, 'Abbruch, die Mehrfachauslösung ist nicht aktiviert!', 0);
                                    return false;
                                }
                                if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                                    $this->SendDebug(__FUNCTION__, 'Die Bedingungen wurden erfüllt. Es wurde eine Bewegung erkannt!', 0);
                                    $alerting = false;
                                    switch ($this->GetValue('AlarmZoneDetailedState')) {
                                        case 1: //armed
                                        case 3: //partial armed
                                            $mode = $this->GetValue('Mode');
                                            switch ($mode) {
                                                //Check if sensor is activated for full protection mode
                                                case 1:
                                                    if ($variable['FullProtectionModeActive']) {
                                                        $alerting = true;
                                                    }
                                                    break;

                                                //Check if sensor is activated for hull protection mode
                                                case 2:
                                                    if ($variable['HullProtectionModeActive']) {
                                                        $alerting = true;
                                                    }
                                                    break;

                                                //Check if sensor is activated for partial protection mode
                                                case 3:
                                                    if ($variable['PartialProtectionModeActive']) {
                                                        $alerting = true;
                                                    }
                                                    break;
                                            }
                                            //Alerting
                                            if ($alerting) {
                                                $result = true;
                                                //Alarm state
                                                $this->SetValue('AlarmState', 1);
                                                $this->SetValue('AlertingSensor', $variable['Designation']);
                                                //Alarm protocol
                                                if ($variable['UseAlarmProtocol']) {
                                                    $text = $variable['Designation'] . ' hat eine Bewegung erkannt und einen Alarm ausgelöst. (ID ' . $SenderID . ')';
                                                    $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $text;
                                                    $this->UpdateAlarmProtocol($logText, 2);
                                                }
                                                //Notification
                                                if ($variable['UseNotification']) {
                                                    $this->SendNotification('MotionDetectorAlarmNotification', (string) $variable['Designation']);
                                                }
                                                //Options
                                                if ($variable['UseAlarmSiren']) {
                                                    $this->SetValue('AlarmSiren', true);
                                                }
                                                if ($variable['UseAlarmLight']) {
                                                    $this->SetValue('AlarmLight', true);
                                                }
                                                if ($variable['UseAlarmCall']) {
                                                    $this->SetValue('AlarmCall', true);
                                                }
                                                if ($variable['UseAlertingAction']) {
                                                    $action = json_decode($variable['AlertingAction'], true);
                                                    @IPS_RunAction($action['actionID'], $action['parameters']);
                                                }
                                            }
                                            break;

                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    private function CreateMotionDetectorVariableProfiles(): void
    {
        //Bool variable
        $profile = 'MotionDetector.Bool';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Untätig', 'Information', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Bewegung erkannt', 'Motion', 0xFF0000);

        //Bool variable reversed
        $profile = 'MotionDetector.Bool.Reversed';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Bewegung erkannt', 'Motion', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, 'Untätig', 'Information', 0x00FF00);

        //Integer variable
        $profile = 'MotionDetector.Integer';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Untätig', 'Information', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Bewegung erkannt', 'Motion', 0xFF0000);

        //Integer variable reversed
        $profile = 'MotionDetector.Integer.Reversed';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Bewegung erkannt', 'Motion', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, 'Untätig', 'Information', 0x00FF00);
    }

    #################### Private

    /**
     * Checks the state of all activated motion detectors.
     *
     * @return bool
     * false =  no motion,
     * true =   motion detected
     *
     * @throws Exception
     */
    private function CheckMotionDetectorState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $state = false;
        $variables = json_decode($this->ReadPropertyString('MotionDetectors'), true);
        foreach ($variables as $variable) {
            if (!$variable['Use']) {
                continue;
            }
            //Check conditions
            if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                $state = true;
            }
        }
        $this->SetValue('MotionDetectorState', $state);
        return $state;
    }
}