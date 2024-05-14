<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/helper/
 * @file          AZST_Control.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait AZST_Control
{
    /**
     * Determines the variables of the alarm zones.
     *
     * @return void
     * @throws Exception
     */
    public function DetermineAlarmZoneVariables(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $alarmZones = IPS_GetInstanceListByModuleID(self::ALARMZONE_MODULE_GUID);
        $this->UpdateFormField('DetermineAlarmZoneVariablesProgress', 'minimum', 0);
        $maximumZones = count($alarmZones);
        $this->UpdateFormField('DetermineAlarmZoneVariablesProgress', 'maximum', $maximumZones);
        $this->UpdateFormField('DetermineAlarmZoneVariablesProgress', 'visible', true);
        $this->UpdateFormField('DetermineAlarmZoneVariablesProgressInfo', 'visible', true);
        $determinedVariables = [];
        $passedZones = 0;
        foreach ($alarmZones as $alarmZone) {
            $passedZones++;
            $this->UpdateFormField('DetermineAlarmZoneVariablesProgress', 'visible', true);
            $this->UpdateFormField('DetermineAlarmZoneVariablesProgress', 'current', $passedZones);
            $this->UpdateFormField('DetermineAlarmZoneVariablesProgressInfo', 'visible', true);
            $this->UpdateFormField('DetermineAlarmZoneVariablesProgressInfo', 'caption', $passedZones . '/' . $maximumZones);
            IPS_Sleep(100);
            $id = $alarmZone;
            if ($id <= 1 || !@IPS_ObjectExists($id)) {
                continue;
            }
            $determinedVariables[] = [
                'Use'      => false,
                'ID'       => $id,
                'Location' => @IPS_GetLocation($id)];
        }
        $amount = count($determinedVariables);
        //Get already listed variables
        $listedVariables = json_decode($this->ReadPropertyString('AlarmZones'), true);
        foreach ($listedVariables as $listedVariable) {
            $listedVariableID = $listedVariable['ID'];
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
        if (empty($determinedVariables)) {
            $this->UpdateFormField('DetermineAlarmZoneVariablesProgress', 'visible', false);
            $this->UpdateFormField('DetermineAlarmZoneVariablesProgressInfo', 'visible', false);
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
        $this->UpdateFormField('DeterminedAlarmZoneList', 'visible', true);
        $this->UpdateFormField('DeterminedAlarmZoneList', 'rowCount', count($determinedVariables));
        $this->UpdateFormField('DeterminedAlarmZoneList', 'values', json_encode($determinedVariables));
        $this->UpdateFormField('ApplyPreAlarmZoneTriggerValues', 'visible', true);
    }

    /**
     * Applies the determined variables to the alarm zone list.
     *
     * @param object $ListValues
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function ApplyDeterminedAlarmZoneVariables(object $ListValues): void
    {
        $determinedVariables = [];
        $zones = [];
        $protectionMode = [];
        $glassBreakageDetectorControl = [];
        $systemState = [];
        $systemDetailedState = [];
        $alarmState = [];
        $alertingSensor = [];
        $doorWindowState = [];
        $motionDetectorState = [];
        $glassBreakageDetectorState = [];
        $smokeDetectorState = [];
        $waterDetectorState = [];
        $alarmSiren = [];
        $internalAlarmSiren = [];
        $alarmLight = [];
        $alarmCall = [];
        $panicAlarm = [];
        $reflection = new ReflectionObject($ListValues);
        $property = $reflection->getProperty('array');
        $property->setAccessible(true);
        $variables = $property->getValue($ListValues);
        foreach ($variables as $variable) {
            if (!$variable['Use']) {
                continue;
            }
            $id = $variable['ID'];
            $name = @IPS_GetName($id);
            $determinedVariables[] = [
                'Use'                       => true,
                'ID'                        => $id,
                'Designation'               => $name,
                'IndividualProtectionMode'  => 4];
        }
        //Get already listed variables
        $listedVariables = json_decode($this->ReadPropertyString('AlarmZones'), true);
        foreach ($determinedVariables as $determinedVariable) {
            $determinedVariableID = $determinedVariable['ID'];
            if ($determinedVariableID > 1 && @IPS_ObjectExists($determinedVariableID)) {
                //Check variable id with already listed variable ids
                $add = true;
                foreach ($listedVariables as $listedVariable) {
                    $listedVariableID = $listedVariable['ID'];
                    if ($listedVariableID > 1 && @IPS_ObjectExists($determinedVariableID)) {
                        if ($determinedVariableID == $listedVariableID) {
                            $add = false;
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
        $this->UpdateFormField('ApplyNewConfigurationProgress', 'minimum', 0);
        $maximumConfiguration = 19 * count($listedVariables);
        $this->UpdateFormField('ApplyNewConfigurationProgress', 'maximum', $maximumConfiguration);
        $passedConfiguration = 0;
        foreach ($listedVariables as $listedVariable) {
            $passedConfiguration++;
            $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
            $id = $listedVariable['ID'];
            $zones[] = ['Use' => true, 'ID' => $id, 'Designation' => $listedVariable['Designation'], 'IndividualProtectionMode' => $listedVariable['IndividualProtectionMode']];
            $children = IPS_GetChildrenIDs($id);
            if (empty($children)) {
                continue;
            }
            $description = IPS_GetName($id);
            foreach ($children as $child) {
                IPS_Sleep(50);
                if ($child <= 1 || !@IPS_ObjectExists($child)) {
                    continue;
                }
                $ident = IPS_GetObject($child)['ObjectIdent'];
                switch ($ident) {
                    case 'Mode':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $protectionMode[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'GlassBreakageDetectorControlSwitch':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $glassBreakageDetectorControl[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'AlarmZoneState':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $systemState[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'AlarmZoneDetailedState':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $systemDetailedState[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'AlarmState':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $alarmState[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'AlertingSensor':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $alertingSensor[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'DoorWindowState':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $doorWindowState[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'MotionDetectorState':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $motionDetectorState[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'GlassBreakageDetectorState':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $glassBreakageDetectorState[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'SmokeDetectorState':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $smokeDetectorState[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'WaterDetectorState':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $waterDetectorState[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'AlarmSiren':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $alarmSiren[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'InternalAlarmSiren':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $internalAlarmSiren[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'AlarmLight':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration, $maximumConfiguration);
                        $alarmLight[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'AlarmCall':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration++, $maximumConfiguration);
                        $alarmCall[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                    case 'PanicAlarm':
                        $passedConfiguration++;
                        $this->ApplyNewConfigurationUpdateProgressState($passedConfiguration++, $maximumConfiguration);
                        $panicAlarm[] = ['Use' => true, 'ID' => $child, 'Designation' => $description];
                        break;

                }
            }
        }
        //Sort variables by name
        array_multisort(array_column($listedVariables, 'Designation'), SORT_ASC, $listedVariables);
        @IPS_SetProperty($this->InstanceID, 'AlarmZones', json_encode($zones));
        @IPS_SetProperty($this->InstanceID, 'ProtectionMode', json_encode($protectionMode));
        @IPS_SetProperty($this->InstanceID, 'GlassBreakageDetectorControl', json_encode($glassBreakageDetectorControl));
        @IPS_SetProperty($this->InstanceID, 'SystemState', json_encode($systemState));
        @IPS_SetProperty($this->InstanceID, 'SystemDetailedState', json_encode($systemDetailedState));
        @IPS_SetProperty($this->InstanceID, 'AlarmState', json_encode($alarmState));
        @IPS_SetProperty($this->InstanceID, 'AlertingSensor', json_encode($alertingSensor));
        @IPS_SetProperty($this->InstanceID, 'DoorWindowState', json_encode($doorWindowState));
        @IPS_SetProperty($this->InstanceID, 'MotionDetectorState', json_encode($motionDetectorState));
        @IPS_SetProperty($this->InstanceID, 'GlassBreakageDetectorState', json_encode($glassBreakageDetectorState));
        @IPS_SetProperty($this->InstanceID, 'SmokeDetectorState', json_encode($smokeDetectorState));
        @IPS_SetProperty($this->InstanceID, 'WaterDetectorState', json_encode($waterDetectorState));
        @IPS_SetProperty($this->InstanceID, 'AlarmSiren', json_encode($alarmSiren));
        @IPS_SetProperty($this->InstanceID, 'InternalAlarmSiren', json_encode($internalAlarmSiren));
        @IPS_SetProperty($this->InstanceID, 'AlarmLight', json_encode($alarmLight));
        @IPS_SetProperty($this->InstanceID, 'AlarmCall', json_encode($alarmCall));
        @IPS_SetProperty($this->InstanceID, 'PanicAlarm', json_encode($panicAlarm));
        if (@IPS_HasChanges($this->InstanceID)) {
            @IPS_ApplyChanges($this->InstanceID);
        }
    }

    /**
     * Sets an alarm.
     *
     * @param bool $State
     * false =  alarm off
     * true =   panic alarm on
     *
     * @param int $SenderID
     *
     * @return void
     * @throws Exception
     */
    public function SetAlarm(bool $State, int $SenderID): void
    {
        //Alarm off
        if (!$State) {
            //Protocol
            $text = 'Der Alarm wurde deaktiviert. (ID ' . $SenderID . ')';
            $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('SystemName') . ', ' . $text;
            $this->UpdateAlarmProtocol($logText, 1);
            //Reset
            $this->WriteAttributeBoolean('DisableUpdateMode', true);
            if (!$this->ReadPropertyBoolean('AlarmSwitchDisarmAlarmZones')) {
                $this->SetValue('AlarmSwitch', false);
                $this->SetValue('AlertingSensor', '');
                $this->SetValue('AlarmState', 0);
                $this->SetValue('AlarmCall', false);
                $this->SetValue('PanicAlarm', false);
                if ($this->ReadPropertyBoolean('AlarmSwitchAlarmSirenOff')) {
                    $this->SetValue('AlarmSiren', false);
                    $this->SetValue('InternalAlarmSiren', false);
                }
                if ($this->ReadPropertyBoolean('AlarmSwitchAlarmLightOff')) {
                    $this->SetValue('AlarmLight', false);
                }
            } else {
                $this->SelectProtectionMode(0, (string) $SenderID);
            }
            $this->WriteAttributeBoolean('DisableUpdateMode', false);
            //Update
            $this->UpdateStates();
            //Notification
            if (!$this->ReadPropertyBoolean('AlarmSwitchDisarmAlarmZones') && !$this->GetValue('AlarmSwitch')) {
                $this->ExecuteAlarmZoneControllerNotification(910);
            }
        }
        //Panic alarm
        if ($State) {
            //Protocol
            $text = 'Der Panikalarm wurde ausgelöst. (ID ' . $SenderID . ')';
            $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', ' . $this->ReadPropertyString('SystemName') . ', ' . $text;
            $this->UpdateAlarmProtocol($logText, 1);
            //States
            $this->SetValue('AlarmSwitch', true);
            $this->SetValue('AlertingSensor', 'Panikalarm');
            $this->SetValue('AlarmState', 2);
            $this->SetValue('PanicAlarm', true);
            if ($this->ReadPropertyBoolean('PanicAlarmUseAlarmSiren')) {
                $this->SetValue('AlarmSiren', true);
            }
            if ($this->ReadPropertyBoolean('PanicAlarmUseInternalAlarmSiren')) {
                $this->SetValue('InternalAlarmSiren', true);
            }
            if ($this->ReadPropertyBoolean('PanicAlarmUseAlarmLight')) {
                $this->SetValue('AlarmLight', true);
            }
            if ($this->ReadPropertyBoolean('PanicAlarmUseAlarmCall')) {
                $this->SetValue('AlarmCall', true);
            }
            //Notification
            $this->ExecuteAlarmZoneControllerNotification(911);
        }
    }

    /**
     * Switches the glass breakage detector control off or on.
     *
     * @param bool $State
     * false =  off = no detection,
     * true =   on = detection
     *
     * @return void
     * @throws Exception
     */
    public function SwitchGlassBreakageDetectorControl(bool $State): void
    {
        $this->WriteAttributeBoolean('DisableUpdateGlassBreakageDetectorControl', true);
        $this->SetValue('GlassBreakageDetectorControlSwitch', $State);
        $alarmZones = json_decode($this->ReadPropertyString('AlarmZones'), true);
        if (empty($alarmZones)) {
            return;
        }
        foreach ($alarmZones as $alarmZone) {
            if (!$alarmZone['Use']) {
                continue;
            }
            $id = $alarmZone['ID'];
            if ($id == 0 || @!IPS_ObjectExists($id)) {
                continue;
            }
            @AZ_SwicthGlassBreakageControl($id, $State);
        }
        $this->WriteAttributeBoolean('DisableUpdateGlassBreakageDetectorControl', false);
        $this->UpdateGlassBreakageDetectorControl();
    }

    /**
     * Selects the protection mode.
     *
     * @param int $Mode
     * 0 =  Disarmed,
     * 1 =  Full protection mode,
     * 2 =  Hull protection mode,
     * 3 =  Partial protection mode,
     * 4 =  Individual protection mode
     *
     * @param string $SenderID
     *
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function SelectProtectionMode(int $Mode, string $SenderID): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        if (!$this->CheckOperationMode($Mode)) {
            return false;
        }
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        $alarmZones = json_decode($this->ReadPropertyString('AlarmZones'), true);
        if (empty($alarmZones)) {
            return false;
        }
        $this->WriteAttributeBoolean('DisableUpdateMode', true);
        $result = true;
        if ($Mode >= 0 && $Mode <= 4) {
            $this->SetValue('Mode', $Mode);
        }
        $useAlarmZoneControllerNotification = $this->CheckAlarmZoneControllerNotification($Mode);
        $useAlarmZoneControllerStatusIndicator = $this->CheckAlarmZoneControllerStatusIndicator($Mode);
        $useAlarmZoneControllerAcknowledgementTone = $this->CheckAlarmZoneControllerAcknowledgementTone($Mode);
        $useAlarmZoneControllerAction = $this->CheckAlarmZoneControllerAction($Mode);
        //Select protection mode
        switch ($Mode) {
            case 0: //Disarm
            case 1: //Full protection mode
            case 2: //Hull protection mode
            case 3: //Partial protection mode
                foreach ($alarmZones as $alarmZone) {
                    if (!$alarmZone['Use']) {
                        continue;
                    }
                    $id = $alarmZone['ID'];
                    if ($id == 0 || @!IPS_ObjectExists($id)) {
                        continue;
                    }
                    $response = @AZ_SelectProtectionMode($id, $Mode, $SenderID, !$useAlarmZoneControllerNotification, !$useAlarmZoneControllerStatusIndicator, !$useAlarmZoneControllerAcknowledgementTone, !$useAlarmZoneControllerAction);
                    if (!$response) {
                        $result = false;
                    }
                }
                break;

            case 4: //Individual protection mode
                foreach ($alarmZones as $alarmZone) {
                    if (!$alarmZone['Use']) {
                        continue;
                    }
                    $id = $alarmZone['ID'];
                    if ($id == 0 || @!IPS_ObjectExists($id)) {
                        continue;
                    }
                    switch ($alarmZone['IndividualProtectionMode']) {
                        case 0: //Disarmed
                            $response = @AZ_SelectProtectionMode($id, 0, $SenderID, !$useAlarmZoneControllerNotification, !$useAlarmZoneControllerStatusIndicator, !$useAlarmZoneControllerAcknowledgementTone, !$useAlarmZoneControllerAction);
                            if (!$response) {
                                $result = false;
                            }
                            break;

                        case 1: //Full protection mode
                            $response = @AZ_SelectProtectionMode($id, 1, $SenderID, !$useAlarmZoneControllerNotification, !$useAlarmZoneControllerStatusIndicator, !$useAlarmZoneControllerAcknowledgementTone, !$useAlarmZoneControllerAction);
                            if (!$response) {
                                $result = false;
                            }
                            break;

                        case 2: //Hull protection mode
                            $response = @AZ_SelectProtectionMode($id, 2, $SenderID, !$useAlarmZoneControllerNotification, !$useAlarmZoneControllerStatusIndicator, !$useAlarmZoneControllerAcknowledgementTone, !$useAlarmZoneControllerAction);
                            if (!$response) {
                                $result = false;
                            }
                            break;

                        case 3: //Partial protection mode
                            $response = @AZ_SelectProtectionMode($id, 3, $SenderID, !$useAlarmZoneControllerNotification, !$useAlarmZoneControllerStatusIndicator, !$useAlarmZoneControllerAcknowledgementTone, !$useAlarmZoneControllerAction);
                            if (!$response) {
                                $result = false;
                            }
                            break;

                        case 4: //No function
                            $this->SendDebug(__FUNCTION__, 'Alarmzone ID ' . $id . ' hat keinen Individualschutz!', 0);
                            break;

                    }
                }
                break;

            default:
                $result = false;

        }
        $this->WriteAttributeBoolean('DisableUpdateMode', false);

        //Update
        $this->UpdateProtectionMode();
        $this->UpdateSystemState();
        $this->UpdateSystemDetailedState();
        //Notification
        $this->ExecuteAlarmZoneControllerNotification($Mode);

        //Status indicator
        $this->ExecuteStatusIndicator($Mode);

        //Acknowledgement tone
        $this->ExecuteAcknowledgementTone($Mode);

        //Action
        $this->ExecuteAction($Mode);
        return $result;
    }

    /**
     * Checks whether an operating mode is being used.
     *
     * @param int $Mode
     *  0 =  Disarm,
     *  1 =  Full protection mode
     *  2 =  Hull protection mode,
     *  3 =  Partial protection mode,
     *  4 =  Individual protection mode
     *
     * @return bool
     * false =  operation mode is not used,
     * true =   operation mode is used
     *
     * @throws Exception
     */
    private function CheckOperationMode(int $Mode): bool
    {
        switch ($Mode) {
            case 0: //Disarmed
                $modeText = $this->ReadPropertyString('DisarmedName');
                $check = true;
                break;

            case 1: //Full protection  mode
                $modeText = $this->ReadPropertyString('FullProtectionName');
                $check = $this->ReadPropertyBoolean('UseFullProtectionMode');
                break;

            case 2:
                $modeText = $this->ReadPropertyString('HullProtectionName');
                $check = $this->ReadPropertyBoolean('UseHullProtectionMode');
                break;

            case 3:
                $modeText = $this->ReadPropertyString('PartialProtectionName');
                $check = $this->ReadPropertyBoolean('UsePartialProtectionMode');
                break;

            case 4:
                $modeText = $this->ReadPropertyString('IndividualProtectionName');
                $check = $this->ReadPropertyBoolean('UseIndividualProtectionMode');
                break;

            default:
                $modeText = 'Unbekannt';
                $check = false;
        }
        $this->SendDebug(__FUNCTION__, 'Modus: ' . $modeText, 0);
        if (!$check) {
            $this->SendDebug(__FUNCTION__, 'Der Modus ist deaktiviert und steht nicht zur Verfügung!', 0);
            $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der Modus ' . $modeText . ' ist deaktiviert und steht nicht zur Verfügung!', KL_WARNING);
            return false;
        }
        return true;
    }

    ########## Private

    private function ApplyNewConfigurationUpdateProgressState(int $PassedConfiguration, int $MaximumConfiguration): void
    {
        $this->UpdateFormField('ApplyNewConfigurationProgress', 'visible', true);
        $this->UpdateFormField('ApplyNewConfigurationProgress', 'current', $PassedConfiguration);
        $this->UpdateFormField('ApplyNewConfigurationProgressInfo', 'visible', true);
        $this->UpdateFormField('ApplyNewConfigurationProgressInfo', 'caption', $PassedConfiguration . '/' . $MaximumConfiguration);
        IPS_Sleep(50);
    }
}