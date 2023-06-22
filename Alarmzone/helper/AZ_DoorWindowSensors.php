<?php

/**
 * @project       Alarmzone/Alarmzone
 * @file          AZ_DoorWindowSensors.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZ_DoorWindowSensors
{
    /**
     * Determines automatically the variables of all existing door and window sensors.
     *
     * @param string $SelectIdents
     * @param string $ObjectIdents
     * @return void
     * @throws Exception
     */
    public function DetermineDoorWindowVariables(string $SelectIdents, string $ObjectIdents): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Auswahl: ' . $SelectIdents, 0);
        $this->SendDebug(__FUNCTION__, 'Identifikator: ' . $ObjectIdents, 0);
        $this->UpdateFormField('DoorWindowSensorProgress', 'minimum', 0);
        $maximumVariables = count(IPS_GetVariableList());
        $this->UpdateFormField('DoorWindowSensorProgress', 'maximum', $maximumVariables);
        //Determine variables first
        $determinedVariables = [];
        $passedVariables = 0;
        foreach (@IPS_GetVariableList() as $variable) {
            if ($SelectIdents == '') {
                if ($ObjectIdents == '') {
                    $infoText = 'Abbruch, es wurde kein Identifikator angegeben!';
                    $this->UpdateFormField('InfoMessage', 'visible', true);
                    $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
                    return;
                }
            } else {
                $ObjectIdents = $SelectIdents;
            }
            $passedVariables++;
            $this->UpdateFormField('DoorWindowSensorProgress', 'visible', true);
            $this->UpdateFormField('DoorWindowSensorProgress', 'current', $passedVariables);
            $this->UpdateFormField('DoorWindowSensorProgressInfo', 'visible', true);
            $this->UpdateFormField('DoorWindowSensorProgressInfo', 'caption', $passedVariables . '/' . $maximumVariables);
            IPS_Sleep(25);
            $objectIdents = str_replace(' ', '', $ObjectIdents);
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
                        'Use'                                   => true,
                        'Designation'                           => $name,
                        'Comment'                               => $address,
                        'UseMultipleAlerts'                     => false,
                        'PrimaryCondition'                      => json_encode($primaryCondition),
                        'SecondaryCondition'                    => '[]',
                        'FullProtectionModeActive'              => true,
                        'HullProtectionModeActive'              => false,
                        'PartialProtectionModeActive'           => false,
                        'CheckFullProtectionActivation'         => false,
                        'CheckHullProtectionActivation'         => false,
                        'CheckPartialProtectionActivation'      => false,
                        'UseAlarmProtocol'                      => true,
                        'OpenDoorWindowStatusVerificationDelay' => 0,
                        'UseNotification'                       => true,
                        'UseAlarmSiren'                         => true,
                        'UseAlarmLight'                         => false,
                        'UseAlarmCall'                          => false,
                        'UseAlertingAction'                     => false,
                        'AlertingAction'                        => '[]'];
                }
            }
        }
        //Get already listed variables
        $listedVariables = json_decode($this->ReadPropertyString('DoorWindowSensors'), true);
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
        //Sort variables by name
        array_multisort(array_column($listedVariables, 'Designation'), SORT_ASC, $listedVariables);
        @IPS_SetProperty($this->InstanceID, 'DoorWindowSensors', json_encode(array_values($listedVariables)));
        if (@IPS_HasChanges($this->InstanceID)) {
            $this->UpdateFormField('ProgressAlert', 'visible', false);
            @IPS_ApplyChanges($this->InstanceID);
        }
        if (empty($determinedVariables)) {
            $infoText = 'Es wurden keinen Variablen gefunden!';
            $this->UpdateFormField('InfoMessage', 'visible', true);
            $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
        }
    }

    /**
     * Checks the alerting of a door or window sensor.
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
    public function CheckDoorWindowSensorAlerting(int $SenderID, bool $ValueChanged): bool
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
        $variables = json_decode($this->ReadPropertyString('DoorWindowSensors'), true);
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
                                    $this->SendDebug(__FUNCTION__, 'Abbruch, der Tür- / Fenstersensor ist nicht aktiviert!', 0);
                                    return false;
                                }
                                if (!$variable['UseMultipleAlerts'] && !$ValueChanged) {
                                    $this->SendDebug(__FUNCTION__, 'Abbruch, die Mehrfachauslösung ist nicht aktiviert!', 0);
                                    return false;
                                }
                                $open = false;
                                if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                                    $this->SendDebug(__FUNCTION__, 'Die Bedingungen wurden erfüllt. Die Tür oder das Fenster ist geöffnet!', 0);
                                    $open = true;
                                }
                                $mode = $this->GetValue('Mode');
                                switch ($this->GetValue('AlarmZoneDetailedState')) {
                                    case 0: //disarmed
                                        $this->CheckDoorWindowState($mode, false, false, false);
                                        //Protocol
                                        if ($variable['UseAlarmProtocol']) {
                                            $text = ' wurde geschlossen. (ID ' . $SenderID . ')';
                                            if ($open) {
                                                $text = ' wurde geöffnet. (ID ' . $SenderID . ')';
                                            }
                                            $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $variable['Designation'] . $text;
                                            $this->UpdateAlarmProtocol($logText, 0);
                                        }
                                        break;

                                    case 1: //armed
                                    case 3: //partial armed
                                        $this->CheckDoorWindowState($mode, false, false, false);
                                        //Variable is blacklisted
                                        if ($this->CheckSensorBlacklist($SenderID)) {
                                            //Protocol
                                            if ($variable['UseAlarmProtocol']) {
                                                $text = ' wurde geschlossen. (ID ' . $SenderID . ')';
                                                if ($open) {
                                                    $text = ' wurde ohne Alarmauslösung geöffnet. (ID ' . $SenderID . ')';
                                                }
                                                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $variable['Designation'] . $text;
                                                $this->UpdateAlarmProtocol($logText, 0);
                                            }
                                        } //Variable is not blacklisted
                                        else {
                                            $alerting = false;
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
                                            if ($alerting) { //always open
                                                $timeStamp = time();
                                                //Status verification
                                                if ($variable['OpenDoorWindowStatusVerificationDelay'] > 0) {
                                                    $this->SendDebug(__FUNCTION__, 'Status wird in ' . $variable['OpenDoorWindowStatusVerificationDelay'] . ' Sekunden erneut geprüft!', 0);
                                                    $scriptText = self::MODULE_PREFIX . '_VerifyOpenDoorWindowStatus(' . $this->InstanceID . ', ' . $SenderID . ', ' . $variable['OpenDoorWindowStatusVerificationDelay'] . ');';
                                                    @IPS_RunScriptText($scriptText);
                                                    return false;
                                                }
                                                $result = true;
                                                //Alarm state
                                                $this->SetValue('AlarmState', 1);
                                                $this->SetValue('AlertingSensor', $variable['Designation']);
                                                //Protocol
                                                if ($variable['UseAlarmProtocol']) {
                                                    $text = ' wurde geöffnet und hat einen Alarm ausgelöst. (ID ' . $SenderID . ')';
                                                    $logText = date('d.m.Y, H:i:s', $timeStamp) . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $variable['Designation'] . $text;
                                                    $this->UpdateAlarmProtocol($logText, 2);
                                                }
                                                //Notification
                                                if ($variable['UseNotification']) {
                                                    $this->SendNotification('DoorWindowAlarmNotification', (string) $variable['Designation']);
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
                                            } //No alerting
                                            else {
                                                //Protocol
                                                if ($variable['UseAlarmProtocol']) {
                                                    $text = ' wurde geschlossen. (ID ' . $SenderID . ')';
                                                    if ($open) {
                                                        $text = ' wurde ohne Alarmauslösung geöffnet. (ID ' . $SenderID . ')';
                                                    }
                                                    $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $variable['Designation'] . $text;
                                                    $this->UpdateAlarmProtocol($logText, 0);
                                                }
                                            }
                                        }
                                        break;

                                    case 2: //delayed armed
                                    case 4: //delayed partial armed
                                        $this->CheckDoorWindowState($mode, false, false, false);
                                        break;

                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Verifies the alerting after a delay.
     *
     * @param int $SenderID
     * @param int $Delay
     * @return void
     * @throws Exception
     */
    public function VerifyOpenDoorWindowStatus(int $SenderID, int $Delay): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        $sensors = json_decode($this->ReadAttributeString('VerificationSensors'));
        $sensors[] = $SenderID;
        $this->WriteAttributeString('VerificationSensors', json_encode($sensors));
        IPS_Sleep($Delay * 1000);
        $variables = json_decode($this->ReadPropertyString('DoorWindowSensors'), true);
        foreach ($variables as $variable) {
            if (array_key_exists('PrimaryCondition', $variable)) {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if ($primaryCondition != '') {
                    if (array_key_exists(0, $primaryCondition)) {
                        if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                            $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                            if ($id == $SenderID) {
                                if (!$variable['Use']) {
                                    $this->RemoveVerificationSensor($SenderID);
                                    return;
                                }
                                $open = false;
                                if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                                    $this->SendDebug(__FUNCTION__, 'Der Status wurde erneut geprüft. Die Tür oder das Fenster ist geöffnet!', 0);
                                    $open = true;
                                }
                                $mode = $this->GetValue('Mode');
                                switch ($this->GetValue('AlarmZoneDetailedState')) {
                                    case 1: //armed
                                    case 3: //partial armed
                                        $this->CheckDoorWindowState($mode, false, false, false);
                                        //Variable is blacklisted
                                        if (!$this->CheckSensorBlacklist($SenderID)) {
                                            $alerting = false;
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
                                            if ($alerting) { //open is verified
                                                //Alarm state
                                                $this->SetValue('AlarmState', 1);
                                                $this->SetValue('AlertingSensor', $variable['Designation']);
                                                //Protocol
                                                if ($variable['UseAlarmProtocol']) {
                                                    $TimeStamp = time();
                                                    $text = ' wurde geöffnet und hat einen Alarm ausgelöst. (ID ' . $SenderID . ')';
                                                    $logText = date('d.m.Y, H:i:s', $TimeStamp) . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $variable['Designation'] . $text;
                                                    $this->UpdateAlarmProtocol($logText, 2);
                                                }
                                                //Notification
                                                if ($variable['UseNotification']) {
                                                    $this->SendNotification('DoorWindowAlarmNotification', (string) $variable['Designation']);
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
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->RemoveVerificationSensor($SenderID);
    }

    /**
     * Configures the verification delay.
     *
     * @param int $Delay
     * @return void
     * @throws Exception
     */
    public function ConfigureVerificationDelay(int $Delay): void
    {
        if ($Delay > 10) {
            return;
        }
        $listedVariables = json_decode($this->ReadPropertyString('DoorWindowSensors'), true);
        $maximumVariables = count($listedVariables);
        $this->UpdateFormField('VerificationDelayProgress', 'minimum', 0);
        $this->UpdateFormField('VerificationDelayProgress', 'maximum', $maximumVariables);
        $passedVariables = 0;
        foreach ($listedVariables as $key => $variable) {
            $passedVariables++;
            $this->UpdateFormField('VerificationDelayProgress', 'visible', true);
            $this->UpdateFormField('VerificationDelayProgress', 'current', $passedVariables);
            $this->UpdateFormField('VerificationDelayProgressInfo', 'visible', true);
            $this->UpdateFormField('VerificationDelayProgressInfo', 'caption', $passedVariables . '/' . $maximumVariables);
            IPS_Sleep(200);
            $listedVariables[$key]['OpenDoorWindowStatusVerificationDelay'] = $Delay;
        }
        @IPS_SetProperty($this->InstanceID, 'DoorWindowSensors', json_encode(array_values($listedVariables)));
        if (@IPS_HasChanges($this->InstanceID)) {
            @IPS_ApplyChanges($this->InstanceID);
        }
    }

    #################### Private

    /**
     * Removes a sensor from the verification list.
     *
     * @param int $SensorID
     * @return void
     * @throws Exception
     */
    private function RemoveVerificationSensor(int $SensorID): void
    {
        $sensors = json_decode($this->ReadAttributeString('VerificationSensors'), true);
        foreach ($sensors as $key => $sensor) {
            if ($sensor == $SensorID) {
                unset($sensors[$key]);
            }
        }
        $sensors = array_values($sensors);
        $this->WriteAttributeString('VerificationSensors', json_encode($sensors));
    }

    /**
     * Checks the status of the door and window sensors for a specific mode.
     *
     * @param int $Mode
     * 0 =      disarmed,
     * 1 =      full protection mode
     * 2 =      hull protection mode
     * 3 =      partial protection mode
     *
     * @param bool $UseBlacklist
     * false =  don't use the blacklist,
     * true =   use the blacklist
     *
     * @param bool $UseProtocol
     * false =  don't use the alarm protocol,
     * true =   use the alarm protocol
     *
     * @param bool $UseNotification
     * false =  don't use the notification,
     * true =   use the notification for an open door or window
     *
     * @return bool
     * Returns the activation status:
     * false =  don't activate,
     * true =   activate
     *
     * @throws Exception
     */
    private function CheckDoorWindowState(int $Mode, bool $UseBlacklist, bool $UseProtocol, bool $UseNotification): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        switch ($Mode) {
            case 1:
                $modeText = 'Vollschutz';
                break;

            case 2:
                $modeText = 'Hüllschutz';
                break;

            case 3:
                $modeText = 'Teilschutz';
                break;

            default:
                $modeText = 'Unscharf';
        }
        $this->SendDebug(__FUNCTION__, 'Modus: ' . $modeText, 0);
        $activationState = true;
        $doorWindowState = false;
        $variables = json_decode($this->ReadPropertyString('DoorWindowSensors'), true);
        foreach ($variables as $variable) {
            $open = false;
            if (!$variable['Use']) {
                continue;
            }
            $id = 0;
            if (array_key_exists('PrimaryCondition', $variable)) {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if ($primaryCondition != '') {
                    if (array_key_exists(0, $primaryCondition)) {
                        if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                            $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        }
                    }
                }
            }
            if ($id <= 1 || !@IPS_ObjectExists($id)) {
                continue;
            }
            $check = true;
            switch ($Mode) {
                //Disarm
                case 0:
                    $check = false;
                    $checkProtectionModeActivation = '';
                    $protectionModeActive = '';
                    $checkActivation = '';
                    if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                        $doorWindowState = true;
                    }
                    break;

                //Full protection
                case 1:
                    $checkProtectionModeActivation = 'CheckFullProtectionModeActivation';
                    $protectionModeActive = 'FullProtectionModeActive';
                    $checkActivation = 'CheckFullProtectionActivation';
                    break;

                case 2: //Hull protection
                    $checkProtectionModeActivation = 'CheckHullProtectionModeActivation';
                    $protectionModeActive = 'HullProtectionModeActive';
                    $checkActivation = 'CheckHullProtectionActivation';
                    break;

                case 3: //Partial protection
                    $checkProtectionModeActivation = 'CheckPartialProtectionModeActivation';
                    $protectionModeActive = 'PartialProtectionModeActive';
                    $checkActivation = 'CheckPartialProtectionActivation';
                    break;

                default:
                    return false;

            }
            if ($check) {
                if ($variable[$protectionModeActive]) {
                    //Check conditions
                    if (IPS_IsConditionPassing($variable['PrimaryCondition']) && IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                        $open = true;
                        $doorWindowState = true;
                    }
                    if ($open) {
                        //Check if we have an activation check for this mode
                        if ($this->ReadPropertyBoolean($checkProtectionModeActivation)) {
                            //Check if the sensor has an activation check
                            if ($variable[$checkActivation]) {
                                $activationState = false;
                            } else {
                                if ($UseBlacklist) {
                                    $this->AddSensorBlacklist($id, $variable['Designation']);
                                }
                            }
                        }
                        //We don't have an activation check for this mode
                        else {
                            if ($UseBlacklist) {
                                $this->AddSensorBlacklist($id, $variable['Designation']);
                            }
                            //Protocol
                            if ($UseProtocol) {
                                $text = $variable['Designation'] . ' ist noch geöffnet. Bitte prüfen! (ID ' . $id . ')';
                                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('AlarmZoneName') . ', ' . $text;
                                $this->UpdateAlarmProtocol($logText, 0);
                            }
                        }
                        if ($UseNotification) {
                            $this->SendNotification('OpenDoorWindowNotification', (string) $variable['Designation']);
                            IPS_Sleep(self::SLEEP_DELAY);
                        }
                    }
                }
            }
        }
        $doorWindowStateText = 'Geschlossen';
        if ($doorWindowState) {
            $doorWindowStateText = 'Geöffnet';
        }
        $this->SendDebug(__FUNCTION__, 'Tür- und Fensterstatus: ' . $doorWindowStateText, 0);
        $this->SetValue('DoorWindowState', $doorWindowState);
        if ($Mode != 0) {
            $activationStateText = 'Abbruch';
            if ($activationState) {
                $activationStateText = 'OK';
            }
            $this->SendDebug(__FUNCTION__, 'Aktivierung: ' . $activationStateText, 0);
        }
        return $activationState;
    }
}