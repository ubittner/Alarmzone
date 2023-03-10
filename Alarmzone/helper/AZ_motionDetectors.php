<?php

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

/*
 * @author      Ulrich Bittner
 * @copyright   (c) 2021
 * @license     CC BY-NC-SA 4.0
 * @see         https://github.com/ubittner/Alarmzone/tree/master/Alarmzone
 */

declare(strict_types=1);

trait AZ_motionDetectors
{
    public function DetermineMotionDetectorVariables(): void
    {
        $variables = [];
        foreach (@IPS_GetInstanceListByModuleID(self::HOMEMATIC_DEVICE_GUID) as $instanceID) {
            $childrenIDs = @IPS_GetChildrenIDs($instanceID);
            foreach ($childrenIDs as $childrenID) {
                $match = false;
                $object = @IPS_GetObject($childrenID);
                if ($object['ObjectIdent'] == 'MOTION') {
                    $match = true;
                }
                if ($match) {
                    if ($object['ObjectType'] == 2) {
                        $name = strstr(@IPS_GetName($instanceID), ':', true);
                        if ($name == false) {
                            $name = @IPS_GetName($instanceID);
                        }
                        $type = IPS_GetVariable($childrenID)['VariableType'];
                        $triggerValue = 'true';
                        if ($type == 1) {
                            $triggerValue = '1';
                        }
                        array_push($variables, [
                            'Use'                         => true,
                            'Name'                        => $name,
                            'ID'                          => $childrenID,
                            'TriggerType'                 => 6,
                            'TriggerValue'                => $triggerValue,
                            'FullProtectionModeActive'    => true,
                            'HullProtectionModeActive'    => false,
                            'PartialProtectionModeActive' => true,
                            'UseAlarmSiren'               => true,
                            'UseAlarmLight'               => true,
                            'UseAlarmCall'                => true]);
                    }
                }
            }
        }
        // Get already listed variables
        $listedVariables = json_decode($this->ReadPropertyString('MotionDetectors'), true);
        // Add new variables
        if (!empty($listedVariables)) {
            $addVariables = array_diff(array_column($variables, 'ID'), array_column($listedVariables, 'ID'));
            if (!empty($addVariables)) {
                foreach ($addVariables as $addVariable) {
                    $name = strstr(@IPS_GetName(@IPS_GetParent($addVariable)), ':', true);
                    $type = IPS_GetVariable($addVariable)['VariableType'];
                    $triggerValue = 'true';
                    if ($type == 1) {
                        $triggerValue = '1';
                    }
                    array_push($listedVariables, [
                        'Use'                         => true,
                        'Name'                        => $name,
                        'ID'                          => $addVariable,
                        'TriggerType'                 => 6,
                        'TriggerValue'                => $triggerValue,
                        'FullProtectionModeActive'    => true,
                        'HullProtectionModeActive'    => true,
                        'PartialProtectionModeActive' => true,
                        'UseNotification'             => true,
                        'UseAlarmSiren'               => true,
                        'UseAlarmLight'               => true,
                        'UseAlarmCall'                => true]);
                }
            }
        } else {
            $listedVariables = $variables;
        }
        // Sort variables by name
        array_multisort(array_column($listedVariables, 'Name'), SORT_ASC, $listedVariables);
        $listedVariables = array_values($listedVariables);
        // Update variable list
        $value = json_encode($listedVariables);
        @IPS_SetProperty($this->InstanceID, 'MotionDetectors', $value);
        if (@IPS_HasChanges($this->InstanceID)) {
            @IPS_ApplyChanges($this->InstanceID);
        }
        echo 'Die Bewegungsmelder wurden erfolgreich ermittelt!';
    }

