<?php

/**
 * @project       Alarmzone/Alarmzonensteuerung/helper/
 * @file          AZST_Control.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
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
        $alarmLight = [];
        $alarmCall = [];
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
        $maximumConfiguration = 14 * count($listedVariables);
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

                }
            }
        }
        //Sort variables by name
        array_multisort(array_column($listedVariables, 'Designation'), SORT_ASC, $listedVariables);
        @IPS_SetProperty($this->InstanceID, 'AlarmZones', json_encode($zones));
        @IPS_SetProperty($this->InstanceID, 'ProtectionMode', json_encode($protectionMode));
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
        @IPS_SetProperty($this->InstanceID, 'AlarmLight', json_encode($alarmLight));
        @IPS_SetProperty($this->InstanceID, 'AlarmCall', json_encode($alarmCall));
        if (@IPS_HasChanges($this->InstanceID)) {
            @IPS_ApplyChanges($this->InstanceID);
        }
    }

    /**
     * Sets an alarm.
     *
     * @param bool $State
     * false =  no alarm,
     * true =   alarm
     *
     * @return void
     * @throws Exception
     */
    public function SetAlarm(bool $State): void
    {
        if (!$State) {
            $this->SetValue('AlarmSwitch', false);
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
                if ($this->ReadPropertyBoolean('UseDisarmAlarmZonesWhenAlarmSwitchIsOff')) {
                    $this->SelectProtectionMode(0, (string) $this->GetIDForIdent('AlarmSwitch'));
                } else {
                    @AZ_SetAlarm($id, false);
                }
            }
        } else {
            $alarm = false;
            $useAlarmSiren = $this->ReadPropertyBoolean('UseAlarmSirenWhenAlarmSwitchIsOn');
            $useAlarmLight = $this->ReadPropertyBoolean('UseAlarmLightWhenAlarmSwitchIsOn');
            $useAlarmCall = $this->ReadPropertyBoolean('UseAlarmCallWhenAlarmSwitchIsOn');
            if ($useAlarmSiren || $useAlarmLight || $useAlarmCall) {
                $alarm = true;
            }
            if ($alarm) {
                $this->SetValue('AlarmSwitch', true);
                $this->SetValue('AlarmState', 1);
                $this->SetValue('AlertingSensor', $this->ReadPropertyString('AlertingSensorNameWhenAlarmSwitchIsOn'));
                if ($useAlarmSiren) {
                    $this->SetValue('AlarmSiren', true);
                }
                if ($useAlarmLight) {
                    $this->SetValue('AlarmLight', true);
                }
                if ($useAlarmCall) {
                    $this->SetValue('AlarmCall', true);
                }
            }
        }
    }

    /**
     * Selects the protection mode.
     *
     * @param int $Mode
     * 0 =  disarmed,
     * 1 =  full protection mode,
     * 2 =  hull protection mode,
     * 3 =  partial protection mode,
     * 4 =  individual protection mode
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
        switch ($Mode) {
            case 0:
                $modeText = $this->ReadPropertyString('DisarmedName');
                break;

            case 1:
                $modeText = $this->ReadPropertyString('FullProtectionName');
                break;

            case 2:
                $modeText = $this->ReadPropertyString('HullProtectionName');
                break;

            case 3:
                $modeText = $this->ReadPropertyString('PartialProtectionName');
                break;

            case 4:
                $modeText = $this->ReadPropertyString('IndividualProtectionName');
                break;

            default:
                $modeText = 'Unbekannt';
        }
        $this->SendDebug(__FUNCTION__, 'Modus: ' . $modeText, 0);
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $alarmZones = json_decode($this->ReadPropertyString('AlarmZones'), true);
        if (empty($alarmZones)) {
            return false;
        }
        $this->WriteAttributeBoolean('DisableUpdateMode', true);
        $result = true;
        if ($Mode >= 0 && $Mode <= 4) {
            $this->SetValue('Mode', $Mode);
        }
        //Check if the mode is used
        $check = true;
        switch ($Mode) {
            case 1: //Full protection  mode
                $useProtectionModeName = 'UseFullProtectionMode';
                break;

            case 2: //Hull protection  mode
                $useProtectionModeName = 'UseHullProtectionMode';
                break;

            case 3: //Partial protection  mode
                $useProtectionModeName = 'UsePartialProtectionMode';
                break;

            case 4: //Individual protection  mode
                $useProtectionModeName = 'UseIndividualProtectionMode';
                break;

            default:
                $useProtectionModeName = '';
                $check = false;

        }
        if ($check) {
            if (!$this->ReadPropertyBoolean($useProtectionModeName)) {
                $this->SendDebug(__FUNCTION__, 'Der Modus ist deaktiviert und steht nicht zur Verfügung!', 0);
                $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der Modus ' . $modeText . ' ist deaktiviert und steht nicht zur Verfügung!', KL_WARNING);
                return false;
            }
        }
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
                    $response = @AZ_SelectProtectionMode($id, $Mode, $SenderID);
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
                            $response = @AZ_SelectProtectionMode($id, 0, $SenderID);
                            if (!$response) {
                                $result = false;
                            }
                            break;

                        case 1: //Full protection mode
                            $response = @AZ_SelectProtectionMode($id, 1, $SenderID);
                            if (!$response) {
                                $result = false;
                            }
                            break;

                        case 2: //Hull protection mode
                            $response = @AZ_SelectProtectionMode($id, 2, $SenderID);
                            if (!$response) {
                                $result = false;
                            }
                            break;

                        case 3: //Partial protection mode
                            $response = @AZ_SelectProtectionMode($id, 3, $SenderID);
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
        //Action
        $this->ExecuteAction($Mode, $SenderID);
        return $result;
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

    /**
     * Executes an action.
     *
     * @param int $Mode
     * 0 =  disarmed,
     * 1 =  full protection,
     * 2 =  hull protection,
     * 3 =  partial protection
     *
     * @param string $SenderID
     * ID of the sender
     *
     * @return void
     * @throws Exception
     */
    private function ExecuteAction(int $Mode, string $SenderID): void
    {
        //Action
        $executeAction = false;
        $action = [];
        switch ($Mode) {
            case 0: # disarmed
                switch ($SenderID) {
                    case $this->GetIDForIdent('AlarmSwitch'):
                    case $this->GetIDForIdent('FullProtectionControlSwitch'):
                    case $this->GetIDForIdent('HullProtectionControlSwitch'):
                    case $this->GetIDForIdent('PartialProtectionControlSwitch'):
                    case $this->GetIDForIdent('IndividualProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UseDisarmedAction')) {
                            $executeAction = true;
                            $action = json_decode($this->ReadPropertyString('DisarmedAction'), true);
                        }
                        break;

                }
                break;

            case 1: # full protection
                switch ($SenderID) {
                    case $this->GetIDForIdent('FullProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UseFullProtectionAction')) {
                            //Check if the status has remained the same
                            if ($this->GetValue('FullProtectionControlSwitch') || $this->GetValue('Mode') == 1) {
                                $executeAction = true;
                                $action = json_decode($this->ReadPropertyString('FullProtectionAction'), true);
                            }
                        }
                        break;

                }
                break;

            case 2: # hull protection
                switch ($SenderID) {
                    case $this->GetIDForIdent('HullProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UseHullProtectionAction')) {
                            //Check if the status has remained the same
                            if ($this->GetValue('HullProtectionControlSwitch') || $this->GetValue('Mode') == 2) {
                                $executeAction = true;
                                $action = json_decode($this->ReadPropertyString('HullProtectionAction'), true);
                            }
                        }
                        break;

                }
                break;

            case 3: # partial protection
                switch ($SenderID) {
                    case $this->GetIDForIdent('PartialProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UsePartialProtectionAction')) {
                            //Check if the status has remained the same
                            if ($this->GetValue('PartialProtectionControlSwitch') || $this->GetValue('Mode') == 3) {
                                $executeAction = true;
                                $action = json_decode($this->ReadPropertyString('PartialProtectionAction'), true);
                            }
                        }
                        break;

                }
                break;

            case 4: # individual protection
                switch ($SenderID) {
                    case $this->GetIDForIdent('IndividualProtectionControlSwitch'):
                    case $this->GetIDForIdent('Mode'):
                        if ($this->ReadPropertyBoolean('UseIndividualProtectionAction')) {
                            //Check if the status has remained the same
                            if ($this->GetValue('IndividualProtectionControlSwitch') || $this->GetValue('Mode') == 4) {
                                $executeAction = true;
                                $action = json_decode($this->ReadPropertyString('IndividualProtectionAction'), true);
                            }
                        }
                        break;

                }
                break;

        }
        if ($executeAction && !empty($action)) {
            $this->SendDebug(__FUNCTION__, 'Aktion: ' . json_encode($action), 0);
            IPS_RunAction($action['actionID'], $action['parameters']);
        }
    }
}