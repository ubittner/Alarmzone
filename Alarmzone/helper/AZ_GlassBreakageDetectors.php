<?php

/**
 * @project       Alarmzone/Alarmzone/helper/
 * @file          AZ_GlassBreakageDetectors.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZ_GlassBreakageDetectors
{
    /**
     *  Checks the determination value.
     *
     * @param int $GlassBreakageDetectorDeterminationType
     * @return void
     */
    public function CheckGlassBreakageDetectorDeterminationValue(int $GlassBreakageDetectorDeterminationType): void
    {
        $profileSelection = false;
        $determinationValue = false;
        //Profile selection
        if ($GlassBreakageDetectorDeterminationType == 0) {
            $profileSelection = true;
        }
        //Custom ident
        if ($GlassBreakageDetectorDeterminationType == 2) {
            $this->UpdateFormfield('GlassBreakageDetectorDeterminationValue', 'caption', 'Identifikator');
            $determinationValue = true;
        }
        $this->UpdateFormfield('GlassBreakageDetectorDeterminationProfileSelection', 'visible', $profileSelection);
        $this->UpdateFormfield('GlassBreakageDetectorDeterminationValue', 'visible', $determinationValue);
    }

    /**
     * Determines the glass breakage detector variables.
     *
     * @param int $DeterminationType
     * @param string $DeterminationValue
     * @param string $ProfileSelection
     * @return void
     * @throws Exception
     */
    public function DetermineGlassBreakageDetectorVariables(int $DeterminationType, string $DeterminationValue, string $ProfileSelection = ''): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Auswahl: ' . $DeterminationType, 0);
        $this->SendDebug(__FUNCTION__, 'Identifikator: ' . $DeterminationValue, 0);
        //Set minimum an d maximum of existing variables
        $this->UpdateFormField('GlassBreakageDetectorDeterminationProgress', 'minimum', 0);
        $maximumVariables = count(IPS_GetVariableList());
        $this->UpdateFormField('GlassBreakageDetectorDeterminationProgress', 'maximum', $maximumVariables);
        //Determine variables first
        $determineIdent = false;
        $determineProfile = false;
        $determinedVariables = [];
        $passedVariables = 0;
        foreach (@IPS_GetVariableList() as $variable) {
            switch ($DeterminationType) {
                case 0: //Profile: Select profile
                    if ($ProfileSelection == '') {
                        $infoText = 'Abbruch, es wurde kein Profil ausgewählt!';
                        $this->UpdateFormField('InfoMessage', 'visible', true);
                        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
                        return;
                    } else {
                        $determineProfile = true;
                    }
                    break;

                case 1: //Ident: STATE
                    $determineIdent = true;
                    break;

                case 2: //Custom Ident
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
            $this->UpdateFormField('GlassBreakageDetectorDeterminationProgress', 'visible', true);
            $this->UpdateFormField('GlassBreakageDetectorDeterminationProgress', 'current', $passedVariables);
            $this->UpdateFormField('GlassBreakageDetectorDeterminationProgressInfo', 'visible', true);
            $this->UpdateFormField('GlassBreakageDetectorDeterminationProgressInfo', 'caption', $passedVariables . '/' . $maximumVariables);
            IPS_Sleep(10);

            ##### Profile

            //Determine via profile
            if ($determineProfile && !$determineIdent) {
                //Select profile
                if ($DeterminationType == 0) {
                    $profileNames = $ProfileSelection;
                }
                if (isset($profileNames)) {
                    $profileNames = str_replace(' ', '', $profileNames);
                    $profileNames = explode(',', $profileNames);
                    foreach ($profileNames as $profileName) {
                        $variableData = IPS_GetVariable($variable);
                        if ($variableData['VariableCustomProfile'] == $profileName || $variableData['VariableProfile'] == $profileName) {
                            $location = @IPS_GetLocation($variable);
                            $determinedVariables[] = [
                                'Use'      => false,
                                'ID'       => $variable,
                                'Location' => $location];
                        }
                    }
                }
            }

            ##### Ident

            //Determine via ident
            if ($determineIdent && !$determineProfile) {
                switch ($DeterminationType) {
                    case 1: //State
                        $objectIdents = 'STATE';
                        break;

                    case 2: //Custom ident
                        $objectIdents = $DeterminationValue;
                        break;

                }
                if (isset($objectIdents)) {
                    $objectIdents = str_replace(' ', '', $objectIdents);
                    $objectIdents = explode(',', $objectIdents);
                    foreach ($objectIdents as $objectIdent) {
                        $object = @IPS_GetObject($variable);
                        if ($object['ObjectIdent'] == $objectIdent) {
                            $location = @IPS_GetLocation($variable);
                            $determinedVariables[] = [
                                'Use'      => false,
                                'ID'       => $variable,
                                'Location' => $location];
                        }
                    }
                }
            }
        }
        $amount = count($determinedVariables);
        //Get already listed variables
        $listedVariables = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
        foreach ($listedVariables as $listedVariable) {
            if (array_key_exists('PrimaryCondition', $listedVariable)) {
                $primaryCondition = json_decode($listedVariable['PrimaryCondition'], true);
                if ($primaryCondition != '') {
                    if (array_key_exists(0, $primaryCondition)) {
                        if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                            $listedVariableID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                            if ($listedVariableID > 1 && @IPS_ObjectExists($listedVariableID)) {
                                foreach ($determinedVariables as $key => $determinedVariable) {
                                    $determinedVariableID = $determinedVariable['ID'];
                                    if ($determinedVariableID > 1 && @IPS_ObjectExists($determinedVariableID)) {
                                        //Check if variable id is already a listed variable id
                                        if ($determinedVariableID == $listedVariableID) {
                                            unset($determinedVariables[$key]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (empty($determinedVariables)) {
            $this->UpdateFormField('GlassBreakageDetectorDeterminationProgress', 'visible', false);
            $this->UpdateFormField('GlassBreakageDetectorDeterminationProgressInfo', 'visible', false);
            if ($amount > 0) {
                $infoText = 'Es wurden keine weiteren Variablen gefunden!';
            } else {
                $infoText = 'Es wurden keine Variablen gefunden!';
            }
            $this->UpdateFormField('InfoMessage', 'visible', true);
            $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
            return;
        }
        $determinedVariables = array_values($determinedVariables);
        $this->UpdateFormField('DeterminedGlassBreakageDetectorVariableList', 'visible', true);
        $this->UpdateFormField('DeterminedGlassBreakageDetectorVariableList', 'rowCount', count($determinedVariables));
        $this->UpdateFormField('DeterminedGlassBreakageDetectorVariableList', 'values', json_encode($determinedVariables));
        $this->UpdateFormField('OverwriteGlassBreakageDetectorVariableProfiles', 'visible', true);
        $this->UpdateFormField('ApplyPreGlassBreakageDetectorTriggerValues', 'visible', true);
    }

    /**
     * Applies the determined glass breakage detector variables to the glass breakage detector list.
     *
     * @param object $ListValues
     * @param bool $OverwriteVariableProfiles
     * false =  don't overwrite
     * true =   overwrite
     *
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function ApplyDeterminedGlassBreakageDetectorVariables(object $ListValues, bool $OverwriteVariableProfiles): void
    {
        $determinedVariables = [];
        $reflection = new ReflectionObject($ListValues);
        $property = $reflection->getProperty('array');
        $property->setAccessible(true);
        $variables = $property->getValue($ListValues);
        foreach ($variables as $variable) {
            if (!$variable['Use']) {
                continue;
            }
            $id = $variable['ID'];
            //Overwrite variable profiles
            if ($OverwriteVariableProfiles) {
                $variableType = @IPS_GetVariable($id)['VariableType'];
                $profileName = '';
                switch ($variableType) {
                    case 0: //Boolean
                        $profileName = 'GlassBreakageDetector.Bool';
                        break;

                    case 1: //Integer
                        $profileName = 'GlassBreakageDetector.Integer';
                        break;

                }
                if ($profileName != '') {
                    @IPS_SetVariableCustomProfile($id, $profileName);
                }
            }
            $name = @IPS_GetName($id);
            $address = '';
            $parent = @IPS_GetParent($id);
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
            if (IPS_GetVariable($id)['VariableType'] == 1) {
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
                            'variableID' => $id,
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
                'Use'                                              => true,
                'Designation'                                      => $name,
                'Comment'                                          => $address,
                'UseMultipleAlerts'                                => false,
                'PrimaryCondition'                                 => json_encode($primaryCondition),
                'SecondaryCondition'                               => '[]',
                'PermanentMonitoring'                              => true,
                'FullProtectionModeActive'                         => false,
                'HullProtectionModeActive'                         => false,
                'PartialProtectionModeActive'                      => false,
                'OpenGlassBreakageDetectorStatusVerificationDelay' => 0,
                'UseNotification'                                  => true,
                'UseAlarmSiren'                                    => true,
                'UseAlarmLight'                                    => false,
                'UseAlarmCall'                                     => false,
                'UseAlertingAction'                                => false,
                'AlertingAction'                                   => '[]',
                'UseAlarmProtocol'                                 => true];
        }
        //Get already listed variables
        $listedVariables = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
        foreach ($determinedVariables as $determinedVariable) {
            $determinedVariableID = 0;
            if (array_key_exists('PrimaryCondition', $determinedVariable)) {
                $primaryCondition = json_decode($determinedVariable['PrimaryCondition'], true);
                if ($primaryCondition != '') {
                    if (array_key_exists(0, $primaryCondition)) {
                        if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                            $determinedVariableID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        }
                    }
                }
            }
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
        if (empty($determinedVariables)) {
            return;
        }
        //Sort variables by name
        array_multisort(array_column($listedVariables, 'Designation'), SORT_ASC, $listedVariables);
        @IPS_SetProperty($this->InstanceID, 'GlassBreakageDetectors', json_encode(array_values($listedVariables)));
        if (@IPS_HasChanges($this->InstanceID)) {
            @IPS_ApplyChanges($this->InstanceID);
        }
    }

    /**
     * Gets the actual glass breakage detector states.
     *
     * @return void
     * @throws Exception
     */
    public function GetActualGlassBreakageDetectorStates(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->UpdateFormField('ActualGlassBreakageDetectorStateConfigurationButton', 'visible', false);
        $actualVariableStates = [];
        $variables = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
        foreach ($variables as $variable) {
            if (!$variable['Use']) {
                continue;
            }
            $conditions = true;
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $sensorID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($sensorID <= 1 || @!IPS_ObjectExists($sensorID)) {
                            $conditions = false;
                        }
                    }
                }
            }
            if ($variable['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($variable['SecondaryCondition'], true);
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
            if ($conditions && isset($sensorID)) {
                $stateName = '🟢 OK';
                if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                    $stateName = '🔴 Glasbruch erkannt';
                }
                $variableUpdate = IPS_GetVariable($sensorID)['VariableUpdated']; //timestamp or 0 = never
                $lastUpdate = 'Nie';
                if ($variableUpdate != 0) {
                    $lastUpdate = date('d.m.Y H:i:s', $variableUpdate);
                }
                $actualVariableStates[] = ['ActualStatus' => $stateName, 'SensorID' => $sensorID, 'Designation' =>  $variable['Designation'], 'Comment' => $variable['Comment'], 'LastUpdate' => $lastUpdate];
            }
        }
        $amount = count($actualVariableStates);
        if ($amount == 0) {
            $amount = 1;
        }
        $this->UpdateFormField('ActualGlassBreakageDetectorStateList', 'visible', true);
        $this->UpdateFormField('ActualGlassBreakageDetectorStateList', 'rowCount', $amount);
        $this->UpdateFormField('ActualGlassBreakageDetectorStateList', 'values', json_encode($actualVariableStates));
    }

    /**
     * Assigns variable profiles to the glass breakage detector variables.
     *
     * @return void
     * @throws Exception
     */
    public function AssignGlassBreakageDetectorVariableProfile(): void
    {
        //Only assign a standard profile, a reversed profile must be assigned manually by the user!
        $listedVariables = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
        $maximumVariables = count($listedVariables);
        $this->UpdateFormField('AssignGlassBreakageDetectorVariableProfileProgress', 'minimum', 0);
        $this->UpdateFormField('AssignGlassBreakageDetectorVariableProfileProgress', 'maximum', $maximumVariables);
        $passedVariables = 0;
        foreach ($listedVariables as $variable) {
            $passedVariables++;
            $this->UpdateFormField('AssignGlassBreakageDetectorVariableProfileProgress', 'visible', true);
            $this->UpdateFormField('AssignGlassBreakageDetectorVariableProfileProgress', 'current', $passedVariables);
            $this->UpdateFormField('AssignGlassBreakageDetectorVariableProfileProgressInfo', 'visible', true);
            $this->UpdateFormField('AssignGlassBreakageDetectorVariableProfileProgressInfo', 'caption', $passedVariables . '/' . $maximumVariables);
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
                //0: Category, 1: Instance, 2: Variable, 3: Script, 4: Event, 5: Media, 6: Link
                if ($object == 2) {
                    $variable = IPS_GetVariable($id)['VariableType'];
                    switch ($variable) {
                        //0: Boolean, 1: Integer, 2: Float, 3: String
                        case 0:
                            $profileName = 'GlassBreakageDetector.Bool';
                            break;

                        case 1:
                            $profileName = 'GlassBreakageDetector.Integer';
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
            $this->UpdateFormField('AssignGlassBreakageDetectorVariableProfileProgress', 'visible', false);
            $this->UpdateFormField('AssignGlassBreakageDetectorVariableProfileProgressInfo', 'visible', false);
            $infoText = 'Variablenprofil wurde erfolgreich zugewiesen!';
        }
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
    }

    /**
     * Checks the alerting of a glass breakage detector.
     *
     * @param int $SenderID
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
    public function CheckGlassBreakageDetectorAlerting(int $SenderID, bool $ValueChanged): bool
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
        $variables = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
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
                                    $this->SendDebug(__FUNCTION__, 'Abbruch, der Glasbruchmelder ist nicht aktiviert!', 0);
                                    return false;
                                }
                                if (!$variable['UseMultipleAlerts'] && !$ValueChanged) {
                                    $this->SendDebug(__FUNCTION__, 'Abbruch, die Mehrfachauslösung ist nicht aktiviert!', 0);
                                    return false;
                                }
                                $open = false;
                                $alerting = false;
                                if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                                    $this->SendDebug(__FUNCTION__, 'Die Bedingungen wurden erfüllt. Der Glasbruchmelder hat einen Glasbruch erkannt!', 0);
                                    $open = true;
                                }
                                if ($variable['PermanentMonitoring']) {
                                    if ($open) {
                                        $alerting = true;
                                    }
                                } else {
                                    $mode = $this->GetValue('Mode');
                                    switch ($this->GetValue('AlarmZoneDetailedState')) {
                                        case 0: //disarmed
                                            $this->SendDebug(__FUNCTION__, 'Alarmzone ist unscharf! Glasbruchmelder ohne Auslösefunktion!', 0);
                                            break;

                                        case 1: //armed
                                        case 3: //partial armed
                                                if ($open) {
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
                                                }

                                            break;

                                        case 2: //delayed armed
                                        case 4: //delayed partial armed
                                            $this->SendDebug(__FUNCTION__, 'Alarmzone ist verzögert scharf! Glasbruchmelder ohne Auslösefunktion!', 0);
                                        break;

                                    }
                                }
                                if ($alerting) {
                                    $timeStamp = time();
                                    //Status verification
                                    if ($variable['OpenGlassBreakageDetectorStatusVerificationDelay'] > 0) {
                                        $this->SendDebug(__FUNCTION__, 'Status wird in ' . $variable['OpenGlassBreakageDetectorStatusVerificationDelay'] . ' Millisekunden erneut geprüft!', 0);
                                        $scriptText = self::MODULE_PREFIX . '_VerifyOpenGlassBreakageDetectorStatus(' . $this->InstanceID . ', ' . $SenderID . ', ' . $variable['OpenGlassBreakageDetectorStatusVerificationDelay'] . ');';
                                        @IPS_RunScriptText($scriptText);
                                        return false;
                                    }
                                    $result = true;
                                    //Alarm state
                                    $this->SetValue('AlarmSwitch', true);
                                    $this->SetValue('AlarmState', 1);
                                    $this->SetValue('AlertingSensor', $variable['Designation']);
                                    //Protocol
                                    if ($variable['UseAlarmProtocol']) {
                                        $text = ' hat einen Glasbruch erkannt und einen Alarm ausgelöst. (ID ' . $SenderID . ')';
                                        $logText = date('d.m.Y, H:i:s', $timeStamp) . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $variable['Designation'] . $text;
                                        $this->UpdateAlarmProtocol($logText, 2);
                                    }
                                    //Notification
                                    if ($variable['UseNotification']) {
                                        $this->SendNotification('GlassBreakageDetectorAlarmNotification', (string) $variable['Designation']);
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
                            }
                        }
                    }
                }
            }
        }
        $this->CheckGlassBreakageDetectorState();
        return $result;
    }

    /**
     * Verifies the glass breakage detector alerting again after a delay.
     *
     * @param int $SenderID
     * @param int $Delay
     * @return void
     * @throws Exception
     */
    public function VerifyOpenGlassBreakageDetectorStatus(int $SenderID, int $Delay): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        $this->SendDebug(__FUNCTION__, 'Verzögerung: ' . $Delay, 0);
        $sensors = json_decode($this->ReadAttributeString('VerificationGlassBreakageDetectors'));
        //Add new sender id
        $sensors[] = $SenderID;
        $this->WriteAttributeString('VerificationGlassBreakageDetectors', json_encode($sensors));
        IPS_Sleep($Delay);
        $variables = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
        foreach ($variables as $variable) {
            if (array_key_exists('PrimaryCondition', $variable)) {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if ($primaryCondition != '') {
                    if (array_key_exists(0, $primaryCondition)) {
                        if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                            $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                            if ($id == $SenderID) {
                                if (!$variable['Use']) {
                                    $this->RemoveGlassBreakageDetectorVerification($SenderID);
                                    return;
                                }
                                $open = false;
                                $alerting = false;
                                if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                                    $this->SendDebug(__FUNCTION__, 'Der Status wurde erneut geprüft. Der Glasbruchmelder hat einen Glasbruch erkannt!', 0);
                                    $open = true;
                                }
                                if ($variable['PermanentMonitoring']) {
                                    if ($open) {
                                        $alerting = true;
                                    }
                                } else {
                                    $mode = $this->GetValue('Mode');
                                    switch ($this->GetValue('AlarmZoneDetailedState')) {
                                        case 1: //armed
                                        case 3: //partial armed
                                            if ($open) {
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
                                            }
                                            break;

                                    }
                                }
                                if ($alerting) { //open is verified
                                    //Alarm state
                                    $this->SetValue('AlarmSwitch', true);
                                    $this->SetValue('AlarmState', 1);
                                    $this->SetValue('AlertingSensor', $variable['Designation']);
                                    //Protocol
                                    if ($variable['UseAlarmProtocol']) {
                                        $TimeStamp = time();
                                        $text = ' hat einen Glasbruch erkannt und einen Alarm ausgelöst. (ID ' . $SenderID . ')';
                                        $logText = date('d.m.Y, H:i:s', $TimeStamp) . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $variable['Designation'] . $text;
                                        $this->UpdateAlarmProtocol($logText, 2);
                                    }
                                    //Notification
                                    if ($variable['UseNotification']) {
                                        $this->SendNotification('GlassBreakageDetectorAlarmNotification', (string) $variable['Designation']);
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
                            }
                        }
                    }
                }
            }
        }
        $this->RemoveGlassBreakageDetectorVerification($SenderID);
        $this->CheckGlassBreakageDetectorState();
    }

    /**
     * Configures the verification delay for the glass breakage detectors.
     *
     * @param int $Delay
     * @return void
     * @throws Exception
     */
    public function ConfigureGlassBreakageDetectorVerificationDelay(int $Delay): void
    {
        if ($Delay > 10000) { //Delay must be equal or lower than 10 seconds
            return;
        }
        $listedVariables = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
        $maximumVariables = count($listedVariables);
        $this->UpdateFormField('GlassBreakageDetectorVerificationDelayProgress', 'minimum', 0);
        $this->UpdateFormField('GlassBreakageDetectorVerificationDelayProgress', 'maximum', $maximumVariables);
        $passedVariables = 0;
        foreach ($listedVariables as $key => $variable) {
            $passedVariables++;
            $this->UpdateFormField('GlassBreakageDetectorVerificationDelayProgress', 'visible', true);
            $this->UpdateFormField('GlassBreakageDetectorVerificationDelayProgress', 'current', $passedVariables);
            $this->UpdateFormField('GlassBreakageDetectorVerificationDelayProgressInfo', 'visible', true);
            $this->UpdateFormField('GlassBreakageDetectorVerificationDelayProgressInfo', 'caption', $passedVariables . '/' . $maximumVariables);
            IPS_Sleep(200);
            $listedVariables[$key]['OpenGlassBreakageDetectorStatusVerificationDelay'] = $Delay;
        }
        @IPS_SetProperty($this->InstanceID, 'GlassBreakageDetectors', json_encode(array_values($listedVariables)));
        if (@IPS_HasChanges($this->InstanceID)) {
            @IPS_ApplyChanges($this->InstanceID);
        }
    }

    #################### Private

    /**
     * Creates profiles for the glass breakage detectors.
     *
     * @return void
     */
    private function CreateGlassBreakageDetectorVariableProfiles(): void
    {
        //Bool variable
        $profile = 'GlassBreakageDetector.Bool';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Window');
        IPS_SetVariableProfileAssociation($profile, 0, 'Geschlossen', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Geöffnet', '', 0xFF0000);

        //Bool variable reversed
        $profile = 'GlassBreakageDetector.Bool.Reversed';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Window');
        IPS_SetVariableProfileAssociation($profile, 0, 'Geöffnet', '', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, 'Geschlossen', '', 0x00FF00);

        //Integer variable
        $profile = 'GlassBreakageDetector.Integer';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Window');
        IPS_SetVariableProfileAssociation($profile, 0, 'Geschlossen', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Geöffnet', '', 0xFF0000);

        //Integer variable reversed
        $profile = 'GlassBreakageDetector.Integer.Reversed';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Window');
        IPS_SetVariableProfileAssociation($profile, 0, 'Geöffnet', '', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, 'Geschlossen', '', 0x00FF00);
    }

    /**
     * Removes a sensor from the glass breakage detector verification list.
     *
     * @param int $SensorID
     * @return void
     * @throws Exception
     */
    private function RemoveGlassBreakageDetectorVerification(int $SensorID): void
    {
        $sensors = json_decode($this->ReadAttributeString('VerificationGlassBreakageDetectors'), true);
        foreach ($sensors as $key => $sensor) {
            if ($sensor == $SensorID) {
                unset($sensors[$key]);
            }
        }
        $sensors = array_values($sensors);
        $this->WriteAttributeString('VerificationGlassBreakageDetectors', json_encode($sensors));
    }

    /**
     * Checks the state of all activated glass breakage detectors.
     *
     * @return bool
     * false =  ok,
     * true =   glass breakage alarm
     *
     * @throws Exception
     */
    private function CheckGlassBreakageDetectorState(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $state = false;
        $variables = json_decode($this->ReadPropertyString('GlassBreakageDetectors'), true);
        foreach ($variables as $variable) {
            if (!$variable['Use']) {
                continue;
            }
            //Check conditions
            if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                $state = true;
            }
        }
        $this->SetValue('GlassBreakageDetectorState', $state);
        return $state;
    }
}