    public function CheckMotionDetectorAlerting(int $SenderID, bool $ValueChanged): bool
    {
        if ($this->CheckMaintenanceMode()) {
            return false;
        }
        $vars = json_decode($this->ReadPropertyString('MotionDetectors'), true);
        if (empty($vars)) {
            return false;
        }
        $key = array_search($SenderID, array_column($vars, 'ID'));
        if (!is_int($key)) {
            return false;
        }
        if (!$vars[$key]['Use']) {
            return false;
        }
        $execute = false;
        $motionDetected = false;
        $type = IPS_GetVariable($SenderID)['VariableType'];
        $value = $vars[$key]['TriggerValue'];
        switch ($vars[$key]['TriggerType']) {
            case 0: # on change (bool, integer, float, string)
                if ($ValueChanged) {
                    $this->SendDebug(__FUNCTION__, 'Bei ??nderung (bool, integer, float, string)', 0);
                    $execute = true;
                    $motionDetected = true;
                }
                break;

            case 1: # on update (bool, integer, float, string)
                $this->SendDebug(__FUNCTION__, 'Bei Aktualisierung (bool, integer, float, string)', 0);
                $execute = true;
                $motionDetected = true;
                break;

            case 2: # on limit drop, once (integer, float)
                switch ($type) {
                    case 1: # integer
                        if ($ValueChanged) {
                            $execute = true;
                            if ($value == 'false') {
                                $value = '0';
                            }
                            if ($value == 'true') {
                                $value = '1';
                            }
                            if (GetValueInteger($SenderID) < intval($value)) {
                                $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung, einmalig (integer)', 0);
                                $motionDetected = true;
                            }
                        }
                        break;

                    case 2: # float
                        if ($ValueChanged) {
                            $execute = true;
                            if ($value == 'false') {
                                $value = '0';
                            }
                            if ($value == 'true') {
                                $value = '1';
                            }
                            if (GetValueFloat($SenderID) < floatval(str_replace(',', '.', $value))) {
                                $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung, einmalig (float)', 0);
                                $motionDetected = true;
                            }
                        }
                        break;

                }
                break;

            case 3: # on limit drop, every time (integer, float)
                switch ($type) {
                    case 1: #integer
                        $execute = true;
                        if ($value == 'false') {
                            $value = '0';
                        }
                        if ($value == 'true') {
                            $value = '1';
                        }
                        if (GetValueInteger($SenderID) < intval($value)) {
                            $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung, mehrmalig (integer)', 0);
                            $motionDetected = true;
                        }
                        break;

                    case 2: # float
                        $execute = true;
                        if ($value == 'false') {
                            $value = '0';
                        }
                        if ($value == 'true') {
                            $value = '1';
                        }
                        if (GetValueFloat($SenderID) < floatval(str_replace(',', '.', $value))) {
                            $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung, mehrmalig (float)', 0);
                            $motionDetected = true;
                        }
                        break;

                }
                break;

            case 4: # on limit exceed, once (integer, float)
                switch ($type) {
                    case 1: # integer
                        if ($ValueChanged) {
                            $execute = true;
                            if ($value == 'false') {
                                $value = '0';
                            }
                            if ($value == 'true') {
                                $value = '1';
                            }
                            if (GetValueInteger($SenderID) > intval($value)) {
                                $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung, einmalig (integer)', 0);
                                $motionDetected = true;
                            }
                        }
                        break;

                    case 2: # float
                        if ($ValueChanged) {
                            $execute = true;
                            if ($value == 'false') {
                                $value = '0';
                            }
                            if ($value == 'true') {
                                $value = '1';
                            }
                            if (GetValueFloat($SenderID) > floatval(str_replace(',', '.', $value))) {
                                $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung, einmalig (float)', 0);
                                $motionDetected = true;
                            }
                        }
                        break;

                }
                break;

            case 5: # on limit exceed, every time (integer, float)
                switch ($type) {
                    case 1: # integer
                        $execute = true;
                        if ($value == 'false') {
                            $value = '0';
                        }
                        if ($value == 'true') {
                            $value = '1';
                        }
                        if (GetValueInteger($SenderID) > intval($value)) {
                            $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung, mehrmalig (integer)', 0);
                            $motionDetected = true;
                        }
                        break;

                    case 2: # float
                        $execute = true;
                        if ($value == 'false') {
                            $value = '0';
                        }
                        if ($value == 'true') {
                            $value = '1';
                        }
                        if (GetValueFloat($SenderID) > floatval(str_replace(',', '.', $value))) {
                            $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung, mehrmalig (float)', 0);
                            $motionDetected = true;
                        }
                        break;

                }
                break;

            case 6: # on specific value, once (bool, integer, float, string)
                switch ($type) {
                    case 0: # bool
                        if ($ValueChanged) {
                            $execute = true;
                            if ($value == 'false') {
                                $value = '0';
                            }
                            if (GetValueBoolean($SenderID) == boolval($value)) {
                                $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert, einmalig (bool)', 0);
                                $motionDetected = true;
                            }
                        }
                        break;

                    case 1: # integer
                        if ($ValueChanged) {
                            $execute = true;
                            if ($value == 'false') {
                                $value = '0';
                            }
                            if ($value == 'true') {
                                $value = '1';
                            }
                            if (GetValueInteger($SenderID) == intval($value)) {
                                $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert, einmalig (integer)', 0);
                                $motionDetected = true;
                            }
                        }
                        break;

                    case 2: # float
                        if ($ValueChanged) {
                            $execute = true;
                            if ($value == 'false') {
                                $value = '0';
                            }
                            if ($value == 'true') {
                                $value = '1';
                            }
                            if (GetValueFloat($SenderID) == floatval(str_replace(',', '.', $value))) {
                                $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert, einmalig (float)', 0);
                                $motionDetected = true;
                            }
                        }
                        break;

                    case 3: # string
                        if ($ValueChanged) {
                            $execute = true;
                            if (GetValueString($SenderID) == (string) $value) {
                                $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert, einmalig (string)', 0);
                                $motionDetected = true;
                            }
                        }
                        break;

                }
                break;

            case 7: # on specific value, every time (bool, integer, float, string)
                switch ($type) {
                    case 0: # bool
                        $execute = true;
                        if ($value == 'false') {
                            $value = '0';
                        }
                        if (GetValueBoolean($SenderID) == boolval($value)) {
                            $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert, mehrmalig (bool)', 0);
                            $motionDetected = true;
                        }
                        break;

                    case 1: # integer
                        $execute = true;
                        if ($value == 'false') {
                            $value = '0';
                        }
                        if ($value == 'true') {
                            $value = '1';
                        }
                        if (GetValueInteger($SenderID) == intval($value)) {
                            $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert, mehrmalig (integer)', 0);
                            $motionDetected = true;
                        }
                        break;

                    case 2: # float
                        $execute = true;
                        if ($value == 'false') {
                            $value = '0';
                        }
                        if ($value == 'true') {
                            $value = '1';
                        }
                        if (GetValueFloat($SenderID) == floatval(str_replace(',', '.', $value))) {
                            $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert, mehrmalig (float)', 0);
                            $motionDetected = true;
                        }
                        break;

                    case 3: # string
                        $execute = true;
                        if (GetValueString($SenderID) == (string) $value) {
                            $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert, mehrmalig (string)', 0);
                            $motionDetected = true;
                        }
                        break;

                }
                break;

        }
        $this->SendDebug(__FUNCTION__, 'Bedingung erf??llt: ' . json_encode($execute), 0);
        $this->SendDebug(__FUNCTION__, 'Bewegung erkannt: ' . json_encode($motionDetected), 0);
        // Check alarm zone state
        $sensorName = $vars[$key]['Name'];
        $timeStamp = date('d.m.Y, H:i:s');
        $location = $this->ReadPropertyString('Location');
        $alarmZoneName = $this->ReadPropertyString('AlarmZoneName');
        $alarmZoneState = $this->GetValue('AlarmZoneState');
        switch ($alarmZoneState) {
            case 0: # disarmed
                if ($execute) {
                    $this->CheckMotionDetectorState();
                }
                break;

            case 1: # armed
            case 3: # partial armed
                if ($execute) {
                    $this->CheckMotionDetectorState();
                }
                $alerting = false;
                if ($execute && $motionDetected) {
                    // Check if sensor is activated for full protection mode
                    if ($this->GetValue('FullProtectionMode')) {
                        if ($vars[$key]['FullProtectionModeActive']) {
                            $alerting = true;
                        }
                    }
                    // Check if sensor is activated for hull protection mode
                    if ($this->GetValue('HullProtectionMode')) {
                        if ($vars[$key]['HullProtectionModeActive']) {
                            $alerting = true;
                        }
                    }
                    // Check if sensor is activated for partial protection mode
                    if ($this->GetValue('PartialProtectionMode')) {
                        if ($vars[$key]['PartialProtectionModeActive']) {
                            $alerting = true;
                        }
                    }
                    if ($alerting) {
                        // Alarm state
                        $this->SetValue('AlarmState', 1);
                        $this->SetValue('AlertingSensor', $sensorName);
                        // Options
                        if ($vars[$key]['UseAlarmSiren']) {
                            $this->SetValue('AlarmSiren', true);
                        }
                        if ($vars[$key]['UseAlarmLight']) {
                            $this->SetValue('AlarmLight', true);
                        }
                        if ($vars[$key]['UseAlarmCall']) {
                            $this->SetValue('AlarmCall', true);
                        }
                        // Protocol
                        $text = $sensorName . ' hat eine Bewegung erkannt und einen Alarm ausgel??st. (ID ' . $SenderID . ')';
                        $logText = $timeStamp . ', ' . $location . ', ' . $alarmZoneName . ', ' . $text;
                        $this->UpdateAlarmProtocol($logText, 2);
                    }
                }
                break;

            case 2: # delayed armed
            case 4: # delayed partial armed
                if ($execute) {
                    $this->CheckMotionDetectorState();
                }
                break;

        }
        return $motionDetected;
    }

    #################### Private

    private function CheckMotionDetectorState(): bool
    {
        $state = false;
        $vars = json_decode($this->ReadPropertyString('MotionDetectors'));
        if (!empty($vars)) {
            foreach ($vars as $var) {
                if (!$var->Use) {
                    continue;
                }
                $id = $var->ID;
                if ($id == 0 || @!IPS_ObjectExists($id)) {
                    continue;
                }
                $type = IPS_GetVariable($id)['VariableType'];
                $value = $var->TriggerValue;
                switch ($var->TriggerType) {
                    case 0: # on change (bool, integer, float, string)
                    case 1: # on update (bool, integer, float, string)
                        $this->SendDebug(__FUNCTION__, 'Bei ??nderung und bei Aktualisierung wird nicht ber??cksichtigt!', 0);
                        break;

                    case 2: # on limit drop, once (integer, float)
                    case 3: # on limit drop, every time (integer, float)
                        switch ($type) {
                            case 1: # integer
                                if ($value == 'false') {
                                    $value = '0';
                                }
                                if ($value == 'true') {
                                    $value = '1';
                                }
                                if (GetValueInteger($id) < intval($value)) {
                                    $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung (integer)', 0);
                                    $state = true;
                                }
                                break;

                            case 2: # float
                                if ($value == 'false') {
                                    $value = '0';
                                }
                                if ($value == 'true') {
                                    $value = '1';
                                }
                                if (GetValueFloat($id) < floatval(str_replace(',', '.', $value))) {
                                    $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung (float)', 0);
                                    $state = true;
                                }
                                break;

                        }
                        break;

                    case 4: # on limit exceed, once (integer, float)
                    case 5: # on limit exceed, every time (integer, float)
                        switch ($type) {
                            case 1: # integer
                                if ($value == 'false') {
                                    $value = '0';
                                }
                                if ($value == 'true') {
                                    $value = '1';
                                }
                                if (GetValueInteger($id) > intval($value)) {
                                    $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung (integer)', 0);
                                    $state = true;
                                }
                                break;

                            case 2: # float
                                if ($value == 'false') {
                                    $value = '0';
                                }
                                if ($value == 'true') {
                                    $value = '1';
                                }
                                if (GetValueFloat($id) > floatval(str_replace(',', '.', $value))) {
                                    $this->SendDebug(__FUNCTION__, 'Bei Grenzunterschreitung (float)', 0);
                                    $state = true;
                                }
                                break;

                        }
                        break;

                    case 6: # on specific value, once (bool, integer, float, string)
                    case 7: # on specific value, every time (bool, integer, float, string)
                        switch ($type) {
                            case 0: # bool
                                if ($value == 'false') {
                                    $value = '0';
                                }
                                if (GetValueBoolean($id) == boolval($value)) {
                                    $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert (bool)', 0);
                                    $state = true;
                                }
                                break;

                            case 1: # integer
                                if ($value == 'false') {
                                    $value = '0';
                                }
                                if ($value == 'true') {
                                    $value = '1';
                                }
                                if (GetValueInteger($id) == intval($value)) {
                                    $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert (integer)', 0);
                                    $state = true;
                                }
                                break;

                            case 2: # float
                                if ($value == 'false') {
                                    $value = '0';
                                }
                                if ($value == 'true') {
                                    $value = '1';
                                }
                                if (GetValueFloat($id) == floatval(str_replace(',', '.', $value))) {
                                    $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert (float)', 0);
                                    $state = true;
                                }
                                break;

                            case 3: # string
                                if (GetValueString($id) == (string) $value) {
                                    $this->SendDebug(__FUNCTION__, 'Bei bestimmten Wert (string)', 0);
                                    $state = true;
                                }
                                break;

                        }
                        break;

                }
            }
        }
        $this->SetValue('MotionDetectorState', $state);
        return $state;
    }
}